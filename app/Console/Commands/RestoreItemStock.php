<?php
namespace App\Console\Commands;

use App\BookingState;
use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

/**
 * Clase que se va a usar para utilizar un comando programado para cambiar el stock de las reservas dependiendo de los estados.
 */
class RestoreItemStock extends Command
{
    protected $signature = 'booking:restore-stock';
    protected $description = 'Restaurar el stock de items de reservas finalizadas';

    public function handle()
    {
        //Se obtienen todos las reservas cuyo estado sea aprobado.
        $bookings = Booking::where('state', 'approved')
            ->get();
        //dump('Sin condición');
        //Por cada reserva que haya dentro de este atributo
        foreach ($bookings as $booking) {
            //Se filtran para obtener solo las reservas que tienen items
            $items = $booking->bookingItems->filter(fn($b) => $b->reservable_type === Item::class);
            //dump('Segunda condición');
            //Se realiza una transición en la base de datos.
            DB::transaction(function () use ($items, $booking) {
                foreach ($items as $bItem) {
                    //dump('Tercera condición');
                    $item = Item::lockForUpdate()->find($bItem->reservable_id); //Se bloquea hasta que se actualice.
                    $item->quantity += $bItem->quantity; //Se suma la cantidad de items que había en la reserva a las que tiene el item actualmente.
                    $item->save(); //Se guarda el item en la base de datos.
                    
                }
            });
            // importante para no sumar varias veces. Cuando se termine de reponer el stock, se actualiza la reserva para cambiar el estado a finished.
            $booking->update([
                'state' => BookingState::finished,
            ]);
        }
    }
}
