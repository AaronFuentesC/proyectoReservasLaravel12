<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Modelo para ficheros adjuntos
 */
class Attachment extends Model
{
    //Atributos que pueden ser rellenados de forma masiva
    protected $fillable = [
        "user_id",
        "path",
        "original_name",
        "mime",
        "size"
    ];
    /**
     * Función para indicar que tiene una relación polimórfica
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<Model, Attachment>
     */
    public function attachable()
    {
        return $this->morphTo();
    }
    /**
     * Función para obtener el usuario que ha subido el fichero.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Attachment>
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
