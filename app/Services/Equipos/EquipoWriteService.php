<?php

namespace App\Services\Equipos;

use App\Models\Equipo;
use App\Services\Media\ImageStorageService;
use Illuminate\Http\UploadedFile;
use Throwable;

class EquipoWriteService
{
    public function __construct(
        private readonly ImageStorageService $imageStorage,
    ) {}

    public function create(array $data): Equipo
    {
        $equipo = new Equipo;
        $newShieldPath = null;
        $newPhotoPath = null;

        try {
            $this->fillEquipo($equipo, $data, false, $newShieldPath, $newPhotoPath);
            $equipo->save();

            return $equipo;
        } catch (Throwable $exception) {
            $this->cleanupNewImage($newShieldPath, 'escudos');
            $this->cleanupNewImage($newPhotoPath, 'equipos/plantillas');

            throw $exception;
        }
    }

    public function update(Equipo $equipo, array $data): Equipo
    {
        $oldShieldPath = $equipo->imagen_club;
        $oldPhotoPath = $equipo->foto_plantilla;
        $newShieldPath = null;
        $newPhotoPath = null;
        $shouldRemoveOldPhoto = false;

        try {
            $this->fillEquipo($equipo, $data, true, $newShieldPath, $newPhotoPath, $shouldRemoveOldPhoto);
            $equipo->save();

            if ($newShieldPath !== null && $oldShieldPath && $oldShieldPath !== $newShieldPath) {
                $this->imageStorage->deleteFromDirectory($oldShieldPath, 'escudos');
            }

            if (($newPhotoPath !== null || $shouldRemoveOldPhoto) && $oldPhotoPath && $oldPhotoPath !== $newPhotoPath) {
                $this->imageStorage->deleteFromDirectory($oldPhotoPath, 'equipos/plantillas');
            }

            return $equipo->refresh();
        } catch (Throwable $exception) {
            $this->cleanupNewImage($newShieldPath, 'escudos');
            $this->cleanupNewImage($newPhotoPath, 'equipos/plantillas');

            throw $exception;
        }
    }

    public function delete(Equipo $equipo): bool
    {
        $tienePartidosAsociados = $equipo->partidosComoLocal()->exists()
            || $equipo->partidosComoVisitante()->exists();

        if ($tienePartidosAsociados) {
            return false;
        }

        $shieldPath = $equipo->imagen_club;
        $photoPath = $equipo->foto_plantilla;

        $equipo->delete();
        $this->imageStorage->deleteFromDirectory($shieldPath, 'escudos');
        $this->imageStorage->deleteFromDirectory($photoPath, 'equipos/plantillas');

        return true;
    }

    private function fillEquipo(
        Equipo $equipo,
        array $data,
        bool $updating = false,
        ?string &$newShieldPath = null,
        ?string &$newPhotoPath = null,
        bool &$shouldRemoveOldPhoto = false
    ): void {
        $equipo->nombre = $data['nombre'];
        $equipo->categoria = $data['categoria'];
        $equipo->es_local = array_key_exists('es_local', $data);
        $equipo->oculto = filter_var($data['oculto'] ?? false, FILTER_VALIDATE_BOOL);

        if (($data['imagen_club'] ?? null) instanceof UploadedFile) {
            $newShieldPath = $this->imageStorage->store($data['imagen_club'], 'escudos');
            $equipo->imagen_club = $newShieldPath;
        }

        if (! $equipo->es_local && $equipo->foto_plantilla) {
            $shouldRemoveOldPhoto = true;
            $equipo->foto_plantilla = null;
        }

        if ($equipo->es_local && ($data['foto_plantilla'] ?? null) instanceof UploadedFile) {
            $newPhotoPath = $this->imageStorage->store($data['foto_plantilla'], 'equipos/plantillas', 'plantilla_');
            $equipo->foto_plantilla = $newPhotoPath;
        }
    }

    private function cleanupNewImage(?string $path, string $directory): void
    {
        if ($path !== null) {
            $this->imageStorage->deleteFromDirectory($path, $directory);
        }
    }
}
