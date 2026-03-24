<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que hace de intermedio entre la relación polimórfica de recursos(salas o items). Se ha creado una tabla intermedia debido a que tiene campos extra como la cantidad
 */
class BookingItem extends Model
{
    protected $fillable = [
        "booking_id","reservable_type","reservable_id","quantity"];
    /**
     * Método que indica que un BookingItem pertenece a una reserva
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<TRelatedModel, BookingItem>
     */
    public function booking(){
        return $this->belongsTo("booking_id");
    }

    /**
     * Método que indica que tiene una relación polimórfica.
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<Model, BookingItem>
     */
    public function reservable()
    {
        return $this->morphTo();
    }
}
