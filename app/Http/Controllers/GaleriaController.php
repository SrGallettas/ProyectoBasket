<?php

namespace App\Http\Controllers;

// Modelo principal de este controlador.
// Representa una galería completa con sus datos generales.
use App\Models\Galeria;

// Este modelo representa cada imagen individual dentro de una galería.
use App\Models\ImagenGaleria;

// Request permite leer formularios y parámetros de la petición.
use Illuminate\Http\Request;

// Str se usa para generar slugs, carpetas limpias y nombres aleatorios.
use Illuminate\Support\Str;

// Storage permite guardar y borrar archivos físicos del disco.
use Illuminate\Support\Facades\Storage;

class GaleriaController extends Controller
{
    /*
     * LISTADO DE GALERÍAS
     *
     * Este método se usa en el panel admin para mostrar
     * todas las galerías creadas.
     * También permite buscar por título, descripción o tipo.
     *
     * Flujo:
     * 1. leer el texto del buscador
     * 2. construir la consulta
     * 3. cargar cuántas imágenes tiene cada galería
     * 4. traer también una imagen de portada
     * 5. ordenar, paginar y enviar a la vista
     */
    public function index(Request $request)
    {
        // Recogemos el texto que el usuario escribe en el buscador.
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'created_at');
        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Solo admitimos las columnas ordenables visibles en la tabla.
        $sortableColumns = [
            'titulo' => 'titulo',
            'tipo' => 'tipo',
            'imagenes_count' => 'imagenes_count',
            'slug' => 'slug',
            'oculto' => 'oculto',
            'created_at' => 'created_at',
        ];

        $sortColumn = $sortableColumns[$sort] ?? 'created_at';

        // Construimos la consulta base de galerías.
        // withCount('imagenes') añade el número total de imágenes.
        // with(['imagenes' => ...]) trae solo una imagen para usarla como vista previa.
        $galerias = Galeria::withCount('imagenes')
            ->with(['imagenes' => function ($query) {
                $query->orderBy('id')->limit(1);
            }])
            ->when($search, function ($query) use ($search) {
                // Si hay búsqueda, filtramos por varios campos de texto.
                $query->where(function ($subquery) use ($search) {
                    $subquery->where('titulo', 'like', '%' . $search . '%')
                        ->orWhere('descripcion', 'like', '%' . $search . '%')
                        ->orWhere('tipo', 'like', '%' . $search . '%');
                });
            });

        // En columnas de texto ignoramos mayúsculas al ordenar.
        if (in_array($sortColumn, ['titulo', 'tipo', 'slug'], true)) {
            $galerias->orderByRaw('LOWER(' . $sortColumn . ') ' . $direction);
        } else {
            $galerias->orderBy($sortColumn, $direction);
        }

        $galerias = $galerias
            ->orderBy('created_at', 'desc')
            ->paginate(9)
            ->withQueryString();

        // Enviamos a la vista las galerías, la búsqueda y los tipos disponibles.
        return view('admin.galerias.index', [
            'galerias' => $galerias,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'tipos' => Galeria::TIPOS,
        ]);
    }

    /*
     * FORMULARIO DE CREACIÓN
     *
     * Este método no guarda nada.
     * Solo prepara la vista para crear una galería nueva.
     */
    public function create()
    {
        return view('admin.galerias.create', [
            'tipos' => Galeria::TIPOS,
        ]);
    }

    /*
     * GUARDAR GALERÍA NUEVA
     *
     * Este método procesa el formulario de creación.
     *
     * Qué hace:
     * 1. valida datos básicos e imágenes
     * 2. crea el registro principal de la galería
     * 3. genera slug y carpeta únicos
     * 4. guarda las imágenes en disco y en base de datos
     * 5. marca una imagen principal
     */
    public function store(Request $request)
    {
        // Validamos el formulario principal y el array de imágenes subidas.
        $validated = $request->validate([
            'titulo' => 'required|string|min:3|max:255',
            'descripcion' => 'required|string|max:2000',
            'tipo' => 'required|in:' . implode(',', array_keys(Galeria::TIPOS)),
            'imagenes' => 'required|array|min:1',
            'imagenes.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            'imagen_principal_nueva' => 'nullable|integer|min:0',
            'oculto' => 'nullable|boolean',
        ]);

        // Creamos primero la galería, sin las imágenes.
        // El slug se usará en URLs y la carpeta agrupa físicamente sus archivos.
        $galeria = Galeria::create([
            'titulo' => $validated['titulo'],
            'slug' => $this->generarSlugUnico($validated['titulo']),
            'carpeta' => $this->generarCarpetaUnica($validated['titulo']),
            'descripcion' => $validated['descripcion'],
            'tipo' => $validated['tipo'],
            'oculto' => $request->boolean('oculto'),
        ]);

        // El formulario puede indicar cuál de las imágenes nuevas será la principal.
        $indicePrincipal = (int) $request->input('imagen_principal_nueva', 0);

        // Guardamos las imágenes físicas y sus filas en la base de datos.
        $this->guardarImagenes($galeria, $request->file('imagenes', []), null, $indicePrincipal);

        return redirect()->route('galerias.index')->with('mensaje', 'Galeria creada correctamente.');
    }

    /*
     * VER DETALLE DE GALERÍA
     *
     * Carga una galería concreta junto con todas sus imágenes
     * para mostrarla en la vista de detalle.
     */
    public function show($id)
    {
        // Busca la galería o lanza 404 si no existe.
        $galeria = Galeria::with('imagenes')->findOrFail($id);

        return view('admin.galerias.show', [
            'galeria' => $galeria,
            'tipos' => Galeria::TIPOS,
        ]);
    }

    /*
     * FORMULARIO DE EDICIÓN
     *
     * Busca una galería ya existente y carga la vista de edición.
     * También trae sus imágenes actuales para poder gestionarlas.
     */
    public function edit($id)
    {
        // Busca la galería o lanza 404 si no existe.
        $galeria = Galeria::with('imagenes')->findOrFail($id);

        return view('admin.galerias.edit', [
            'galeria' => $galeria,
            'tipos' => Galeria::TIPOS,
        ]);
    }

    /*
     * ACTUALIZAR GALERÍA
     *
     * Este método guarda cambios sobre una galería ya existente.
     *
     * Qué hace:
     * 1. valida datos básicos, imágenes nuevas y borrados
     * 2. actualiza título, descripción, tipo y visibilidad
     * 3. comprueba que la galería no se quede sin imágenes
     * 4. elimina imágenes marcadas
     * 5. guarda imágenes nuevas
     * 6. recalcula cuál es la imagen principal
     */
    public function update(Request $request, $id)
    {
        // Cargamos la galería con sus imágenes actuales.
        $galeria = Galeria::with('imagenes')->findOrFail($id);

        // Validamos tanto los campos básicos como las operaciones sobre imágenes.
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

        // Actualizamos los datos generales de la galería.
        // El slug se recalcula por si el título ha cambiado.
        $galeria->update([
            'titulo' => $validated['titulo'],
            'slug' => $this->generarSlugUnico($validated['titulo'], $galeria->id),
            'descripcion' => $validated['descripcion'],
            'tipo' => $validated['tipo'],
            'oculto' => $request->boolean('oculto'),
        ]);

        // Normalizamos los IDs de imágenes que el admin quiere borrar.
        $idsAEliminar = collect($request->input('eliminar_imagenes', []))
            ->map(fn ($id) => (int) $id)
            ->all();

        // Calculamos cuántas imágenes quedarían tras aplicar borrados y altas nuevas.
        // La galería no puede quedarse vacía.
        $imagenesActuales = $galeria->imagenes->count();
        $imagenesNuevas = count(array_filter($request->file('imagenes', [])));
        $imagenesRestantes = $imagenesActuales - count($idsAEliminar) + $imagenesNuevas;

        if ($imagenesRestantes <= 0) {
            return back()
                ->withErrors(['imagenes' => 'La galeria debe tener al menos una imagen.'])
                ->withInput();
        }

        // Borramos primero las imágenes marcadas, tanto del disco como de la base de datos.
        if (!empty($idsAEliminar)) {
            $imagenes = $galeria->imagenes()->whereIn('id', $idsAEliminar)->get();

            foreach ($imagenes as $imagen) {
                $this->borrarArchivoImagen($imagen->archivo);
                $imagen->delete();
            }
        }

        // El formulario puede marcar como principal una imagen existente
        // o una de las nuevas que todavía no se ha guardado.
        $imagenPrincipalExistente = $request->filled('imagen_principal_existente')
            ? (int) $request->input('imagen_principal_existente')
            : null;
        $indicePrincipalNueva = $request->filled('imagen_principal_nueva')
            ? (int) $request->input('imagen_principal_nueva')
            : null;

        // Guardamos nuevas imágenes y después resolvemos cuál debe ser la principal final.
        $nuevasImagenes = $this->guardarImagenes($galeria, $request->file('imagenes', []), $imagenPrincipalExistente, $indicePrincipalNueva);
        $this->actualizarImagenPrincipal($galeria, $imagenPrincipalExistente, $nuevasImagenes, $indicePrincipalNueva);

        return redirect()->route('galerias.index')->with('mensaje', 'Galeria actualizada correctamente.');
    }

    /*
     * ELIMINAR GALERÍA
     *
     * Este método borra:
     * 1. los archivos físicos de sus imágenes
     * 2. la carpeta de la galería si queda vacía
     * 3. el registro de la galería
     */
    public function destroy($id)
    {
        // Cargamos la galería junto con todas sus imágenes.
        $galeria = Galeria::with('imagenes')->findOrFail($id);

        // Primero se eliminan los archivos del disco.
        foreach ($galeria->imagenes as $imagen) {
            $this->borrarArchivoImagen($imagen->archivo);
        }

        // Después se intenta borrar la carpeta si ya no queda nada dentro.
        $this->borrarCarpetaGaleriaSiExiste($galeria);

        // Finalmente se borra el registro principal.
        $galeria->delete();

        return redirect()->route('galerias.index')->with('mensaje', 'Galeria eliminada correctamente.');
    }

    /*
     * OCULTAR / MOSTRAR GALERÍA
     *
     * No borra la galería.
     * Solo cambia el booleano "oculto" para controlar su visibilidad pública.
     */
    public function toggleOculto(Galeria $galeria)
    {
        // Invertimos el valor actual.
        $galeria->oculto = !$galeria->oculto;
        $galeria->save();

        return redirect()->route('galerias.index')->with('mensaje', $galeria->oculto ? 'Galeria ocultada de la web publica.' : 'Galeria visible de nuevo en la web publica.');
    }

    /*
     * GENERAR SLUG ÚNICO
     *
     * Convierte el título en un slug legible para URL.
     * Si ya existe otro igual, añade un número al final.
     *
     * En edición se puede ignorar el ID actual para no chocar consigo misma.
     */
    private function generarSlugUnico(string $titulo, ?int $ignorarId = null): string
    {
        // Str::slug limpia acentos, espacios y símbolos.
        $slugBase = Str::slug($titulo);
        $slugBase = $slugBase !== '' ? $slugBase : 'galeria';
        $slug = $slugBase;
        $contador = 1;

        // Si el slug ya existe, se añaden sufijos numéricos hasta encontrar uno libre.
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

    /*
     * GUARDAR IMÁGENES DE LA GALERÍA
     *
     * Este helper guarda en disco las imágenes nuevas y crea sus registros.
     *
     * Además:
     * - marca una principal automática si todavía no había ninguna
     * - puede respetar una principal existente
     * - puede marcar como principal una imagen nueva concreta
     */
    private function guardarImagenes(Galeria $galeria, array $imagenes, ?int $imagenPrincipalExistente = null, ?int $indicePrincipalNueva = null): array
    {
        // Aquí guardaremos los modelos ImagenGaleria creados en esta operación.
        $imagenesGuardadas = [];

        // Comprobamos si la galería ya tiene una imagen principal definida.
        $hayImagenPrincipalActual = $galeria->imagenes()->where('es_principal', true)->exists();

        foreach ($imagenes as $imagen) {
            // Si alguna posición del array viene vacía, se ignora.
            if (!$imagen) {
                continue;
            }

            // Se genera un nombre único para evitar colisiones en disco.
            $nombreImagen = time() . '_' . Str::random(8) . '.' . $imagen->getClientOriginalExtension();

            // El archivo se guarda dentro de la carpeta propia de la galería.
            $imagen->storeAs('galerias/' . $galeria->carpeta, $nombreImagen, 'public');

            // En base de datos se guarda la ruta relativa interna de la galería.
            $imagenesGuardadas[] = $galeria->imagenes()->create([
                'archivo' => $galeria->carpeta . '/' . $nombreImagen,
                'es_principal' => false,
            ]);
        }

        // Si todavía no existía principal, la primera imagen guardada pasa a serlo.
        if (!$hayImagenPrincipalActual && isset($imagenesGuardadas[0])) {
            $imagenesGuardadas[0]->update(['es_principal' => true]);
        }

        // En creación o en ciertas ediciones, una imagen nueva concreta puede marcarse como principal.
        if ($imagenPrincipalExistente === null && $indicePrincipalNueva !== null && isset($imagenesGuardadas[$indicePrincipalNueva])) {
            $this->marcarImagenPrincipal($galeria, $imagenesGuardadas[$indicePrincipalNueva]->id);
        }

        return $imagenesGuardadas;
    }

    /*
     * BORRAR ARCHIVO DE IMAGEN
     *
     * El campo "archivo" ya guarda la carpeta propia de la galería,
     * así que aquí solo añadimos el prefijo general "galerias/".
     */
    private function borrarArchivoImagen(string $archivo): void
    {
        Storage::disk('public')->delete('galerias/' . ltrim($archivo, '/'));
    }

    /*
     * GENERAR CARPETA ÚNICA
     *
     * Crea el nombre de carpeta física donde se guardarán
     * las imágenes de la galería.
     * Si el nombre ya existe, añade un sufijo numérico.
     */
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

    /*
     * DIRECTORIO DE LA GALERÍA
     *
     * Devuelve la ruta relativa de storage donde viven
     * las imágenes de una galería concreta.
     */
    private function directorioGaleria(Galeria $galeria): string
    {
        return 'galerias/' . $galeria->carpeta;
    }

    /*
     * BORRAR CARPETA SI ESTÁ VACÍA
     *
     * Este helper evita dejar carpetas vacías en storage
     * después de eliminar una galería.
     */
    private function borrarCarpetaGaleriaSiExiste(Galeria $galeria): void
    {
        $directorio = $this->directorioGaleria($galeria);

        // Solo se elimina si existe y no contiene ya ningún archivo.
        if (Storage::disk('public')->exists($directorio) && empty(Storage::disk('public')->files($directorio))) {
            Storage::disk('public')->deleteDirectory($directorio);
        }
    }

    /*
     * ACTUALIZAR IMAGEN PRINCIPAL
     *
     * Este helper decide cuál debe quedar marcada como principal
     * después de una edición.
     *
     * Prioridad:
     * 1. una imagen existente elegida explícitamente
     * 2. una imagen nueva marcada como principal
     * 3. si no queda principal, usar la primera disponible
     */
    private function actualizarImagenPrincipal(Galeria $galeria, ?int $imagenPrincipalExistente, array $nuevasImagenes, ?int $indicePrincipalNueva): void
    {
        // Si el admin ha elegido una imagen existente y sigue existiendo, manda esa opción.
        if ($imagenPrincipalExistente !== null) {
            $imagenExiste = $galeria->imagenes()->where('id', $imagenPrincipalExistente)->exists();

            if ($imagenExiste) {
                $this->marcarImagenPrincipal($galeria, $imagenPrincipalExistente);
                return;
            }
        }

        // Si no, se intenta con la imagen nueva elegida en el formulario.
        if ($indicePrincipalNueva !== null && isset($nuevasImagenes[$indicePrincipalNueva])) {
            $this->marcarImagenPrincipal($galeria, $nuevasImagenes[$indicePrincipalNueva]->id);
            return;
        }

        // Si por cualquier motivo no hay principal, recuperamos una válida automáticamente.
        $imagenActual = $galeria->imagenes()->where('es_principal', true)->first();

        if (!$imagenActual) {
            $primeraImagen = $galeria->imagenes()->orderBy('id')->first();

            // La primera imagen disponible pasa a ser la principal de seguridad.
            if ($primeraImagen) {
                $this->marcarImagenPrincipal($galeria, $primeraImagen->id);
            }
        }
    }

    /*
     * MARCAR IMAGEN PRINCIPAL
     *
     * Primero desmarca todas las imágenes de la galería
     * y luego marca la elegida.
     */
    private function marcarImagenPrincipal(Galeria $galeria, int $imagenId): void
    {
        $galeria->imagenes()->update(['es_principal' => false]);
        $galeria->imagenes()->where('id', $imagenId)->update(['es_principal' => true]);
    }
}
