<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Models\AuditLog;

/*
Para generar el controlador con todos los métodos para el crud, hay que hacerlo con el siguiente comando: php artisan make:controller Admin\RoomController --model=Room
En este caso es Admin\RoomController porque se ha querido crear dentro de una carpeta admin. El --model es para especificar de que modelo va a ser el controlador.
*/


class RoomController extends Controller
{
    /**
     * Método para crear un nuevo log
     * @param mixed $room Sala sobre la que se ha realizado la acción
     * @param mixed $action Acción que se ha realizado
     * @return void
     */
    private function logAction($room, $action)
    {
        AuditLog::create([
            'auditable_type' => get_class($room), //Clase room
            'auditable_id'   => $room->id, //Id de la habitación sobre la que se ha realizado la acción
            'user_id'        => auth()->id(), //Id del usuario que ha realizado la acción
            'action'         => $action, //Acción que se ha realizado
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rooms = Room::all(); //Se obtienen todas las salas
        return view('admin.rooms.index', compact('rooms')); //Se devuelve la vista index del administrador con todas las salas.
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.rooms.create'); //Redirige a la vista de creación de salas.
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Se valida que el item cunpla con toda slas validaciones
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'active' => 'required',
            'description' => 'nullable|string',
        ],[
            //Mensajes de error personalizados para cada error en la validación
            'name.required' => 'El nombre de la sala es obligatorio',
            'name.max'=> 'El nombre no puede tener más de 255 carácteres',

            'location.required' => 'La ubicación de la sala es obligatoria',
            'location.max' => 'La ubicación no puede tener más de 255 carácteres.',

            'capacity.required'=> 'La capacidad de la sala es obligatoria',
            'capacity.min'=> 'La capacidad mínima de la sala debe ser de 1',

            'active.required'=> 'Debes indicar si la sala esta activa',

            'description.string'=> 'La descripción debe ser texto',
        ]);
        $room = Room::create($data); //Se crea la sala
        $this->logAction($room, 'creado'); //Se crea un log indicando que se ha creado la sala.


        //Se valida el comentario
        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ],[
            'comment.string'=> 'El comentario debe ser un texto',
            'comment.max'=> 'El comentario puede tener como máximo 1000 caracteres',
        ]);

        //En caso de que se haya hecho un comentario se crea un nuevo registro en la tabla comments.
        if ($request->filled('comment')) {
            $room->comments()->create([
                'description' => $request->comment,
                'user_id' => auth()->id(),
            ]);
            $this->logAction($room, 'comentario añadido: ' . substr($request->comment, 0, 200)); //Se crea un log indicando que se ha añadido un comentario
        }

        //Archivos adjuntos
        if ($request->hasFile('attachment')) {

            //Por cada fichero adjunto que se haya subido
            foreach ($request->file('attachment') as $file) {
                //Se obtiene la ruta del fichero.
                $path = $file->store('attachments', 'public');
                //Se crea un fichero adjunto a la habitación
                $room->attachments()->create([
                    'path' => $path, //Ruta del fichero
                    'original_name' => $file->getClientOriginalName(), //Nombre original del fichero
                    'mime' => $file->getMimeType(), //Tipo de mime
                    'size' => $file->getSize(), //Tamaño del fichero
                    'user_id' => auth()->id(), //Id del usuario que ha subido el fichero
                ]);
                $this->logAction($room, 'archivo adjuntado: ' . $file->getClientOriginalName()); //Se crea un log indicando que se ha adjuntado un fichero.
            }
        }

        //Se muestra una alerta indicando que la sala se ha creado de manera correcta.
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Sala creada correctamente',
            'text' => 'La sala se ha creado correctamente'
        ]);
        return redirect()->route('admin.rooms.index'); //Redirige a la vista index de las salas.
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        $room->load('comments.user' , 'attachments.user'); //Evitar posibles futuros problemas de N+1. Carga los comentario y los ficheros adjuntos del usuario
        return view('admin.rooms.edit', compact('room')); //Redirige a la vista de edición de la sala.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        //Se valida la sala para que no haya ningún error
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'active' => 'required',
            'description' => 'nullable|string',
        ],
        [
            //Mensaje personalizado por cada error que se pueda dar en la validación
            'name.required' => 'El nombre de la sala es obligatorio',
            'name.max'=> 'El nombre no puede tener más de 255 carácteres',

            'location.required' => 'La ubicación de la sala es obligatoria',
            'location.max' => 'La ubicación no puede tener más de 255 carácteres.',

            'capacity.required'=> 'La capacidad de la sala es obligatoria',
            'capacity.min'=> 'La capacidad mínima de la sala debe ser de 1',

            'active.required'=> 'Debes indicar si la sala esta activa',

            'description.string'=> 'La descripción debe ser texto',
        ]);
        $room->update($data); //Se actualiza la sala
        $this->logAction($room, 'editado');
        // Si cambia el estado activo o state crea un log indicando que el estado activo ha cambiado.
        if ($room->wasChanged('active')) {
            $this->logAction($room, 'cambio estado activo a ' . $room->active);
        }

        //Se valida el comentario para que sea string con un máximo de 1000 caracteres
        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ],
        [
            //Mensajes de error personalizados por si ocurre algún fallo en la validación
            'comment.string'=> 'El comentario debe ser un texto',
            'comment.max'=> 'El comentario debe tener como máximo 1000 caracteres',
        ]);

        //Si se ha realizado un comentario se crea.
        if ($request->filled('comment')) {
            $room->comments()->create([
                'description' => $request->comment,
                'user_id' => auth()->id(),
            ]);
            $this->logAction($room, 'comentario añadido: ' . substr($request->comment, 0, 200)); //Log que demuestra que se ha creado un comentario
        }
        //Ficheros adjuntos
        if ($request->hasFile('attachment')) {

            //Por cada fichero que se haya subido
            foreach ($request->file('attachment') as $file) {

                $path = $file->store('attachments', 'public'); //Se obtiene la ruta

                //Se crea un fichero adjunto relacionado directamente con la sala.
                $room->attachments()->create([
                    'path' => $path, //Ruta del fichero
                    'original_name' => $file->getClientOriginalName(), //Nombre original del fichero
                    'mime' => $file->getMimeType(), //Tipo de mime
                    'size' => $file->getSize(), //Tamaño del fichero
                    'user_id' => auth()->id(), //Id del usuario que ha subido el fichero
                ]);
                $this->logAction($room, 'archivo adjuntado: ' . $file->getClientOriginalName()); //Se crea un log indicando que se ha subido un fichero.
            }
        }

        //En caso de que todo haya salido bien, muestra una alerta indicando que la sala se ha actualizado correctamente.
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Sala actualizada correctamente',
            'text' => 'La sala se ha actualizado correctamente'
        ]);
        return redirect()->route('admin.rooms.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        $this->logAction($room, 'eliminado'); //Log indicando que se ha eliminado la sala.
        $room->delete(); //Se elimina la sala
        //Alerta indicando que la eliminación de la sala se ha realizado de manera exitosa
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Sala eliminada correctamente',
            'text' => 'La sala se ha eliminado correctamente'
        ]);

        return redirect()->route('admin.rooms.index');
    }
}
