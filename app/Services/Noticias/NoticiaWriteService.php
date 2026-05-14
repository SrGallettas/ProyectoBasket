<?php

namespace App\Services\Noticias;

use App\Models\Equipo;
use App\Models\Jugador;
use App\Models\Noticia;
use App\Models\Partido;
use App\Services\Media\ImageStorageService;
use App\Services\Translation\ApertiumTranslationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;

class NoticiaWriteService
{
    public function __construct(
        private readonly ImageStorageService $imageStorage,
        private readonly ApertiumTranslationService $translationService,
    ) {}

    public function create(array $data): Noticia
    {
        $noticia = new Noticia;
        $this->fillNoticia($noticia, $data);
        $noticia->slug = $this->generateUniqueSlug($noticia->titulo_es ?: $noticia->titulo_val ?: $data['titulo']);
        $noticia->save();

        return $noticia;
    }

    public function update(Noticia $noticia, array $data): Noticia
    {
        $this->fillNoticia($noticia, $data, true);
        $noticia->slug = $this->generateUniqueSlug($noticia->titulo_es ?: $noticia->titulo_val ?: $data['titulo'], $noticia->id);
        $noticia->save();

        return $noticia->refresh();
    }

    public function delete(Noticia $noticia): void
    {
        $this->imageStorage->deleteFromDirectory($noticia->imagen_noticia, 'noticias');
        $noticia->delete();
    }

    private function fillNoticia(Noticia $noticia, array $data, bool $updating = false): void
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
            if ($updating) {
                $this->imageStorage->deleteFromDirectory($noticia->imagen_noticia, 'noticias');
            }

            $noticia->imagen_noticia = $this->imageStorage->store($data['imagen_noticia'], 'noticias');
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
        } catch (RuntimeException) {
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
        $teamTerms = Equipo::query()
            ->select(['nombre', 'categoria'])
            ->get()
            ->flatMap(function (Equipo $equipo) {
                return [
                    $equipo->nombre,
                    $equipo->categoria,
                ];
            })
            ->all();

        $playerTerms = Jugador::query()
            ->select(['nombre', 'apellido'])
            ->get()
            ->flatMap(function (Jugador $jugador) {
                $fullName = trim($jugador->nombre.' '.$jugador->apellido);

                return array_filter([
                    $jugador->nombre,
                    $jugador->apellido,
                    $fullName !== '' ? $fullName : null,
                ]);
            })
            ->all();

        $venueTerms = Partido::query()
            ->select(['lugar', 'categoria'])
            ->get()
            ->flatMap(function (Partido $partido) {
                return array_filter([
                    $partido->lugar,
                    $partido->categoria,
                ]);
            })
            ->all();

        return array_values(array_unique(array_filter([
            $author,
            ...$teamTerms,
            ...$playerTerms,
            ...$venueTerms,
        ])));
    }
}
