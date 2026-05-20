<?php

namespace App\Services\Noticias;

use App\Models\Noticia;
use App\Services\Media\ImageStorageService;
use App\Services\Translation\ApertiumTranslationService;
use App\Services\Translation\ProtectedTermsService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class NoticiaWriteService
{
    private ?string $translationWarning = null;

    public function __construct(
        private readonly ImageStorageService $imageStorage,
        private readonly ApertiumTranslationService $translationService,
        private readonly ProtectedTermsService $protectedTerms,
    ) {}

    public function create(array $data): Noticia
    {
        $this->resetTranslationWarning();

        $noticia = new Noticia;
        $newImagePath = null;

        try {
            $this->fillNoticia($noticia, $data, false, $newImagePath);
            $noticia->slug = $this->generateUniqueSlug($noticia->titulo_es ?: $noticia->titulo_val ?: $data['titulo']);
            $noticia->save();

            return $noticia;
        } catch (Throwable $exception) {
            $this->cleanupNewImage($newImagePath);

            throw $exception;
        }
    }

    public function update(Noticia $noticia, array $data): Noticia
    {
        $this->resetTranslationWarning();

        $newImagePath = null;
        $oldImagePath = $noticia->imagen_noticia;

        try {
            $this->fillNoticia($noticia, $data, true, $newImagePath);
            $noticia->slug = $this->generateUniqueSlug($noticia->titulo_es ?: $noticia->titulo_val ?: $data['titulo'], $noticia->id);
            $noticia->save();

            if ($newImagePath !== null && $oldImagePath && $oldImagePath !== $newImagePath) {
                $this->imageStorage->deleteFromDirectory($oldImagePath, 'noticias');
            }

            return $noticia->refresh();
        } catch (Throwable $exception) {
            $this->cleanupNewImage($newImagePath);

            throw $exception;
        }
    }

    public function delete(Noticia $noticia): void
    {
        $imagePath = $noticia->imagen_noticia;
        $noticia->delete();
        $this->imageStorage->deleteFromDirectory($imagePath, 'noticias');
    }

    public function consumeTranslationWarning(): ?string
    {
        $warning = $this->translationWarning;
        $this->translationWarning = null;

        return $warning;
    }

    private function fillNoticia(Noticia $noticia, array $data, bool $updating = false, ?string &$newImagePath = null): void
    {
        $translations = $this->resolveTranslations($data);

        $noticia->titulo = $translations['titulo_es'] ?: $translations['titulo_val'];
        $noticia->titulo_es = $translations['titulo_es'];
        $noticia->titulo_val = $translations['titulo_val'];
        $noticia->subtitulo = $translations['subtitulo_es'] ?: $translations['subtitulo_val'];
        $noticia->subtitulo_es = $translations['subtitulo_es'];
        $noticia->subtitulo_val = $translations['subtitulo_val'];
        $noticia->contenido = $translations['contenido_es'] ?: $translations['contenido_val'];
        $noticia->contenido_es = $translations['contenido_es'];
        $noticia->contenido_val = $translations['contenido_val'];
        $noticia->autor = $data['autor'];
        $noticia->oculto = filter_var($data['oculto'] ?? false, FILTER_VALIDATE_BOOL);

        if (($data['imagen_noticia'] ?? null) instanceof UploadedFile) {
            $newImagePath = $this->imageStorage->store($data['imagen_noticia'], 'noticias');
            $noticia->imagen_noticia = $newImagePath;
        }
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slugBase = Str::slug($title);
        $slugBase = $slugBase !== '' ? $slugBase : 'noticia';
        $slug = $slugBase;
        $counter = 1;

        while (
            Noticia::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $slugBase.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function resolveTranslations(array $data): array
    {
        $sourceLocale = ($data['idioma_origen'] ?? 'es') === 'val' ? 'val' : 'es';
        $protectedTerms = $this->buildProtectedTerms($data['autor'] ?? null);
        $title = (string) $data['titulo'];
        $subtitle = (string) ($data['subtitulo'] ?? '');
        $content = (string) ($data['contenido'] ?? '');

        try {
            if ($sourceLocale === 'val') {
                return [
                    'titulo_es' => $this->translationService->translate($title, $protectedTerms, 'cat_valencia|spa'),
                    'titulo_val' => $title,
                    'subtitulo_es' => $this->translationService->translate($subtitle, $protectedTerms, 'cat_valencia|spa'),
                    'subtitulo_val' => $subtitle,
                    'contenido_es' => $this->translationService->translate($content, $protectedTerms, 'cat_valencia|spa'),
                    'contenido_val' => $content,
                ];
            }

            return [
                'titulo_es' => $title,
                'titulo_val' => $this->translationService->translate($title, $protectedTerms),
                'subtitulo_es' => $subtitle,
                'subtitulo_val' => $this->translationService->translate($subtitle, $protectedTerms),
                'contenido_es' => $content,
                'contenido_val' => $this->translationService->translate($content, $protectedTerms),
            ];
        } catch (RuntimeException $exception) {
            $this->reportTranslationFallback('news', $sourceLocale, $exception);

            return $sourceLocale === 'val'
                ? [
                    'titulo_es' => null,
                    'titulo_val' => $title,
                    'subtitulo_es' => null,
                    'subtitulo_val' => $subtitle,
                    'contenido_es' => null,
                    'contenido_val' => $content,
                ]
                : [
                    'titulo_es' => $title,
                    'titulo_val' => null,
                    'subtitulo_es' => $subtitle,
                    'subtitulo_val' => null,
                    'contenido_es' => $content,
                    'contenido_val' => null,
                ];
        }
    }

    private function buildProtectedTerms(?string $author): array
    {
        return $this->protectedTerms->forNews($author);
    }

    private function resetTranslationWarning(): void
    {
        $this->translationWarning = null;
    }

    private function reportTranslationFallback(string $resource, string $sourceLocale, RuntimeException $exception): void
    {
        $this->translationWarning = __('admin.flash.translation_partial');

        Log::warning('Automatic translation failed while saving content.', [
            'resource' => $resource,
            'source_locale' => $sourceLocale,
            'error' => $exception->getMessage(),
        ]);
    }

    private function cleanupNewImage(?string $path): void
    {
        if ($path !== null) {
            $this->imageStorage->deleteFromDirectory($path, 'noticias');
        }
    }
}
