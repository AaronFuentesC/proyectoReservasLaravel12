<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BookingItem;

/**
 * Modelo para las salas que se van a poder reservar
 */
class Room extends Model
{
    //Atributos que se van a poder rellenar mediante asignación masiva
    protected $fillable = [
        "name",
        "location",
        "capacity",
        "active",
        "description"
    ];
    //Atributos que se quieren que se traten como otros tipos, en este caso active quiere que se trate como un boolean
    protected $casts = [
        "active" => "boolean",
    ];
    /**
     * Método que relaciona Room con bookingItems indicando que es un tipo de los que se puede reservar.
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<BookingItem, Room>
     */
    public function bookingItems()
    {
        //Se puede reservar o una habitación o un item.
        return $this->morphMany(BookingItem::class, 'reservable');
    }
    /**
     * Método que relaciona Room con Comment indicando que una sala puede tener varios comentarios
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<Comment, Room>
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->latest(); //Se añade latest para que se muestre en orden descendiente. 
    }
    /**
     * Método que relaciona Room con Attachment, indicando que una sala puede tener varios comentarios.
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<Attachment, Room>
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
