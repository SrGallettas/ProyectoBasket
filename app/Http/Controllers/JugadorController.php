<?php

namespace App\Http\Controllers;

// Modelo principal de este controlador.
// Representa a los jugadores guardados en la base de datos.
use App\Models\Jugador;

// También se importa Equipo porque al crear o editar un jugador
// hay que asignarlo a un equipo.
use App\Models\Equipo;

use Illuminate\Http\Request;

// Storage se usa para borrar imágenes del disco.
use Illuminate\Support\Facades\Storage;

// Rule se usa para hacer validaciones más específicas,
// por ejemplo comprobar que un equipo exista y además sea local.
use Illuminate\Validation\Rule;

class JugadorController extends Controller
{
    /*
       * LISTADO DE JUGADORES
       *
       * Este método se usa en el panel admin para mostrar todos los jugadores.
       * También permite buscar por nombre, apellido o dorsal.
       *
       * Flujo:
       * 1. leer el texto del buscador
       * 2. construir la consulta
       * 3. si hay búsqueda, filtrar resultados
       * 4. cargar también el equipo de cada jugador
       * 5. ordenar y paginar
       * 6. enviar todo a la vista
       */
    public function index(Request $request)
    {
        // Recogemos lo que el usuario ha escrito en el buscador.
        // Ejemplo: /admin/jugadores?search=Juan
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'nombre');
        $direction = strtolower((string) $request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Igual que en otros listados del admin, limitamos las columnas ordenables.
        $sortableColumns = [
            'dorsal' => 'jugadores.dorsal',
            'nombre' => 'jugadores.nombre',
            'apellido' => 'jugadores.apellido',
            'club' => 'equipos.nombre',
            'categoria' => 'equipos.categoria',
            'posicion' => 'jugadores.posicion',
            'fecha_nacimiento' => 'jugadores.fecha_nacimiento',
            'oculto' => 'jugadores.oculto',
        ];

        $sortColumn = $sortableColumns[$sort] ?? 'nombre';

        // Construimos la consulta de jugadores.
        // with('equipo') hace eager loading:
        // trae también el equipo relacionado para no hacer una consulta extra por cada jugador.
        $jugadores = Jugador::with('equipo')
            ->leftJoin('equipos', 'equipos.id', '=', 'jugadores.equipo_id')
            ->select('jugadores.*')
            ->when($search, function ($query) use ($search) {
                // Si existe texto de búsqueda, filtramos por varios campos.
                // Se busca:
                // - nombre
                // - apellido
                // - dorsal
                $query->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('apellido', 'like', '%' . $search . '%')
                    ->orWhere('dorsal', 'like', '%' . $search . '%');
            });

        // En columnas de texto ignoramos mayúsculas para ordenar como humano.
        if (in_array($sortColumn, ['jugadores.nombre', 'jugadores.apellido', 'jugadores.posicion', 'equipos.nombre', 'equipos.categoria'], true)) {
            $jugadores->orderByRaw('LOWER(' . $sortColumn . ') ' . $direction);
        } else {
            $jugadores->orderBy($sortColumn, $direction);
        }

        $jugadores = $jugadores
            ->orderByRaw('LOWER(jugadores.nombre) asc')
            ->paginate(10) // 10 jugadores por página
            ->withQueryString(); // mantiene la búsqueda al cambiar de página

        // Enviamos resultados y texto buscado a la vista.
        return view('admin.jugadores.index', compact('jugadores', 'search', 'sort', 'direction'));
    }

    /*
       * FORMULARIO DE CREACIÓN
       *
       * Este método no guarda nada.
       * Solo prepara el formulario para crear un jugador.
       *
       * Importante:
       * solo carga equipos locales, porque un jugador del club
       * debe asignarse a un equipo local del Bellreguard.
       */
    public function create()
    {
        $equipos = Equipo::where('es_local', true)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        return view('admin.jugadores.create', compact('equipos'));
    }

    /*
       * GUARDAR JUGADOR NUEVO
       *
       * Este método procesa el formulario de creación.
       *
       * Qué hace:
       * 1. valida campos
       * 2. comprueba que el equipo asignado exista y sea local
       * 3. crea el jugador
       * 4. guarda imagen si se ha subido
       * 5. guarda el registro en la base de datos
       */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'           => 'required|string|max:255',
            'apellido'         => 'required|string|max:255',
            'dorsal'           => 'required|integer|min:0|max:99',
            'posicion'         => 'required|string',
            'fecha_nacimiento' => 'required|date',
            'equipo_id'        => [
                'required',

                // Esta validación no solo comprueba que exista el id.
                // También obliga a que ese id pertenezca a un equipo local.
                Rule::exists('equipos', 'id')->where(fn($query) => $query->where('es_local', true)),
            ],
            'imagen_jugador'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'oculto'           => 'nullable|boolean',
        ], [
            'nombre.required'      => 'El nombre es obligatorio.',
            'apellido.required'    => 'El apellido es obligatorio.',
            'dorsal.required'      => 'El dorsal es obligatorio.',
            'dorsal.integer'       => 'El dorsal debe ser un número.',
            'equipo_id.required'   => 'Debes asignar al jugador a un equipo.',
            'equipo_id.exists'     => 'El equipo seleccionado no es válido.',
            'imagen_jugador.image' => 'El archivo debe ser una imagen.',
            'imagen_jugador.mimes' => 'La foto debe ser JPG o PNG.',
            'imagen_jugador.max'   => 'La foto es demasiado pesada (máximo 2MB).',
        ]);

        // Creamos el objeto jugador.
        $jugador = new Jugador();

        // Asignamos los valores básicos desde el formulario.
        $jugador->nombre = $request->nombre;
        $jugador->apellido = $request->apellido;
        $jugador->dorsal = $request->dorsal;
        $jugador->posicion = $request->posicion;
        $jugador->fecha_nacimiento = $request->fecha_nacimiento;
        $jugador->equipo_id = $request->equipo_id;

        // Campo booleano para controlar si aparece o no en la web pública.
        $jugador->oculto = $request->boolean('oculto');

        // Si el usuario subió imagen del jugador...
        if ($request->hasFile('imagen_jugador')) {
            $imagen = $request->file('imagen_jugador');

            // Se genera un nombre con time() para evitar choques de nombres.
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();

            // Se guarda en storage/app/public/jugadores
            $imagen->storeAs('jugadores', $nombreImagen, 'public');

            // En la BD se guarda solo el nombre del archivo.
            $jugador->imagen_jugador = $nombreImagen;
        }

        // Guardamos el jugador en la base de datos.
        $jugador->save();

        return redirect()->route('jugadores.index')->with('mensaje', 'Jugador creado con éxito');
    }

    /*
       * FORMULARIO DE EDICIÓN
       *
       * Busca un jugador ya existente y carga la vista de edición.
       * También carga la lista de equipos locales posibles.
       */
    public function edit($id)
    {
        // Busca el jugador o lanza 404 si no existe.
        $jugador = Jugador::findOrFail($id);

        // Cargamos equipos locales para el selector del formulario.
        $equipos = Equipo::where('es_local', true)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        return view('admin.jugadores.edit', compact('jugador', 'equipos'));
    }

    /*
       * ACTUALIZAR JUGADOR
       *
       * Este método guarda cambios sobre un jugador ya existente.
       *
       * Qué hace:
       * 1. valida datos
       * 2. busca el jugador
       * 3. actualiza campos básicos
       * 4. si llega una imagen nueva, borra la vieja y guarda la nueva
       * 5. guarda el registro actualizado
       */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'           => 'required|string|max:255',
            'apellido'         => 'required|string|max:255',
            'dorsal'           => 'required|integer|min:0|max:99',
            'posicion'         => 'required|string',
            'fecha_nacimiento' => 'required|date',
            'equipo_id'        => [
                'required',
                Rule::exists('equipos', 'id')->where(fn($query) => $query->where('es_local', true)),
            ],

            // En update aquí admiten archivos más grandes: hasta 10MB.
            'imagen_jugador'   => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'oculto'           => 'nullable|boolean',
        ], [
            'nombre.required'      => 'El nombre es obligatorio.',
            'apellido.required'    => 'El apellido es obligatorio.',
            'dorsal.required'      => 'El dorsal es obligatorio.',
            'dorsal.integer'       => 'El dorsal debe ser un número.',
            'equipo_id.required'   => 'Debes asignar al jugador a un equipo.',
            'equipo_id.exists'     => 'El equipo seleccionado no es válido.',
            'imagen_jugador.image' => 'El archivo debe ser una imagen.',
            'imagen_jugador.mimes' => 'La foto debe ser JPG o PNG.',
            'imagen_jugador.max'   => 'La foto es demasiado pesada (máximo 10MB).',
        ]);

        // Buscamos el jugador actual.
        $jugador = Jugador::findOrFail($id);

        // Actualizamos datos básicos.
        $jugador->nombre = $request->nombre;
        $jugador->apellido = $request->apellido;
        $jugador->dorsal = $request->dorsal;
        $jugador->posicion = $request->posicion;
        $jugador->fecha_nacimiento = $request->fecha_nacimiento;
        $jugador->equipo_id = $request->equipo_id;
        $jugador->oculto = $request->boolean('oculto');

        // Si el usuario sube una nueva foto...
        if ($request->hasFile('imagen_jugador')) {
            // Primero se borra la foto anterior para no dejar basura en el servidor.
            $this->deleteStoredImage($jugador->imagen_jugador, 'jugadores');

            // Luego se guarda la nueva.
            $imagen = $request->file('imagen_jugador');
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();
            $imagen->storeAs('jugadores', $nombreImagen, 'public');

            // Se actualiza la referencia en la base de datos.
            $jugador->imagen_jugador = $nombreImagen;
        }

        $jugador->save();

        return redirect()->route('jugadores.index')->with('mensaje', 'Jugador actualizado correctamente');
    }

    /*
       * ELIMINAR JUGADOR
       *
       * Este método borra:
       * 1. la imagen física del jugador, si existe
       * 2. el registro en base de datos
       */
    public function destroy($id)
    {
        // Busca el jugador o lanza error 404 si no existe.
        $jugador = Jugador::findOrFail($id);

        // Si tenía imagen propia, la borramos del almacenamiento.
        $this->deleteStoredImage($jugador->imagen_jugador, 'jugadores');

        // Después eliminamos el registro de la base de datos.
        $jugador->delete();

        return redirect()->route('jugadores.index')->with('mensaje', 'Jugador eliminado correctamente del club');
    }

    /*
       * VER DETALLE DE UN JUGADOR
       *
       * Carga la ficha de un jugador concreto.
       * Además trae también su equipo relacionado.
       */
    public function show($id)
    {
        // with('equipo') sirve para traer también el equipo al que pertenece.
        $jugador = Jugador::with('equipo')->findOrFail($id);

        return view('admin.jugadores.show', compact('jugador'));
    }

    /*
       * OCULTAR / MOSTRAR JUGADOR
       *
       * No borra al jugador.
       * Solo cambia el booleano "oculto".
       *
       * Si oculto = true, deja de salir en la web pública.
       * Si oculto = false, vuelve a aparecer.
       */
    public function toggleOculto(Jugador $jugador)
    {
        // Invierte el valor actual.
        $jugador->oculto = !$jugador->oculto;
        $jugador->save();

        return redirect()->route('jugadores.index')->with(
            'mensaje',
            $jugador->oculto
                ? 'Jugador ocultado de la web publica.'
                : 'Jugador visible de nuevo en la web publica.'
        );
    }

    /*
       * HELPER PARA BORRAR IMÁGENES
       *
       * Este método privado evita repetir lógica.
       * Borra una imagen real subida por el usuario, pero no borra
       * imágenes por defecto del proyecto.
       */
    private function deleteStoredImage(?string $filename, string $directory): void
    {
        // Si no hay nombre de archivo o es una imagen genérica, no hacemos nada.
        if (!$filename || $filename === 'club_14.png' || $filename === 'basket.jpeg') {
            return;
        }

        // basename() asegura que solo se use el nombre del archivo.
        // Luego se borra de storage/app/public/{directory}/...
        Storage::disk('public')->delete($directory . '/' . ltrim(basename($filename), '/'));
    }
}
