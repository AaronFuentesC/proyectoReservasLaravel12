<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BookingItem;
use App\BookingState;
use App\Models\Attachment;
use App\ItemState;

/**
 * Modelo para las reservas
 */
class Booking extends Model
{
    /**
     * Accesor para las fechas de una reserva. Comprueba si es dentro de un mismo día o un día diferente y dependiendo de ello, lo muestra de una manera o de otra.
     * @return string
     */
    public function getDateRangeAttribute()
    {
    //En caso de que la fecha de inicio y la fecha de fin sean el mismo día lo devuelve en un formato
        if ($this->start_time->isSameDay($this->end_time)) {

            return
                $this->start_time->format('d M Y') .
                ' · ' .
                $this->start_time->format('H:i') .
                ' → ' .
                $this->end_time->format('H:i');
        }

        //En caso de que la fecha de inicio y la fecha de fin sean en días separados, lo devuelve en otro formato distinto.
        return
            $this->start_time->format('d M H:i') .
            ' → ' .
            $this->end_time->format('d M H:i');
    }

    /**
     * Método para obtener la duración total de la reserva
     * @return string Devuelve un string indicando la duración total de una reserva
     */
    public function getDurationAttribute()
    {
        $minutes = $this->start_time->diffInMinutes($this->end_time); //Minutos que va a durar la reserva

        $hours = floor($minutes / 60); //Horas que va a durar la reserva
        $mins = $minutes % 60; 

        //En caso de que la reserva dure más de una hora y más de un minuto lo devuelve en este formato
        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        }
        //En caso de que la reserva dure una cierta cantidad de horas justas, es decir sin minutos sobrantes lo devuelve de esta manera
        if ($hours > 0) {
            return "{$hours}h";
        }
        
        //Y en caso de que la reserva solo dure ciertos minutos lo devuelve de esta forma.
        return "{$mins}m";
    }
    /**
     * Método que comprueba si la reserva está en curso, es pasada o es futura
     * @return string
     */
    public function getTimeStatusAttribute()
    {
        //Si la fecha actual está entre la hora de inicio y la hora de fin, devuelve que la reserva está en curso
        if (now()->between($this->start_time, $this->end_time)) {
            return 'current';
        }

        //Si la fecha de fin es anterior a la fecha actual, la reserva es pasada
        if ($this->end_time < now()) {
            return 'past';
        }

        //Si no se cumple ninguna de las condiciones anteriores, la reserva es futura.
        return 'future';
    }
    /**
     * Método que comprueba si una reserva tiene recursos inválidos
     * @return bool Devuelve true en caso de que sí tenga recursos inválidos, devuelve false si no
     */
    public function hasInvalidResources(): bool
    {
        foreach ($this->bookingItems as $bItem) {

            //En caso de que el tipo de reservable sea una habitación
            if ($bItem->reservable_type === Room::class) {
                //Se obtiene la habitación
                $room = Room::find($bItem->reservable_id);
                //Se comprueba que la habitación tenga estado activa.
                if (!$room || !$room->active) {
                    return true;
                }
            }

            //En caso de que sea un equipo
            if ($bItem->reservable_type === Item::class) {
                //Se obtiene el equipo
                $item = Item::find($bItem->reservable_id);
    
                //En caso de que no exista, no esté activo o que el estado sea diferente a ok devuelve true
                if (
                    !$item ||
                    !$item->active ||
                    $item->state !== ItemState::ok
                ) {
                    return true;
                }
            }
        }
        
        //En caso de que no se cumpla ninguna de las condiciones anteriores significa que todos los recursos son válidos por lo que devuelve false.
        return false;
    }
    //Atributos que se van a poder rellenar mediante asignación masiva
    protected $fillable = [
        'title',
        'description',
        'start_time',
        'end_time',
        'state',
        'user_id'
    ];
    //Atributos que se quiere que se traten como otros tipos (En este caso fecha y enum de estado)
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'state' => BookingState::class,
    ];
    /**
     * Método que indica que tiene una relación con muchos bookingItems
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<BookingItem, Booking>
     */
    public function bookingItems()
    {
        return $this->hasMany(BookingItem::class);
    }
    /**
     * Método que indica que tiene una realación polimórfica con los comentarios. Una reserva puede tener muchos comentarios
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<Comment, Booking>
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->latest(); //Se añade latest para que se muestre en orden descendiente.
    }
    /**
     * Método que indica que tiene una relación polimórfica con los ficheros adjuntos. Una reserva puede tener muchos ficheros adjuntos
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<Attachment, Booking>
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
    /**
     * Método que indica que tiene una relación con usuarios en la que una reserva le pertenece a un usuario.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Booking>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // App/Models/Booking.php
    /**
     * Método que devuelve todo lo que pertenezca a un usuario para que lo pueda observar (a no ser que sea admin, debido a que los adminsitrador pueden ver todo)
     * @param mixed $query
     * @param mixed $user
     */
    public function scopeMine($query, $user)
    {
        if ($user->hasRole('employee')) {
            return $query->where('user_id', $user->id);
        }

        return $query; // admins ven todos
    }
}
