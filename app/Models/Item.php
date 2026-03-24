<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BookingItem;
use App\ItemType;
use App\ItemState;

/**
 * Modelo para los equipos que se van a poder reservar
 */
class Item extends Model
{
    //Atributos que se pueden rellenar mediante asignación masiva
    protected $fillable = [
        "name",
        "type",
        "serial_number",
        "state",
        "quantity",
        "active"

    ];
    //Atributos que se quieren tratar como otros tipos (activo como boolean, type y state como enums)
    protected $casts = [
        "active" => "boolean",
        "type" => ItemType::class,
        "state" => ItemState::class,
    ];
    /**
     * Método que indica que un item se puede reservar y se muestra en la tabla booking_items
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<BookingItem, Item>
     */
    public function bookingItems()
    {
        //Se puede reservar o un item o una habitación.
        return $this->morphMany(BookingItem::class, 'reservable');
    }


    /**
     * Método que relaciona item con comment e indica que un item puede tener varios comentarios
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<Comment, Item>
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->latest(); //Se añade latest para que los muestre en oden descendente.
    }
    /**
     * Método que relacione item con attachment e indica que un item puede tener varios ficheros adjuntos
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<Attachment, Item>
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
