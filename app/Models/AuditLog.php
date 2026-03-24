<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para los logs
 */
class AuditLog extends Model
{
    //Atributos que se pueden rellenar mediante asiganación masiva
    protected $fillable= [
        'auditable_id',
        'auditable_type',
        'user_id',
        'action',
    ];
    /**
     * Método que indica que cuentas con una relación polimórfica
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<Model, AuditLog>
     */
    public function auditable(){
        return $this->morphTo();
    }
    /**
     * Método para obtener el usuario que ha realizado la acción del log.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, AuditLog>
     */
    public function user(){
        return $this->belongsTo(User::class);
    }
}
