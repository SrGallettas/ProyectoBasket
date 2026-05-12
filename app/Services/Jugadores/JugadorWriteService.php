<?php

namespace App\Services\Jugadores;

use App\Models\Jugador;
use App\Services\Media\ImageStorageService;
use Illuminate\Http\UploadedFile;

class JugadorWriteService
{
    public function __construct(
        private readonly ImageStorageService $imageStorage,
    ) {}

    public function create(array $data): Jugador
    {
        $jugador = new Jugador;
        $this->fillJugador($jugador, $data);
        $jugador->save();

        return $jugador;
    }

    public function update(Jugador $jugador, array $data): Jugador
    {
        $this->fillJugador($jugador, $data, true);
        $jugador->save();

        return $jugador->refresh();
    }

    public function delete(Jugador $jugador): void
    {
        $this->imageStorage->deleteFromDirectory($jugador->imagen_jugador, 'jugadores');
        $jugador->delete();
    }

    private function fillJugador(Jugador $jugador, array $data, bool $updating = false): void
    {
        $jugador->nombre = $data['nombre'];
        $jugador->apellido = $data['apellido'];
        $jugador->dorsal = $data['dorsal'];
        $jugador->posicion = $data['posicion'];
        $jugador->fecha_nacimiento = $data['fecha_nacimiento'];
        $jugador->equipo_id = $data['equipo_id'];
        $jugador->oculto = filter_var($data['oculto'] ?? false, FILTER_VALIDATE_BOOL);

        if (($data['imagen_jugador'] ?? null) instanceof UploadedFile) {
            if ($updating) {
                $this->imageStorage->deleteFromDirectory($jugador->imagen_jugador, 'jugadores');
            }

            $jugador->imagen_jugador = $this->imageStorage->store($data['imagen_jugador'], 'jugadores');
        }
    }
}
