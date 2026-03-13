<?php

namespace App\Http\Controllers;

use App\Models\Galeria;
use App\Models\ImagenGaleria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GaleriaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $galerias = Galeria::withCount('imagenes')
            ->with(['imagenes' => function ($query) {
                $query->orderBy('id')->limit(1);
            }])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subquery) use ($search) {
                    $subquery->where('titulo', 'like', '%' . $search . '%')
                        ->orWhere('descripcion', 'like', '%' . $search . '%')
                        ->orWhere('tipo', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.galerias.index', [
            'galerias' => $galerias,
            'search' => $search,
            'tipos' => Galeria::TIPOS,
        ]);
    }

    public function create()
    {
        return view('admin.galerias.create', [
            'tipos' => Galeria::TIPOS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|min:3|max:255',
            'descripcion' => 'required|string|max:2000',
            'tipo' => 'required|in:' . implode(',', array_keys(Galeria::TIPOS)),
            'imagenes' => 'required|array|min:1',
            'imagenes.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            'imagen_principal_nueva' => 'nullable|integer|min:0',
            'oculto' => 'nullable|boolean',
        ]);

        $galeria = Galeria::create([
            'titulo' => $validated['titulo'],
            'slug' => $this->generarSlugUnico($validated['titulo']),
            'carpeta' => $this->generarCarpetaUnica($validated['titulo']),
            'descripcion' => $validated['descripcion'],
            'tipo' => $validated['tipo'],
            'oculto' => $request->boolean('oculto'),
        ]);

        $indicePrincipal = (int) $request->input('imagen_principal_nueva', 0);
        $this->guardarImagenes($galeria, $request->file('imagenes', []), null, $indicePrincipal);

        return redirect()->route('galerias.index')->with('mensaje', 'Galeria creada correctamente.');
    }

    public function show($id)
    {
        $galeria = Galeria::with('imagenes')->findOrFail($id);

        return view('admin.galerias.show', [
            'galeria' => $galeria,
            'tipos' => Galeria::TIPOS,
        ]);
    }

    public function edit($id)
    {
        $galeria = Galeria::with('imagenes')->findOrFail($id);

        return view('admin.galerias.edit', [
            'galeria' => $galeria,
            'tipos' => Galeria::TIPOS,
        ]);
    }

    public function update(Request $request, $id)
    {
        $galeria = Galeria::with('imagenes')->findOrFail($id);

        $validated = $request->validate([
            'titulo' => 'required|string|min:3|max:255',
            'descripcion' => 'required|string|max:2000',
            'tipo' => 'required|in:' . implode(',', array_keys(Galeria::TIPOS)),
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'eliminar_imagenes' => 'nullable|array',
            'eliminar_imagenes.*' => 'integer',
            'imagen_principal_existente' => 'nullable|integer',
            'imagen_principal_nueva' => 'nullable|integer|min:0',
            'oculto' => 'nullable|boolean',
        ]);

        $galeria->update([
            'titulo' => $validated['titulo'],
            'slug' => $this->generarSlugUnico($validated['titulo'], $galeria->id),
            'descripcion' => $validated['descripcion'],
            'tipo' => $validated['tipo'],
            'oculto' => $request->boolean('oculto'),
        ]);

        $idsAEliminar = collect($request->input('eliminar_imagenes', []))
            ->map(fn ($id) => (int) $id)
            ->all();

        $imagenesActuales = $galeria->imagenes->count();
        $imagenesNuevas = count(array_filter($request->file('imagenes', [])));
        $imagenesRestantes = $imagenesActuales - count($idsAEliminar) + $imagenesNuevas;

        if ($imagenesRestantes <= 0) {
            return back()
                ->withErrors(['imagenes' => 'La galeria debe tener al menos una imagen.'])
                ->withInput();
        }

        if (!empty($idsAEliminar)) {
            $imagenes = $galeria->imagenes()->whereIn('id', $idsAEliminar)->get();

            foreach ($imagenes as $imagen) {
                $this->borrarArchivoImagen($imagen->archivo);
                $imagen->delete();
            }
        }

        $imagenPrincipalExistente = $request->filled('imagen_principal_existente')
            ? (int) $request->input('imagen_principal_existente')
            : null;
        $indicePrincipalNueva = $request->filled('imagen_principal_nueva')
            ? (int) $request->input('imagen_principal_nueva')
            : null;

        $nuevasImagenes = $this->guardarImagenes($galeria, $request->file('imagenes', []), $imagenPrincipalExistente, $indicePrincipalNueva);
        $this->actualizarImagenPrincipal($galeria, $imagenPrincipalExistente, $nuevasImagenes, $indicePrincipalNueva);

        return redirect()->route('galerias.index')->with('mensaje', 'Galeria actualizada correctamente.');
    }

    public function destroy($id)
    {
        $galeria = Galeria::with('imagenes')->findOrFail($id);

        foreach ($galeria->imagenes as $imagen) {
            $this->borrarArchivoImagen($imagen->archivo);
        }

        $this->borrarCarpetaGaleriaSiExiste($galeria);

        $galeria->delete();

        return redirect()->route('galerias.index')->with('mensaje', 'Galeria eliminada correctamente.');
    }

    public function toggleOculto(Galeria $galeria)
    {
        $galeria->oculto = !$galeria->oculto;
        $galeria->save();

        return redirect()->route('galerias.index')->with('mensaje', $galeria->oculto ? 'Galeria ocultada de la web publica.' : 'Galeria visible de nuevo en la web publica.');
    }

    private function generarSlugUnico(string $titulo, ?int $ignorarId = null): string
    {
        $slugBase = Str::slug($titulo);
        $slugBase = $slugBase !== '' ? $slugBase : 'galeria';
        $slug = $slugBase;
        $contador = 1;

        while (
            Galeria::where('slug', $slug)
                ->when($ignorarId, fn ($query) => $query->where('id', '!=', $ignorarId))
                ->exists()
        ) {
            $slug = $slugBase . '-' . $contador;
            $contador++;
        }

        return $slug;
    }

    private function guardarImagenes(Galeria $galeria, array $imagenes, ?int $imagenPrincipalExistente = null, ?int $indicePrincipalNueva = null): array
    {
        $imagenesGuardadas = [];
        $hayImagenPrincipalActual = $galeria->imagenes()->where('es_principal', true)->exists();
        $directorioGaleria = $this->directorioGaleria($galeria);

        foreach ($imagenes as $imagen) {
            if (!$imagen) {
                continue;
            }

            if (!is_dir($directorioGaleria)) {
                mkdir($directorioGaleria, 0755, true);
            }

            $nombreImagen = time() . '_' . Str::random(8) . '.' . $imagen->getClientOriginalExtension();
            $imagen->move($directorioGaleria, $nombreImagen);

            $imagenesGuardadas[] = $galeria->imagenes()->create([
                'archivo' => $galeria->carpeta . '/' . $nombreImagen,
                'es_principal' => false,
            ]);
        }

        if (!$hayImagenPrincipalActual && isset($imagenesGuardadas[0])) {
            $imagenesGuardadas[0]->update(['es_principal' => true]);
        }

        if ($imagenPrincipalExistente === null && $indicePrincipalNueva !== null && isset($imagenesGuardadas[$indicePrincipalNueva])) {
            $this->marcarImagenPrincipal($galeria, $imagenesGuardadas[$indicePrincipalNueva]->id);
        }

        return $imagenesGuardadas;
    }

    private function borrarArchivoImagen(string $archivo): void
    {
        $ruta = public_path('galerias/' . $archivo);

        if (file_exists($ruta)) {
            unlink($ruta);
        }
    }

    private function generarCarpetaUnica(string $titulo): string
    {
        $base = Str::slug($titulo);
        $base = $base !== '' ? $base : 'galeria';
        $carpeta = $base;
        $contador = 1;

        while (Galeria::where('carpeta', $carpeta)->exists()) {
            $carpeta = $base . '-' . $contador;
            $contador++;
        }

        return $carpeta;
    }

    private function directorioGaleria(Galeria $galeria): string
    {
        return public_path('galerias/' . $galeria->carpeta);
    }

    private function borrarCarpetaGaleriaSiExiste(Galeria $galeria): void
    {
        $directorio = $this->directorioGaleria($galeria);

        if (is_dir($directorio)) {
            $archivos = array_diff(scandir($directorio), ['.', '..']);

            if (empty($archivos)) {
                rmdir($directorio);
            }
        }
    }

    private function actualizarImagenPrincipal(Galeria $galeria, ?int $imagenPrincipalExistente, array $nuevasImagenes, ?int $indicePrincipalNueva): void
    {
        if ($imagenPrincipalExistente !== null) {
            $imagenExiste = $galeria->imagenes()->where('id', $imagenPrincipalExistente)->exists();

            if ($imagenExiste) {
                $this->marcarImagenPrincipal($galeria, $imagenPrincipalExistente);
                return;
            }
        }

        if ($indicePrincipalNueva !== null && isset($nuevasImagenes[$indicePrincipalNueva])) {
            $this->marcarImagenPrincipal($galeria, $nuevasImagenes[$indicePrincipalNueva]->id);
            return;
        }

        $imagenActual = $galeria->imagenes()->where('es_principal', true)->first();

        if (!$imagenActual) {
            $primeraImagen = $galeria->imagenes()->orderBy('id')->first();

            if ($primeraImagen) {
                $this->marcarImagenPrincipal($galeria, $primeraImagen->id);
            }
        }
    }

    private function marcarImagenPrincipal(Galeria $galeria, int $imagenId): void
    {
        $galeria->imagenes()->update(['es_principal' => false]);
        $galeria->imagenes()->where('id', $imagenId)->update(['es_principal' => true]);
    }
}
