<?php

namespace App\Http\Controllers;

// Request permite leer lo que llega desde formularios o desde la URL.
use Illuminate\Http\Request;

// Modelo principal de este controlador.
// Representa los productos de merchandising guardados en la base de datos.
use App\Models\Merchandising;

// Storage se usa para borrar imágenes del disco cuando se sustituyen o eliminan.
use Illuminate\Support\Facades\Storage;

class MerchandisingController extends Controller
{
    /*
         * LISTADO DE PRODUCTOS
         *
         * Este método se usa en el panel de administración para mostrar
         * todos los productos de merchandising.
         * Además incluye buscador y paginación.
         *
         * Flujo:
         * 1. leer lo que el usuario escribe en el buscador
         * 2. construir la consulta
         * 3. si hay texto, filtrar por nombre o descripción
         * 4. ordenar alfabéticamente
         * 5. paginar resultados
         * 6. enviar todo a la vista
         */
    public function index(Request $request)
    {
        // Recogemos el texto del buscador desde la URL.
        // Ejemplo: /admin/merchandising?search=camiseta
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'nombre');
        $direction = strtolower((string) $request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Restringimos las columnas ordenables del listado.
        $sortableColumns = [
            'nombre' => 'nombre',
            'talla' => 'talla',
            'precio' => 'precio',
            'oculto' => 'oculto',
        ];

        $sortColumn = $sortableColumns[$sort] ?? 'nombre';

        // Construimos la consulta de productos.
        // when(...) solo aplica el filtro si $search tiene contenido.
        $merchandising = Merchandising::when($search, function ($query) use ($search) {
            // Si el usuario busca algo, filtramos por nombre o descripción.
            $query->where('nombre', 'like', '%' . $search . '%')
                ->orWhere('descripcion', 'like', '%' . $search . '%');
        });

        // En texto usamos lower(...) para no depender de mayúsculas.
        if (in_array($sortColumn, ['nombre', 'talla'], true)) {
            $merchandising->orderByRaw('LOWER(' . $sortColumn . ') ' . $direction);
        } else {
            $merchandising->orderBy($sortColumn, $direction);
        }

        $merchandising = $merchandising
            ->orderByRaw('LOWER(nombre) asc') // orden alfabético por nombre
            ->paginate(9) // 9 productos por página
            ->withQueryString(); // mantiene ?search=... al cambiar de página

        // Enviamos resultados y texto buscado a la vista.
        return view('admin.merchandising.index', compact('merchandising', 'search', 'sort', 'direction'));
    }

    /*
         * FORMULARIO DE CREACIÓN
         *
         * Este método no guarda nada.
         * Solo devuelve la vista para crear un producto nuevo.
         */
    public function create()
    {
        return view('admin.merchandising.create');
    }

    /*
         * GUARDAR PRODUCTO NUEVO
         *
         * Este método procesa el formulario de creación.
         *
         * Qué hace:
         * 1. valida los campos
         * 2. crea un producto vacío
         * 3. asigna datos básicos
         * 4. convierte las tallas de array a texto
         * 5. guarda la imagen si se ha subido
         * 6. guarda el registro en base de datos
         */
    public function store(Request $request)
    {
        // Validación del formulario.
        // Si algo falla, Laravel vuelve automáticamente atrás con errores.
        $request->validate([
            'nombre'         => 'required|string|max:255',
            'precio'         => 'required|numeric|min:0',
            'talla'          => 'required|array|min:1',
            'imagen_merchan' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'descripcion'    => 'nullable|string',
            'oculto'         => 'nullable|boolean',
        ], [
            'nombre.required' => 'Tienes que poner un nombre al producto.',
            'precio.required' => 'El precio es obligatorio.',
            'talla.required'  => 'Selecciona al menos una talla disponible.',
            'imagen_merchan.required' => 'La foto es obligatoria para la tienda.',
            'imagen_merchan.image' => 'El archivo debe ser una imagen.',
            'imagen_merchan.mimes' => 'La foto debe ser JPG o PNG.',
            'imagen_merchan.max' => 'La foto es demasiado pesada (máximo 2MB).',
        ]);

        // Creamos una instancia vacía del modelo.
        $item = new Merchandising();

        // Asignamos los datos básicos que llegan del formulario.
        $item->nombre = $request->nombre;
        $item->descripcion = $request->descripcion;
        $item->precio = $request->precio;

        // boolean('oculto') convierte el valor a true o false.
        $item->oculto = $request->boolean('oculto');

        // Las tallas llegan como array desde checkboxes.
        // Ejemplo: ['S', 'M', 'L']
        // Para guardarlas en BD se convierten a texto: "S,M,L"
        if ($request->has('talla') && is_array($request->talla)) {
            $item->talla = implode(',', $request->talla);
        } else {
            $item->talla = null;
        }

        // Si el usuario subió una imagen del producto...
        if ($request->hasFile('imagen_merchan')) {
            $imagen = $request->file('imagen_merchan');

            // Generamos un nombre único con time() para evitar choques.
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();

            // Guardamos la imagen en storage/app/public/merchandising
            $imagen->storeAs('merchandising', $nombreImagen, 'public');

            // En la BD solo guardamos el nombre del archivo.
            $item->imagen_merchan = $nombreImagen;
        }

        // Guardamos el producto en la base de datos.
        $item->save();

        return redirect()->route('merchandising.index')->with('mensaje', 'Producto creado
  con éxito');
    }

    /*
         * FORMULARIO DE EDICIÓN
         *
         * Este método busca un producto existente y abre la vista de edición.
         * Además transforma las tallas guardadas en BD para que los checkboxes
         * del formulario puedan marcarse correctamente.
         */
    public function edit($id)
    {
        // Buscamos el producto o lanzamos 404 si no existe.
        $item = Merchandising::findOrFail($id);

        // En BD las tallas están como texto: "S,M,L"
        // Aquí las convertimos a array para la vista: ['S', 'M', 'L']
        $tallasSeleccionadas = $item->talla ? explode(',', $item->talla) : [];

        return view('admin.merchandising.edit', compact('item', 'tallasSeleccionadas'));
    }

    /*
         * ACTUALIZAR PRODUCTO
         *
         * Este método guarda cambios sobre un producto ya existente.
         *
         * Qué hace:
         * 1. valida los datos
         * 2. busca el producto actual
         * 3. actualiza campos básicos
         * 4. vuelve a convertir las tallas a texto
         * 5. si llega una nueva imagen, borra la antigua y guarda la nueva
         * 6. guarda los cambios en base de datos
         */
    public function update(Request $request, $id)
    {
        // Validamos los datos recibidos.
        $request->validate([
            'nombre'         => 'required|string|max:255',
            'precio'         => 'required|numeric|min:0',
            'talla'          => 'required|array|min:1',
            'imagen_merchan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'descripcion'    => 'nullable|string',
            'oculto'         => 'nullable|boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'precio.required' => 'El precio es obligatorio.',
            'talla.required'  => 'Selecciona al menos una talla.',
        ]);

        // Buscamos el registro actual.
        $item = Merchandising::findOrFail($id);

        // Actualizamos los datos básicos.
        $item->nombre = $request->nombre;
        $item->descripcion = $request->descripcion;
        $item->precio = $request->precio;
        $item->oculto = $request->boolean('oculto');

        // Convertimos el array de tallas en texto para guardarlo en BD.
        if ($request->has('talla') && is_array($request->talla)) {
            $item->talla = implode(',', $request->talla);
        }

        // Si el usuario sube una nueva imagen...
        if ($request->hasFile('imagen_merchan')) {
            // Primero borramos la imagen anterior para no dejar archivos sobrantes.
            $this->deleteStoredImage($item->imagen_merchan, 'merchandising');

            // Después guardamos la nueva.
            $imagen = $request->file('imagen_merchan');
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();
            $imagen->storeAs('merchandising', $nombreImagen, 'public');

            // Actualizamos la referencia en la base de datos.
            $item->imagen_merchan = $nombreImagen;
        }

        // Guardamos cambios.
        $item->save();

        return redirect()->route('merchandising.index')->with('mensaje', 'Producto
  actualizado correctamente');
    }

    /*
         * ELIMINAR PRODUCTO
         *
         * Este método borra:
         * 1. la imagen física del producto, si existe
         * 2. el registro en base de datos
         */
    public function destroy($id)
    {
        // Busca el producto o lanza 404 si no existe.
        $item = Merchandising::findOrFail($id);

        // Si tenía imagen propia, la borramos del almacenamiento.
        $this->deleteStoredImage($item->imagen_merchan, 'merchandising');

        // Después eliminamos el registro de la base de datos.
        $item->delete();

        return redirect()->route('merchandising.index')->with('mensaje', 'Producto
  eliminado correctamente');
    }

    /*
         * VER DETALLE DE UN PRODUCTO
         *
         * Carga la ficha de un producto concreto.
         * Además transforma las tallas guardadas en texto a un array
         * para poder mostrarlas mejor en la vista.
         */
    public function show($id)
    {
        // Busca el producto o lanza 404 si no existe.
        $item = Merchandising::findOrFail($id);

        // Convierte el texto "S,M,L" en array ['S', 'M', 'L'].
        // Si no hay tallas guardadas, devolvemos un array vacío.
        $tallas = $item->talla ? explode(',', $item->talla) : [];

        return view('admin.merchandising.show', compact('item', 'tallas'));
    }

    /*
         * OCULTAR / MOSTRAR PRODUCTO
         *
         * No borra el producto.
         * Solo cambia el booleano "oculto".
         *
         * Si oculto = true:
         * el producto deja de aparecer en la web pública
         *
         * Si oculto = false:
         * vuelve a ser visible
         */
    public function toggleOculto(Merchandising $merchandising)
    {
        // Invertimos el valor actual del campo oculto.
        $merchandising->oculto = !$merchandising->oculto;
        $merchandising->save();

        return redirect()->route('merchandising.index')->with(
            'mensaje',
            $merchandising->oculto
                ? 'Producto ocultado de la web publica.'
                : 'Producto visible de nuevo en la web publica.'
        );
    }

    /*
         * BORRAR ARCHIVO DEL DISCO
         *
         * Este helper evita repetir lógica.
         * Borra una imagen subida por el usuario, pero no elimina
         * imágenes por defecto del proyecto.
         *
         * No borra imágenes base como:
         * - club_14.png
         * - basket.jpeg
         */
    private function deleteStoredImage(?string $filename, string $directory): void
    {
        // Si no hay archivo o es una imagen por defecto, no hacemos nada.
        if (!$filename || $filename === 'club_14.png' || $filename === 'basket.jpeg') {
            return;
        }

        // Borra el archivo de storage/app/public/{directory}/...
        Storage::disk('public')->delete($directory . '/' . ltrim($filename, '/'));
    }
}
