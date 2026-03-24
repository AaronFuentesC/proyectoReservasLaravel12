<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\AuditLog;

/*
Para generar el controlador con todos los métodos para el crud, hay que hacerlo con el siguiente comando: php artisan make:controller Admin\ItemController --model=Room
En este caso es Admin\ItemController porque se ha querido crear dentro de una carpeta admin. El --model es para especificar de que modelo va a ser el controlador.
*/



/**
 * Controlador para Item
 */
class ItemController extends Controller
{
    /**
     * Método para crear un log
     * @param mixed $item Item sobre el que se ha realizado la acción
     * @param mixed $action Acción que se ha realizado
     * @return void
     */
    private function logAction($item, $action)
    {
        AuditLog::create([
            'auditable_id' => $item->id, //id del item sobre el que se ha realizado la acción
            'auditable_type' => get_class($item), //Clase item
            'user_id' => auth()->id(), //Id del usuario que ha realizado la acción
            'action' => $action, //Acción que se ha realizado sobre el item
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::all(); //Se obtienen todos los items.
        return view('admin.items.index', compact('items')); //Se redirige a la pantalla de index con todos los items.
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $items = Item::all(); //Se obtienen todos los items
        return view('admin.items.create', compact('items')); //Se redirige a la pantalla de creación con todos los items.
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Validaciones del objeto
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required',
            'serial_number' => 'nullable|string',
            'state' => 'required',
            'quantity'=> 'nullable|integer|min:1',
            'active' => 'required',
        ],
        [
            //Mensajes personalizados para cada error que se pueda dar en las validaciones
            'name.required'=> 'El nombre del equipo es obligatorio',
            'name.max'=> 'El nombre puede tener como máximo 255 caracteres',

            'type.required'=> 'El tipo del equipo es obligatorio',

            'serial_number.string'=> 'El número serial debe ser un texto',

            'state.required' => 'El estado del objeto es obligatorio',

            'quantity.min'=> 'La cantidad del objeto debe ser 0 como mínimo.',

            'active.required'=> 'Debes indicar si el equipo está activo o no.',
        ]);

        //Se crea el objeto
        $item = Item::create($data);
        $this->logAction($item, 'creado'); //Se crea el log para indicar que se ha creado un objeto.

        //Se valida el comentario
        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ],
        [
            'comment.string'=> 'El comentario debe ser un texto',
            'comment.max'=> 'El commentario puede tener como máximo 1000 caracteres.',
        ]);

        //En caso de que se haya hecho un comentario se crea un nuevo registro en la tabla comments.
        if ($request->filled('comment')) {

            $item->comments()->create([
                'description' => $request->comment,
                'user_id' => auth()->id(),
            ]);
            $this->logAction($item, 'comentario añadido: ' . substr($request->comment, 0, 100)); //Se crea un log indicando que se ha añadido un comentario.
        }
        //Ficheros adjuntos
        if ($request->hasFile('attachment')) {

            //Por cada fichero adjunto a la creación del item
            foreach ($request->file('attachment') as $file) {
                //Se obtiene la ruta del fichero
                $path = $file->store('attachments', 'public');
                //Se crea un nuevo fichero adjunto relacionado con el item
                $item->attachments()->create([
                    'path' => $path, //Ruta del archivo
                    'original_name' => $file->getClientOriginalName(), //Nombre original del fichero
                    'mime' => $file->getMimeType(), //Tipo del mime
                    'size' => $file->getSize(), //Tamaño del fichero
                    'user_id' => auth()->id(), //Id del usuario que ha subido el archivo
                ]);
                $this->logAction($item, 'archivo adjuntado: ' . $file->getClientOriginalName()); //Se crea un nuevo log indicando que ha adjuntado un fichero.
            }
        }
        
        //Se muestra una alerta indicando que se ha creado el equipo de forma correcta.
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Equipo creado correctamente',
            'text' => 'El equipo se ha creado correctamente'
        ]);

        return redirect()->route('admin.items.index'); //Se redirige al index del administrador
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        $item->load('comments.user', 'attachments.user'); //Se cargan los comentarios y los ficheros adjuntos del equipo.
        return view('admin.items.edit', compact('item')); //Te redirige a la ruta de edición del objeto.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        //Se valida el equipo
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required',
            'serial_number' => 'nullable|string',
            'state' => 'required',
            'quantity' => 'nullable|integer|min:0',
            'active' => 'required',
        ],
        [
            //Mensajes de error personalizados para cada posible error que se de en la validación
            'name.required'=> 'El nombre del equipo es obligatorio',
            'name.max'=> 'El nombre puede tener como máximo 255 caracteres',

            'serial_number.string'=> 'El número serial debe ser un texto',

            'state.required'=> 'El estado del equipo es obligatorio',

            'quantity.min'=> 'La cantidad mínima del equipo debe ser de 0',

            'active.required'=> 'Debes indicar si el equipo está activo o no',
        ]);
        $oldState = $item->state->value; // valor antiguo antes de update
        $item->update($data); //Se actualiza el objeto con los nuevos datos.
        $this->logAction($item, 'editado'); //Se crea un log indicando que se ha editado el objeto

        // si cambia el estado activo o state
        if ($item->wasChanged('active')) {
            $this->logAction($item, 'cambio estado activo a ' . $item->active);
        }
        if($oldState !== $item->state->value){
            $this->logAction($item, 'estado cambiado de '.$oldState.' a '.$item->state->value);
        }

        //Se valida que el comentario sea string y tenga como máximo 1000 caracteres.
        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ],
        [
            //Mensajes de error en caso de que haya algún error en la validación
            'comment.string'=> 'El comentario debe ser un texto',
            'comment.max'=> 'El comentario debe tener como máximo 1000 caracteres.',
        ]);

        //En caso de que se haya realizado un comentario
        if ($request->filled('comment')) {
            //Se crea un nuevo comentario relacionado con el item
            $item->comments()->create([
                'description' => $request->comment,
                'user_id' => auth()->id(),
            ]);
            $this->logAction($item, 'comentario añadido: ' . substr($request->comment, 0, 200)); //Log indicando que se ha añadido un comentario
        }
        //Ficheros adjuntos
        if ($request->hasFile('attachment')) {

            //Por cada fichero adjunto
            foreach ($request->file('attachment') as $file) {
                //Se obtiene la ruta del fichero
                $path = $file->store('attachments', 'public');
                //Se crea un nuevo fichero ajunto relacionado con el item
                $item->attachments()->create([
                    'path' => $path, //Ruta del fichero
                    'original_name' => $file->getClientOriginalName(), //Nombre original del fichero
                    'mime' => $file->getMimeType(), //Tipo de mime
                    'size' => $file->getSize(), //Tamaño del fichero
                    'user_id' => auth()->id(), //Id del usuario
                ]);
                $this->logAction($item, 'archivo adjuntado: ' . $file->getClientOriginalName()); //Se crea un log indicando que se ha adjuntado un fichero.
            }
        }

        //Se muestra la alerta indicando que el equipo se ha actualizado correctamente.
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Equipo actualizado correctamente',
            'text' => 'El equipo se ha actualizado correctamente'
        ]);

        return redirect()->route('admin.items.index'); //Te redirige al index del administrador.
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $this->logAction($item, 'eliminado'); //Crea un log indicando que se ha eliminado el equipo.
        $item->delete(); //Se elimina el equipo
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Equipo eliminado correctamente',
            'text' => 'El equipo se ha eliminado correctamente'
        ]);

        return redirect()->route('admin.items.index'); //Te redirige a la ruta index del administrador.
    }
}
