<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Modelo para los comentarios que se pueden realizar tanto en reservas como en items y salas 
 */
class Comment extends Model
{
    //Atributos que se pueden rellenar mediante asignación masiva
    protected $fillable = [
        'user_id',
        'description',
    ];
    /**
     * Método que indica que tiene una relación polimórfica (Se pueden realizar en reservas, items y salas)
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<Model, Comment>
     */
    public function commentable(){
        return $this->morphTo();
    }
    /**
     * Método que indica que un comentario le pertence a un solo usuario
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Comment>
     */
    public function user(){
        return $this->belongsTo(User::class);
    }
}
