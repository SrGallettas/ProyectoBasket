<?php

namespace App\Services\Noticias;

use App\Models\Noticia;
use App\Services\Media\ImageStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class NoticiaWriteService
{
    public function __construct(
        private readonly ImageStorageService $imageStorage,
    ) {}

    public function create(array $data): Noticia
    {
        $noticia = new Noticia;
        $this->fillNoticia($noticia, $data);
        $noticia->slug = $this->generateUniqueSlug($data['titulo']);
        $noticia->save();

        return $noticia;
    }

    public function update(Noticia $noticia, array $data): Noticia
    {
        $this->fillNoticia($noticia, $data, true);
        $noticia->slug = $this->generateUniqueSlug($data['titulo'], $noticia->id);
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
        $noticia->titulo = $data['titulo'];
        $noticia->subtitulo = $data['subtitulo'];
        $noticia->contenido = $data['contenido'];
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
}
