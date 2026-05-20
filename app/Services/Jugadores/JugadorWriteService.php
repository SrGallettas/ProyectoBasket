<?php

namespace App\Services\Jugadores;

use App\Models\Jugador;
use App\Services\Media\ImageStorageService;
use Illuminate\Http\UploadedFile;
use Throwable;

class JugadorWriteService
{
    public function __construct(
        private readonly ImageStorageService $imageStorage,
    ) {}

    public function create(array $data): Jugador
    {
        $jugador = new Jugador;
        $newImagePath = null;

        try {
            $this->fillJugador($jugador, $data, false, $newImagePath);
            $jugador->save();

            return $jugador;
        } catch (Throwable $exception) {
            $this->cleanupNewImage($newImagePath);

            throw $exception;
        }
    }

    public function update(Jugador $jugador, array $data): Jugador
    {
        $oldImagePath = $jugador->imagen_jugador;
        $newImagePath = null;

        try {
            $this->fillJugador($jugador, $data, true, $newImagePath);
            $jugador->save();

            if ($newImagePath !== null && $oldImagePath && $oldImagePath !== $newImagePath) {
                $this->imageStorage->deleteFromDirectory($oldImagePath, 'jugadores');
            }

            return $jugador->refresh();
        } catch (Throwable $exception) {
            $this->cleanupNewImage($newImagePath);

            throw $exception;
        }
    }

    public function delete(Jugador $jugador): void
    {
        $imagePath = $jugador->imagen_jugador;
        $jugador->delete();
        $this->imageStorage->deleteFromDirectory($imagePath, 'jugadores');
    }

    private function fillJugador(Jugador $jugador, array $data, bool $updating = false, ?string &$newImagePath = null): void
    {
        $jugador->nombre = $data['nombre'];
        $jugador->apellido = $data['apellido'];
        $jugador->dorsal = $data['dorsal'];
        $jugador->posicion = $data['posicion'];
        $jugador->fecha_nacimiento = $data['fecha_nacimiento'];
        $jugador->equipo_id = $data['equipo_id'];
        $jugador->oculto = filter_var($data['oculto'] ?? false, FILTER_VALIDATE_BOOL);

        if (($data['imagen_jugador'] ?? null) instanceof UploadedFile) {
            $newImagePath = $this->imageStorage->store($data['imagen_jugador'], 'jugadores');
            $jugador->imagen_jugador = $newImagePath;
        }
    }

    private function cleanupNewImage(?string $path): void
    {
        if ($path !== null) {
            $this->imageStorage->deleteFromDirectory($path, 'jugadores');
        }
    }
}
