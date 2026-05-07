<?php

namespace App\Http\Controllers;

// Importamos el modelo Equipo para poder leer y guardar equipos en la base de datos.
use App\Models\Equipo;

// Request permite acceder a lo que llega desde formularios o desde la URL.
use Illuminate\Http\Request;

// Storage se usa para guardar y borrar imágenes en el disco.
use Illuminate\Support\Facades\Storage;

class EquipoController extends Controller
{
    /*
       * LISTADO DE EQUIPOS
       *
       * Este método se usa en el panel de administración para mostrar todos los equipos.
       * Además incluye buscador y paginación.
       *
       * Flujo:
       * 1. leer lo que el usuario ha escrito en el buscador
       * 2. construir la consulta
       * 3. si hay texto, filtrar por nombre
       * 4. ordenar por nombre
       * 5. paginar resultados
       * 6. enviar todo a la vista
       */
    public function index(Request $request)
    {
        // Leemos el texto del buscador.
        // Si en la URL viene algo como ?search=Senior, aquí lo recogemos.
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'nombre');
        $direction = strtolower((string) $request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Solo permitimos columnas conocidas para no abrir la puerta
        // a ordenaciones arbitrarias desde la URL.
        $sortableColumns = [
            'nombre' => 'nombre',
            'categoria' => 'categoria',
            'jugadores_count' => 'jugadores_count',
            'oculto' => 'oculto',
        ];

        $sortColumn = $sortableColumns[$sort] ?? 'nombre';

        // Construimos la consulta de equipos.
        // withCount('jugadores') añade un contador de jugadores por cada equipo.
        // Así la vista puede mostrar cuántos jugadores tiene cada uno sin hacer otra consulta aparte.
        $equipos = Equipo::withCount('jugadores')
            ->when($search, function ($query) use ($search) {
                // "when" significa:
                // solo aplica este bloque si $search tiene valor.
                // Si el usuario ha buscado algo, filtramos por nombre con LIKE.
                $query->where('nombre', 'like', '%' . $search . '%');
            });

        // En texto usamos lower(...) para que las mayúsculas no rompan el abecedario.
        if (in_array($sortColumn, ['nombre', 'categoria'], true)) {
            $equipos->orderByRaw('LOWER(' . $sortColumn . ') ' . $direction);
        } else {
            $equipos->orderBy($sortColumn, $direction);
        }

        $equipos = $equipos
            ->orderByRaw('LOWER(nombre) asc')
            ->paginate(10) // 10 equipos por página
            ->withQueryString(); // mantiene ?search=... al cambiar de página

        // Mandamos datos a la vista del listado.
        return view('admin.equipos.index', compact('equipos', 'search', 'sort', 'direction'));
    }

    /*
       * FORMULARIO DE CREACIÓN
       *
       * Este método no guarda nada.
       * Solo prepara y devuelve la vista para crear un equipo.
       */
    public function create()
    {
        // categoryOptions() devuelve una lista de categorías predefinidas
        // como "Senior", "Senior B", "Junior B", etc.
        $categoryOptions = $this->categoryOptions();

        // Carga la vista del formulario de creación.
        return view('admin.equipos.create', compact('categoryOptions'));
    }

    /*
       * GUARDAR UN EQUIPO NUEVO
       *
       * Este método procesa el formulario de creación.
       *
       * Qué hace:
       * 1. valida los datos
       * 2. crea un objeto Equipo vacío
       * 3. asigna los campos básicos
       * 4. guarda imagen de escudo si existe
       * 5. guarda foto de plantilla si existe y si es equipo local
       * 6. guarda el registro en la base de datos
       * 7. vuelve al listado
       */
    public function store(Request $request)
    {
        // Validación del formulario.
        // Si algo falla, Laravel vuelve automáticamente atrás con errores.
        $request->validate([
            'nombre'      => 'required|string|min:5|max:255',
            'categoria'   => 'required|string',
            'imagen_club' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_plantilla' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'oculto'      => 'nullable|boolean',
        ], [
            'nombre.min'      => 'El título debe tener al menos 5 caracteres.',
            'nombre.required' => 'El nombre del equipo es obligatorio.',
            'categoria.required' => 'La categoría es obligatoria.',
            'imagen_club.image'  => 'El archivo debe ser una imagen.',
            'imagen_club.mimes'  => 'Solo se permiten formatos: jpg, jpeg, png.',
            'imagen_club.max'    => 'La imagen es demasiado pesada (máximo 2MB).',
            'foto_plantilla.image' => 'La foto de plantilla debe ser una imagen.',
            'foto_plantilla.mimes' => 'La foto de plantilla solo admite jpg, jpeg, png o
  webp.',
            'foto_plantilla.max' => 'La foto de plantilla es demasiado pesada (máximo
  4MB).',
        ]);

        // Creamos una instancia vacía del modelo.
        $equipo = new Equipo();

        // Asignamos datos básicos que vienen del formulario.
        $equipo->nombre = $request->nombre;
        $equipo->categoria = $request->categoria;

        // es_local depende de si el checkbox estaba marcado.
        // has('es_local') devuelve true si ese campo existe en el request.
        $equipo->es_local = $request->has('es_local');

        // boolean('oculto') transforma el valor en true o false.
        $equipo->oculto = $request->boolean('oculto');

        // Si el usuario subió un escudo...
        if ($request->hasFile('imagen_club')) {
            $imagen = $request->file('imagen_club');

            // Creamos un nombre de archivo usando time()
            // para evitar choques con otros archivos.
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();

            // Guardamos el archivo en storage/app/public/escudos
            $imagen->storeAs('escudos', $nombreImagen, 'public');

            // En la base de datos solo guardamos el nombre del archivo, no la imagen en sí.
            $equipo->imagen_club = $nombreImagen;
        }

        // La foto de plantilla solo tiene sentido si el equipo es local.
        if ($equipo->es_local && $request->hasFile('foto_plantilla')) {
            $imagenPlantilla = $request->file('foto_plantilla');

            // Se usa otro prefijo para diferenciar este tipo de imagen.
            $nombreImagenPlantilla = 'plantilla_' . time() . '.' .
                $imagenPlantilla->getClientOriginalExtension();

            // Guardamos la foto en otra carpeta distinta.
            $imagenPlantilla->storeAs(
                'equipos/plantillas',
                $nombreImagenPlantilla,
                'public'
            );

            $equipo->foto_plantilla = $nombreImagenPlantilla;
        }

        // Ahora sí, se guarda todo en base de datos.
        $equipo->save();

        // Redirige al listado con mensaje.
        return redirect()->route('equipos.index')->with('mensaje', 'Equipo creado');
    }

    /*
       * ELIMINAR EQUIPO
       *
       * Este método borra un equipo, pero antes comprueba si tiene partidos asociados.
       *
       * Esto es importante:
       * si un equipo ya está relacionado con partidos, borrarlo rompería coherencia de datos.
       * Por eso primero se protege esa eliminación.
       */
    public function destroy($id)
    {
        // Busca el equipo por ID o lanza 404 si no existe.
        $equipo = Equipo::findOrFail($id);

        // Comprobamos si aparece en partidos como local o visitante.
        $tienePartidosAsociados = $equipo->partidosComoLocal()->exists()
            || $equipo->partidosComoVisitante()->exists();

        // Si tiene partidos asociados, no dejamos borrarlo.
        if ($tienePartidosAsociados) {
            return redirect()
                ->route('equipos.index')
                ->withErrors([
                    'delete' => 'No puedes eliminar este equipo porque tiene partidos
  asociados.',
                ]);
        }

        // Si tenía imágenes propias, las borramos del almacenamiento.
        $this->deleteStoredImage($equipo->imagen_club, 'escudos');
        $this->deleteStoredImage($equipo->foto_plantilla, 'equipos/plantillas');

        // Borramos el registro de la base de datos.
        $equipo->delete();

        return redirect()->route('equipos.index');
    }

    /*
       * FORMULARIO DE EDICIÓN
       *
       * Este método busca un equipo existente y abre la vista de edición.
       * También prepara las categorías disponibles.
       */
    public function edit($id)
    {
        // Buscamos el equipo que se va a editar.
        $equipo = Equipo::findOrFail($id);

        // Sacamos la lista base de categorías.
        $categoryOptions = $this->categoryOptions();

        // Si la categoría actual del equipo no está en la lista base,
        // la añadimos para que no desaparezca del selector.
        if (!in_array($equipo->categoria, $categoryOptions, true)) {
            $categoryOptions[] = $equipo->categoria;
        }

        return view('admin.equipos.edit', compact('equipo', 'categoryOptions'));
    }

    /*
       * ACTUALIZAR EQUIPO
       *
       * Este método guarda cambios sobre un equipo ya existente.
       *
       * Qué hace:
       * 1. busca el equipo
       * 2. valida datos
       * 3. actualiza campos básicos
       * 4. si hay nueva imagen, borra la vieja y guarda la nueva
       * 5. si deja de ser local, elimina foto de plantilla
       * 6. si sigue siendo local y suben otra plantilla, la reemplaza
       * 7. guarda en base de datos
       */
    public function update(Request $request, $id)
    {
        // Buscamos el registro actual.
        $equipo = Equipo::findOrFail($id);

        // Se asignan estos campos antes de validar, aunque normalmente
        // suele validarse primero. Aquí funciona porque luego si la validación falla,
        // Laravel corta el flujo y no llega a guardar.
        $equipo->nombre = $request->nombre;
        $equipo->categoria = $request->categoria;

        // Validación igual que en store().
        $request->validate([
            'nombre'      => 'required|string|min:5|max:255',
            'categoria'   => 'required|string',
            'imagen_club' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_plantilla' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'oculto'      => 'nullable|boolean',
        ], [
            'nombre.min'      => 'El título debe tener al menos 5 caracteres.',
            'nombre.required' => 'El nombre del equipo es obligatorio.',
            'categoria.required' => 'La categoría es obligatoria.',
            'imagen_club.image'  => 'El archivo debe ser una imagen.',
            'imagen_club.mimes'  => 'Solo se permiten formatos: jpg, jpeg, png.',
            'imagen_club.max'    => 'La imagen es demasiado pesada (máximo 2MB).',
            'foto_plantilla.image' => 'La foto de plantilla debe ser una imagen.',
            'foto_plantilla.mimes' => 'La foto de plantilla solo admite jpg, jpeg, png o
  webp.',
            'foto_plantilla.max' => 'La foto de plantilla es demasiado pesada (máximo
  4MB).',
        ]);

        // Se recalculan estos booleanos desde el formulario.
        $equipo->es_local = $request->has('es_local');
        $equipo->oculto = $request->boolean('oculto');

        // Si han subido un nuevo escudo...
        if ($request->hasFile('imagen_club')) {
            // Antes de guardar el nuevo, borramos el antiguo para no dejar basura.
            $this->deleteStoredImage($equipo->imagen_club, 'escudos');

            $imagen = $request->file('imagen_club');
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();
            $imagen->storeAs('escudos', $nombreImagen, 'public');

            // Ahora el equipo apunta a la nueva imagen.
            $equipo->imagen_club = $nombreImagen;
        }

        // Si el equipo deja de ser local, ya no debería tener foto de plantilla.
        if (!$equipo->es_local && $equipo->foto_plantilla) {
            $this->deleteStoredImage($equipo->foto_plantilla, 'equipos/plantillas');
            $equipo->foto_plantilla = null;
        }

        // Si sí es local y además suben nueva foto de plantilla, la reemplazamos.
        if ($equipo->es_local && $request->hasFile('foto_plantilla')) {
            $this->deleteStoredImage($equipo->foto_plantilla, 'equipos/plantillas');

            $imagenPlantilla = $request->file('foto_plantilla');
            $nombreImagenPlantilla = 'plantilla_' . time() . '.' .
                $imagenPlantilla->getClientOriginalExtension();
            $imagenPlantilla->storeAs(
                'equipos/plantillas',
                $nombreImagenPlantilla,
                'public'
            );

            $equipo->foto_plantilla = $nombreImagenPlantilla;
        }

        // Persistimos cambios.
        $equipo->save();

        return redirect()->route('equipos.index')->with('mensaje', 'Equipo actualizado
  correctamente');
    }

    /*
       * VER DETALLE DE UN EQUIPO
       *
       * Método simple: busca un equipo por ID y lo manda a la vista show.
       */
    public function show($id)
    {
        $equipo = Equipo::findOrFail($id);

        return view('admin.equipos.show', compact('equipo'));
    }

    /*
       * OCULTAR / MOSTRAR EQUIPO
       *
       * Esto no borra el equipo.
       * Solo cambia el booleano "oculto".
       *
       * Si oculto = true:
       * el equipo deja de aparecer en la web pública
       *
       * Si oculto = false:
       * vuelve a ser visible
       */
    public function toggleOculto(Equipo $equipo)
    {
        // "!$equipo->oculto" invierte el valor actual.
        $equipo->oculto = !$equipo->oculto;
        $equipo->save();

        return redirect()->route('equipos.index')->with(
            'mensaje',
            $equipo->oculto
                ? 'Equipo ocultado de la web publica.'
                : 'Equipo visible de nuevo en la web publica.'
        );
    }

    /*
       * BORRAR ARCHIVO DEL DISCO
       *
       * Este helper se usa para no repetir lógica.
       * Su función es eliminar una imagen del almacenamiento si realmente es una imagen subida.
       *
       * No borra imágenes por defecto como:
       * - club_14.png
       * - basket.jpeg
       *
       * porque esas parecen ser imágenes "base" del proyecto.
       */
    private function deleteStoredImage(?string $filename, string $directory): void
    {
        // Si no hay nombre de archivo o es una imagen por defecto, no hacemos nada.
        if (!$filename || $filename === 'club_14.png' || $filename === 'basket.jpeg') {
            return;
        }

        // Borra el archivo de storage/app/public/{directory}/...
        Storage::disk('public')->delete($directory . '/' . ltrim($filename, '/'));
    }

    /*
       * OPCIONES DE CATEGORÍA
       *
       * Este helper construye una lista de categorías predefinidas para el formulario.
       *
       * Combina:
       * - base principal: Senior
       * - bases con letras: Senior, Junior, Cadete, Infantil, Alevín
       * - sufijos: A, B, C, D
       *
       * Ejemplo de resultado:
       * - Senior
       * - Senior B
       * - Junior A
       * - Cadete C
       */
    private function categoryOptions(): array
    {
        $bases = ['Senior', 'Junior', 'Cadete', 'Infantil', 'Alevín'];
        $suffixes = ['A', 'B', 'C', 'D'];
        $options = ['Senior'];

        // Doble bucle para generar todas las combinaciones posibles.
        foreach ($bases as $base) {
            foreach ($suffixes as $suffix) {
                if ($base === 'Senior' && $suffix === 'A') {
                    continue;
                }

                $options[] = $base . ' ' . $suffix;
            }
        }

        return $options;
    }
}
