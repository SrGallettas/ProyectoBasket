<?php

namespace App\Services\Equipos;

use App\Models\Equipo;
use App\Services\Media\ImageStorageService;
use Illuminate\Http\UploadedFile;

class EquipoWriteService
{
    public function __construct(
        private readonly ImageStorageService $imageStorage,
    ) {}

    public function create(array $data): Equipo
    {
        $equipo = new Equipo;
        $this->fillEquipo($equipo, $data);
        $equipo->save();

        return $equipo;
    }

    public function update(Equipo $equipo, array $data): Equipo
    {
        $this->fillEquipo($equipo, $data, true);
        $equipo->save();

        return $equipo->refresh();
    }

    public function delete(Equipo $equipo): bool
    {
        $tienePartidosAsociados = $equipo->partidosComoLocal()->exists()
            || $equipo->partidosComoVisitante()->exists();

        if ($tienePartidosAsociados) {
            return false;
        }

        $this->imageStorage->deleteFromDirectory($equipo->imagen_club, 'escudos');
        $this->imageStorage->deleteFromDirectory($equipo->foto_plantilla, 'equipos/plantillas');
        $equipo->delete();

        return true;
    }

    private function fillEquipo(Equipo $equipo, array $data, bool $updating = false): void
    {
        $equipo->nombre = $data['nombre'];
        $equipo->categoria = $data['categoria'];
        $equipo->es_local = array_key_exists('es_local', $data);
        $equipo->oculto = filter_var($data['oculto'] ?? false, FILTER_VALIDATE_BOOL);

        if (($data['imagen_club'] ?? null) instanceof UploadedFile) {
            if ($updating) {
                $this->imageStorage->deleteFromDirectory($equipo->imagen_club, 'escudos');
            }

            $equipo->imagen_club = $this->imageStorage->store($data['imagen_club'], 'escudos');
        }

        if (! $equipo->es_local && $equipo->foto_plantilla) {
            $this->imageStorage->deleteFromDirectory($equipo->foto_plantilla, 'equipos/plantillas');
            $equipo->foto_plantilla = null;
        }

        if ($equipo->es_local && ($data['foto_plantilla'] ?? null) instanceof UploadedFile) {
            if ($updating) {
                $this->imageStorage->deleteFromDirectory($equipo->foto_plantilla, 'equipos/plantillas');
            }

            $equipo->foto_plantilla = $this->imageStorage->store($data['foto_plantilla'], 'equipos/plantillas', 'plantilla_');
        }
    }
}
