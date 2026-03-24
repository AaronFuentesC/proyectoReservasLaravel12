<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\BookingItem;
use App\Models\Room;
use App\Models\Item;
use Illuminate\Validation\Rules\Enum;
use App\BookingState;
use App\Models\AuditLog;
use Exception;


/*
Para generar el controlador con todos los métodos para el crud, hay que hacerlo con el siguiente comando: php artisan make:controller Admin\BookingController --model=Room
En este caso es Admin\BookingController porque se ha querido crear dentro de una carpeta admin. El --model es para especificar de que modelo va a ser el controlador.
*/



class BookingController extends Controller
{

    /**
     * Método para almacenar ficheros temporales. Esto se usa principalmente para cuando estás creando una reserva, has subido algún fichero y hay algún error de validación
     * que no los tengas que volver a subir, que se mantengan en memoria como archivos temporales.
     * @param Request $request
     */
    private function storeTempAttachments(Request $request)
    {
        $tmpFiles = session('tmp_attachments', []);

        if ($request->hasFile('attachment')) {

            foreach ($request->file('attachment') as $file) {

                $path = $file->store('tmp', 'public');

                $tmpFiles[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        session(['tmp_attachments' => $tmpFiles]);

        return $tmpFiles;
    }

    /**
     * Método que 'convierte' los ficheros temporales a ficheros adjuntos para una reserva
     * @param mixed $booking Reserva a la que se van a asociar los ficheros temporales.
     * @return void
     */
    private function saveAttachmentsToBooking($booking)
    {
        $tmpFiles = session('tmp_attachments', []); //Ficheros temporales que se encuentran en sesión

        foreach ($tmpFiles as $file) {
            //Se crea un nuevo fichero adjunto directamente relacionado con la reserva
            $booking->attachments()->create([
                'path' => $file['path'],
                'original_name' => $file['original_name'],
                'mime' => $file['mime'],
                'size' => $file['size'],
                'user_id' => auth()->id(),
            ]);

            //Se crea un log para documentar que se ha adjuntado un fichero a la reserva.
            $this->logAction(
                $booking,
                'archivo adjuntado: ' . $file['original_name']
            );
        }

        session()->forget('tmp_attachments'); //Se eliminan los ficheros temporales de la sesión.
    }

    /**
     * Método que sirve para comprobar que una fecha no esté en el pasado
     * @param Carbon $start
     * @return \Illuminate\Http\RedirectResponse|null
     */
    private function validateNotPast($start, $message = 'No se puede usar una fecha pasada')
    {
        $start = Carbon::parse($start)->second(0);
        $now = now()->second(0);

        if ($start->lt($now)) {
            return back()->withInput()->with('swal', [
                'icon' => 'error',
                'title' => 'Error en fechas',
                'text' => $message
            ]);
        }

        return null;
    }

    /**
     * Función helper para devoler los items de una reserva que pasa de aprobada a cualquier otro estado que no sea finished.
     * @param Booking $booking Reserva a la que se quiere restaurar el stock de los objetos.
     * @return void
     */
    private function restoreItemsStock(Booking $booking)
    {
        //Se almacenan los items que hay en la tabla de bookingItems
        $items = $booking->bookingItems
            ->filter(fn($b) => $b->reservable_type === Item::class);

        //Se crea una transacción para modificar el stock del item.
        DB::transaction(function () use ($items) {

            //Por cada item que haya dentro de la reserva
            foreach ($items as $bItem) {

                //Se bloquea hasta que se actualice.
                $item = Item::lockForUpdate()->find($bItem->reservable_id);
                //Si el item existe, se le suma la cantidad que había en la reserva a la cantidad total del item.
                if ($item) {
                    $item->quantity += $bItem->quantity;
                    $item->save();  //Se guarda el item.
                }
            }
        });
    }



    /**
     * Función helper para redirigir a usuarios o administradores dependiendo de su rol.
     * @param mixed $view Nombre de la vista a la que se quiere redirigir al usuario.
     * @return string Routa a la que se va a redirigir al usuario.
     */
    private function bookingView($view)
    {
        //Si el usuario tiene el rol de administrador, le redirige al grupo para administradores.
        if (auth()->user()->hasRole('admin')) {
            return "admin.bookings.$view";
        }
        //En caso de que no tenga el rol admin (Solo puede tener rol admin o empleado en este caso) se le redirige al grupo de vistas públicas.
        return "public.bookings.$view";
    }
    /**
     * Devuelve al index de administrador o usuario dependiendo del rol
     * @return string Devuelve la ruta index correspondiente al rol.
     */
    private function bookingRedirectRoute()
    {
        if (auth()->user()->hasRole('admin')) {
            return route('admin.bookings.index');
        }

        return route('public.bookings.index');
    }
    /**
     * Método para crear los logs para diversas acciones.
     * @param mixed $booking La reserva sobre la que se ha realizado la acción que se va a registrar.
     * @param mixed $action La acción que se ha realizado sobre la reserva.
     * @return void
     */
    private function logAction($booking, $action)
    {
        AuditLog::create([
            'auditable_type' => get_class($booking), //El tipo de la clase que se registra en el log
            'auditable_id'   => $booking->id, //El id de la reserva sobre la que se ha realizado la acción.
            'user_id'        => auth()->id(), //El id del usuario que ha realizado la acción.
            'action'         => $action, //Acción que se ha realizado.
        ]);
    }
    /**
     * Método para devolver la vista al usuario con todas sus reservas correspondientes.
     * @return \Illuminate\Contracts\View\View
     */
    public function mine()
    {
        $bookings = auth()->user()->bookings; //Se almacenan todas las reservas del usuario.

        return view('public.bookings.index', compact('bookings')); //Se devuelve la vista index con todas sus reservas.
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bookings = Booking::all(); //Se obtienen todas las reservas
        return view($this->bookingView('index'), compact('bookings')); //Se devuelve la vista index con las reservas
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //En caso de que no haya inputs antiguos (Te devuelve aquí por errores de validación)
        if (!old()) {
            session()->forget('tmp_attachments'); //Limpiamos los datos de sesión de los ficheros adjuntos
        }    
        return view($this->bookingView('create')); //Devuelve a la ruta de crear correspondiente.
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'title' => 'required|string|max:255', //Campo título obligatorio, string y como máximo 255 caracteres
                'description' => 'nullable|string', //Campo descripción nullable y string.
                'date_start' => 'required', //Campo para la fecha de inicio requerido
                'time_start' => 'required', //Campo para la hora de inicio requerido
                'date_end' => 'required', //Campo para la fecha de fin requerido
                'time_end' => 'required', //Campo para la hora de fin requerido
            ],
            [
                //Mensajes personalizados por cada error que pueda saltar en las validaciones.
                'title.required' => 'El título de la reserva es obligatorio',
                'title.max' => 'El título de la reserva puede tener como máximo 255 caracteres',

                'description.string' => 'La descripción debe ser un texto',

                'date_start.required' => 'La fecha de inicio es obligatoria',
                'time_start.required' => 'La hora de inicio es obligatoria',
                'date_end.required' => 'La fecha de fin es obligatoria',
                'time_end.required' => 'La hora de fin es obligatoria',
            ]
        );
        //Ficheros que se han subido
        $tmpFiles = $this->storeTempAttachments($request);
        //Recursos que se han reservado
        $resources = $request->input('resources', []);

        //Si la cantidad de recursos es 0, devuelve un error debido a que se tiene que reservar como mínimo 1 recurso.
        if (count($resources) < 1) {
            return back()->withInput()->with('swal', [
                'icon' => 'error',
                'title' => 'Error en recursos',
                'text' => 'Debes seleccionar como mínimo un recurso'
            ]);
        }

        //Fecha de inicio formateada para almacenarla en la base de datos
        $start = Carbon::createFromFormat(
            'd-m-Y H:i',
            $request->date_start . ' ' . $request->time_start
        );
        //Fecha de fin formateada para almacenarla en la base de datos.
        $end = Carbon::createFromFormat(
            'd-m-Y H:i',
            $request->date_end . ' ' . $request->time_end
        );

        //Si la fecha de fin es antes que la fecha de inicio se muestra un error por pantalla.
        if ($end <= $start) {
            return back()->withInput()->with('swal', [
                'icon' => 'error',
                'title' => 'Error en fechas',
                'text' => 'La fecha final debe ser posterior'
            ]);
        }
        /* He decidido no validar que la fecha deba ser pasado porque esto es un borrador, se validará a la hora de publicarla en el método publish         
        $response = $this->validateNotPast($start);
        if($response) return $response; 
        */

        //Por cada recurso que haya dentro de la reserva          
        foreach ($resources as $res) {
            if ($res['type'] === 'room') { //Se comprueba si es una sala
                $exists = Booking::whereNotIn('state', [BookingState::draft, BookingState::cancelled]) //Se comprueba que no esté en estado draft o cancelled
                    ->whereHas('bookingItems', function ($q) use ($res) { //Que tenga booking items que sean salas
                        $q->where('reservable_type', Room::class)
                            ->where('reservable_id', $res['id']);
                    }) //Se comprueba que no se almacena la misma sala en la misma franja horaria.
                    ->where('start_time', '<', $end)
                    ->where('end_time', '>', $start)
                    ->exists();

                //En caso de que si haya un conflicto de horarios se muestra una excepción.
                if ($exists) {
                    $room = Room::find($res['id']);
                    return back()->withInput()->with('swal', [
                        'icon' => 'error',
                        'title' => 'Conflicto de horarios',
                        'text' => "La sala {$room->name} ya está reservada en ese horario."
                    ]);
                }
            }
        }
        //Si no hay ningún error se crea la reserva
        $booking = Booking::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $start,
            'end_time' => $end,
            'user_id' => auth()->id(),
        ]);
        $this->logAction($booking, 'creado'); //Se crea un log para decir que se ha creado una reserva
        //En caso de que se haya hecho un comentario se crea un nuevo registro en la tabla comments.
        if ($request->filled('comment')) {

            $booking->comments()->create([
                'description' => $request->comment,
                'user_id' => auth()->id(),
            ]);
            $this->logAction($booking, 'comentario añadido: ' . substr($request->comment, 0, 200)); //Log para mostrar que se ha añadido un comentario a la reserva.
        }
        $this->saveAttachmentsToBooking($booking); //Se adjuntan los ficheros temporales a la reserva

        // Asociar recursos
        foreach ($resources as $res) {
            $booking->bookingItems()->create([
                'reservable_type' => $res['type'] === 'room' ? Room::class : Item::class, //Se almacena la clase de lo que se ha reservado.
                'reservable_id'   => $res['id'], //Se almacena el id del objeto que se ha reservado
                'quantity' => $res['type'] === 'item' ? ($res['quantity'] ?? 1) : 1, //Si hay cantidad del objeto se almacena esa cantidad y si no, se almacena cantidad 1.
            ]);
        }

        //Se muestra una alerta diciendo que la reserva se ha creado correctamente.
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Reserva creada correctamente',
            'text' => 'La reserva se ha creado correctamente'
        ]);
        return redirect($this->bookingRedirectRoute()); //Se redirige al index dependiendo del rol.
    }
    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        //time: time: 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking)
    {
        //En caso de que no haya inputs pasados, se eliminan de la sesión los ficheros adjuntos (Te redirige aquí porque se ha ocasionado un error de validación)
        if (!old()) {
            session()->forget('tmp_attachments'); //Limpiamos la sesión de ficheros adjuntos
        }    
        //Se obtiene el usuario que quiere acceder a la edición
        $user = auth()->user();

        // Si es empleado, solo puede ver sus reservas y solo draft
        if ($user->hasRole('employee')) {
            $booking = Booking::mine($user)->findOrFail($booking->id);

            if ($booking->state !== BookingState::draft) {
                abort(403); // Bloquea a empleados que intenten editar reservas no draft
            }
        }

        // Los administradores pueden editar cualquier reserva, sin importar el estado
        return view($this->bookingView('edit'), compact('booking'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        //Se obtiene el usuario que quiere actualizar la reserva.
        $user = auth()->user();

        // Si es empleado, solo puede editar sus reservas draft
        if ($user->hasRole('employee')) {
            $booking = Booking::mine($user)->findOrFail($booking->id);

            if ($booking->state !== BookingState::draft) {
                abort(403);
            }
        }

        // Si es admin, no hacemos ningún filtrado, puede editar cualquier reserva
        // -> $booking sigue siendo el que viene por route model binding

        //Validación de los campos provenientes del formulario.
        $request->validate(
            [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date_start' => 'required',
                'time_start' => 'required',
                'date_end' => 'required',
                'time_end' => 'required',
                'state' => ['nullable', new Enum(BookingState::class)],
            ],
            [
                //Mensajes personalizados para cada fallo que se pueda presentar en la validación
                'title.required' => 'El título de la reserva es obligatorio',
                'title.max' => 'El título de la reserva puede tener como máximo 255 caracteres',

                'description.string' => 'La descripción debe ser un texto',

                'date_start.required' => 'La fecha de inicio es obligatoria',
                'time_start.required' => 'La hora de inicio es obligatoria',
                'date_end.required' => 'La fecha de fin es obligatoria',
                'time_end.required' => 'La hora de fin es obligatoria',

                'state.enum' => 'El estado seleccionado no es válido',
            ]
        );
        //Se obtienen los recursos
        $resources = $request->input('resources', []);

        //En caso de que no haya recursos muestra una alerta indicando que se debe seleccionar como mínimo un recurso. 
        if (count($resources) < 1) {
            return back()->withInput()->with('swal', [
                'icon' => 'error',
                'title' => 'Error en recursos',
                'text' => 'Debes seleccionar como mínimo un recurso'
            ]);
        }

        //Se formatea la fecha de inicio para almacenarla en la base de datos.
        $start = Carbon::createFromFormat(
            'd-m-Y H:i',
            $request->date_start . ' ' . $request->time_start
        );
        //Se formatea la fecha de fin para almacenarla en la base de datos.
        $end = Carbon::createFromFormat(
            'd-m-Y H:i',
            $request->date_end . ' ' . $request->time_end
        );

        //Si la fecha de fin es anterior a la fecha de inicio muestra una alerta. 
        if ($end <= $start) {
            return back()->withInput()->with('swal', [
                'icon' => 'error',
                'title' => 'Error en fechas',
                'text' => 'La fecha final debe ser posterior'
            ]);
        }
 
        //Por cada recurso que haya en la reserva
        foreach ($resources as $res) {
            if ($res['type'] === 'room') { //Se comprueba que sea una habitación
                $exists = Booking::where('id', '!=', $booking->id) //La variable exists comprueba que no se solapen dos reservas de la misma sala
                    ->whereNotIn('state', [BookingState::draft, BookingState::cancelled])
                    ->whereHas('bookingItems', function ($q) use ($res) {
                        $q->where('reservable_type', Room::class) // siempre Room para type=room
                            ->where('reservable_id', $res['id']);  // usar array key
                    })
                    ->where('start_time', '<', $end)
                    ->where('end_time', '>', $start)
                    ->exists();

                //En caso de que se solapen dos reservas de la misma sala se muestra una alerta.
                if ($exists) {
                    $room = Room::find($res['id']);
                    return back()->withInput()->with('swal', [
                        'icon' => 'error',
                        'title' => 'Conflicto de horarios',
                        'text' => "La sala {$room->name} ya está reservada en ese horario."
                    ]);
                }
            }
        }
        $oldState = $booking->state->value; // valor antiguo antes de update
        $state = $request->input('state', $booking->state->value); //Estado que se ha introducido en el formulario

        // Validar fechas si se intenta aprobar o poner pendiente
        if (in_array($state, [BookingState::pending->value, BookingState::approved->value])) {
            $response = $this->validateNotPast($start);
            if ($response) return $response;
        }



        //Se actualiza la reserva con todos los datos provenientes del formulario.
        $booking->update([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $start,
            'end_time' => $end,
            'state' => $state,
        ]);
        $this->logAction($booking, 'editado'); //Log para indicar que se ha actualizado la reserva.

        //Log para indicar que se ha cambiado el estado de la reserva
        if ($oldState !== $booking->state->value) {
            $this->logAction($booking, 'estado cambiado de ' . $oldState . ' a ' . $booking->state->value);
        }
        //En caso de que una reserva que estaba aprobada haya pasado a un estado que no sea el mismo (aprobada) o finalizada, se restablece el stock. (Por ejemplo si se cancela la reserva.)
        if (
            $oldState === BookingState::approved->value &&
            $booking->state->value !== BookingState::approved->value &&
            $booking->state->value !== BookingState::finished->value
        ) {
            $this->logAction($booking, 'Restableciendo stock de la reserva porque ha pasado de estado ' . $oldState . ' a estado ' . $booking->state->value); //Log para indicar que se va a restablecer el stock
            $this->restoreItemsStock($booking); //Llamada al método para restablecer el stock.
        }
    

        // --- Si se aprueba la reserva, descontar items y no permitir aprobar una reserva en el pasado---
        if ($oldState !== 'approved' && $booking->state->value === 'approved') { //Si el estado de la reserva ha pasado de algo distinto a aprobado a este estado
            $items = $booking->bookingItems->filter(fn($b) => $b->reservable_type === Item::class); //Se obtienen todos los items que haya dentro de esa reserva.

            //Por cada item que haya en la reserva
            foreach ($booking->bookingItems as $bItem) {

                //Si el objeto es una sala
                if ($bItem->reservable_type === Room::class) {

                    //Se obtiene la sala
                    $room = Room::find($bItem->reservable_id);
                    //Si la sala no existe o si no está activa, muestra un alerta indicando que la sala no está disponible
                    if (!$room || !$room->active) {
                        $booking->update(['state' => $oldState]);

                        return back()->with('swal', [
                            'icon' => 'error',
                            'title' => 'Sala no disponible',
                            'text' => "La sala {$room->name} no está activa."
                        ]);
                    }
                }

                //En caso de que el recurso sea un item
                if ($bItem->reservable_type === Item::class) {
                    //Se obtiene el item de la base de datos.
                    $item = Item::find($bItem->reservable_id);
                    //En caso de que el item no exista o no esté activo muestra una alerta
                    if (!$item || !$item->active) {
                        $booking->update(['state' => $oldState]);

                        return back()->with('swal', [
                            'icon' => 'error',
                            'title' => 'Item no activo',
                            'text' => "El objeto {$item->name} no está activo."
                        ]);
                    }
                    //En caso de que el item tenga un estado diferente a ok, muestra una alerta indicando que el item no está disponible.
                    if ($item->state !== \App\ItemState::ok) {
                        $booking->update(['state' => $oldState]);

                        return back()->with('swal', [
                            'icon' => 'error',
                            'title' => 'Item no disponible',
                            'text' => "El objeto {$item->name} no está disponible."
                        ]);
                    }
                }
            }

            //Se realiza una transacción para comprobar que el item tenga el stock requerido y se resta.
            try {
                DB::transaction(function () use ($items, $booking) {
                    foreach ($items as $bItem) {
                        $item = Item::lockForUpdate()->find($bItem->reservable_id); //Se obtiene el item que se va a reservar

                        //En caso de que la cantidad total del objeto sea menor a la cantidad requerida en la reserva lanza una excepción
                        if ($item->quantity < $bItem->quantity) {
                            throw new Exception("No hay suficiente stock de {$item->name} para aprobar esta reserva.");
                        }

                        //Se resta la cantidad que se ha reservado a la cantidad total
                        $item->quantity -= $bItem->quantity;
                        $item->save(); //Se actualiza el item.
                    }
                });
            } catch (Exception $e) {
                // Volver al estado anterior y mostrar error
                $booking->update(['state' => $oldState]);
                return back()->with('swal', [
                    'icon' => 'error',
                    'title' => 'Error al aprobar reserva',
                    'text' => $e->getMessage(),
                ]);
            }
        }

        //Comentarios
        if ($request->filled('comment')) {
            //Se crea un comentario
            $booking->comments()->create([
                'description' => $request->comment, //Obteniendo la descripción que ha realizado el usuario
                'user_id' => auth()->id(), //Id del usuario que ha realizado el comentario
            ]);
            $this->logAction($booking, 'comentario añadido: ' . substr($request->comment, 0, 200)); //Log para mostrar que se ha añadido un comentario a la reserva.
        }

        //Ficheros adjuntos
        if ($request->hasFile('attachment')) {

            //Por cada fichero adjunto que haya en la request
            foreach ($request->file('attachment') as $file) {
                //Se obtiene la ruta del fichero
                $path = $file->store('attachments', 'public');

                //Se crea un fichero adjunto relacionado con la reserva
                $booking->attachments()->create([
                    'path' => $path, //Se almacena la ruta del fichero
                    'original_name' => $file->getClientOriginalName(), //Se almacena el nombre original del fichero.
                    'mime' => $file->getMimeType(), //Se almacena el tipo de mime
                    'size' => $file->getSize(), //Se almacena el tamaño del fichero
                    'user_id' => auth()->id(), //Se almacena el id del usuario.
                ]);
                $this->logAction($booking, 'archivo adjuntado: ' . $file->getClientOriginalName()); //Se crea un log para mostrar que se ha adjuntado un archivo a esa reserva.
            }
        }

        // Obtener IDs de recursos seleccionados
        $newItems = collect($resources)->map(fn($r) => $r['type'] . '-' . $r['id']);

        // Obtener IDs de recursos actuales
        $oldItems = $booking->bookingItems->map(fn($b) => strtolower(class_basename($b->reservable_type)) . '-' . $b->reservable_id);

        // Eliminar los que ya no están
        $toDelete = $booking->bookingItems->filter(fn($b) => ! $newItems->contains(strtolower(class_basename($b->reservable_type)) . '-' . $b->reservable_id));
        $toDelete->each->delete();

        // Crear los nuevos
        foreach ($resources as $res) {
            $key = $res['type'] . '-' . $res['id'];
            if (! $oldItems->contains($key)) {
                $booking->bookingItems()->create([
                    'reservable_type' => $res['type'] === 'room' ? Room::class : Item::class,
                    'reservable_id'   => $res['id'],
                    'quantity'        => $res['type'] === 'item' ? ($res['quantity'] ?? 1) : 1,
                ]);
            }
        }

        //Se muestra una alerta indicando que la reserva ha sido actualizada correctamente.
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Reserva actualizada correctamente',
            'text' => 'La reserva se ha actualizado correctamente'
        ]);
        return redirect($this->bookingRedirectRoute()); //Se redirige al usuario al index correspondiente (admin o público)
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //Se obtiene el usuario que quiere eliminar la reserva.
        $user = auth()->user();

        // Si el usuario es empleado, mantiene las restricciones
        if ($user->hasRole('employee')) {
            $booking = Booking::mine($user)->findOrFail($booking->id);

            if ($booking->state !== BookingState::draft) {
                abort(403);
            }
        }
        // Si es admin, no hacemos ningún filtrado: puede eliminar cualquier reserva
        // -> $booking sigue siendo el que viene por route model binding

        // Si la reserva estaba aprobada, restablecer stock
        if ($booking->state === BookingState::approved) {
            $this->restoreItemsStock($booking);
        }

        $this->logAction($booking, 'eliminado'); //Se crea el log indicando que se ha eliminado una reserva
        $booking->delete(); //Se elimina la reserva

        //Se muestra una alerta indicando que la reserva ha sido eliminada de manera correcta.
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Reserva eliminada correctamente',
            'text' => 'La reserva se ha eliminado correctamente'
        ]);

        // Redirige según rol
        $statusFilter = request('statusFilter');
        return redirect($this->bookingRedirectRoute() . ($statusFilter ? "?statusFilter=$statusFilter" : ''));
    }
    /**
     * Método que permite a un usuario publicar una reserva
     * @param Booking $booking Reserva que se quiere publicar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function publish(Booking $booking)
    {
        $booking = Booking::mine(auth()->user())->findOrFail($booking->id); //Comprueba que esa reserva sea del usuario.

        //En caso de que esté en un estado diferente a draft muestra el error 403
        if ($booking->state !== BookingState::draft) {
            abort(403);
        }


        //Por cada recurso que se haya reservado
        foreach ($booking->bookingItems as $res) {
            $model = $res->reservable;
            
            //Si no existe el recurso
            if (!$model) {
                return back()->with('swal', [
                    'icon' => 'error',
                    'title' => 'Recurso inválido',
                    'text' => 'Uno de los recursos no existe.'
                ]);
            }

            // sala inactiva
            if ($res->reservable_type === Room::class) {

                if (!$model->active) {
                    return back()->with('swal', [
                        'icon' => 'error',
                        'title' => 'Sala inactiva',
                        'text' => "La sala {$model->name} no está activa."
                    ]);
                }
            }

            // item inválido
            if ($res->reservable_type === Item::class) {

                if (!$model->active) {
                    return back()->with('swal', [
                        'icon' => 'error',
                        'title' => 'Objeto inactivo',
                        'text' => "El objeto {$model->name} no está activo."
                    ]);
                }
                //Item con un estado diferente a ok
                if ($model->state !== \App\ItemState::ok) {
                    return back()->with('swal', [
                        'icon' => 'error',
                        'title' => 'Objeto no disponible',
                        'text' => "El objeto {$model->name} no está disponible."
                    ]);
                }
            }

            //Se comprueba que no exista una reserva que reserve la misma sala en el mismo periodo de tiempo
            $exists = Booking::where('id', '!=', $booking->id)
                ->whereNotIn('state', [BookingState::draft, BookingState::cancelled])
                ->whereHas('bookingItems', function ($q) use ($res) {
                    $q->where('reservable_type', get_class($res->reservable))
                        ->where('reservable_id', $res->reservable_id);
                })
                ->where('start_time', '<', $booking->end_time)
                ->where('end_time', '>', $booking->start_time)
                ->exists();

            //En caso de que exista muestra una alerta indicando que ha habido un conflicto de horarios.
            if ($exists) {
                return back()->with('swal', [
                    'icon' => 'error',
                    'title' => 'Conflicto de horarios',
                    'text' => "El recurso {$res->reservable->name} ya está reservado en ese horario."
                ]);
            }
        }
        //En caso de que un usuario quiera publicar una reserva con fecha en el pasado muestra un error advirtiéndole.
        $response = $this->validateNotPast($booking->start_time,'No se puede publicar una reserva en el pasado');
        if($response) return $response;

        //Si todo va bien se cambio el estado de la reserva para que pase a pendiente
        $booking->update(['state' => BookingState::pending]);
        $this->logAction($booking, 'estado cambiado a Pending (publicado)'); //Se crea un log para registrar la acción realizada.

        //Se muestra una alerta indicando que la reserva se ha publicado de manera correcta.
        return back()->with('swal', [
            'icon' => 'success',
            'title' => 'Reserva publicada',
            'text' => 'Tu reserva ha sido enviada para la revisión del administrador.'
        ]);
    }
    /**
     * Método para cancelar una reserva.
     * @param Booking $booking Reserva que se va a cancelar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Booking $booking)
    {
        //Se obtiene el usuario que quiere cancelar la reservar.
        $user = auth()->user();

        //Se comprueba que la reserva sea del usuario
        $booking = Booking::mine($user)->findOrFail($booking->id);

        //Si la reserva no está pendiente o aprobada no se puede cancelar, muestra un error 403
        if (! in_array($booking->state, [
            BookingState::pending,
            BookingState::approved,
        ])) {
            abort(403);
        }

        //Se cambia el estado de la reserva a cancelada
        $booking->update([
            'state' => BookingState::cancelled,
        ]);

        $this->logAction($booking, 'cancelada'); //Se crea el log para indicar que se ha cancelado la reserva

        //Se muestra una alerta indicando que la reserva se ha cancelado
        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Reserva cancelada',
            'text' => 'La reserva se ha cancelado correctamente'
        ]);

        //Redirige al index correspondiente dependiendo del rol (admin o público)
        return redirect($this->bookingRedirectRoute());
    }
}
