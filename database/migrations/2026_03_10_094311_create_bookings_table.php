<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->dateTime('start_time')->index();
            $table->dateTime('end_time')->index();
            $table->enum('state',['draft','pending','approved','rejected','cancelled','finished'])->default('draft');
            $table->timestamps();

            $table->unsignedBigInteger('user_id'); //Creamos el atributo en el que se va a almacenar el atributo.
            $table->foreign('user_id') //El nombre del atributo en esta tabla.
            ->references('id') //El nombre del atributo al que referencia en la otra tabla.
            ->on('users')//La tabla con la que se quiere hacer la relación.
            ->onDelete('cascade') //La acción que se va a realizar cuando se elimine un usuario. Cuando se elimina un usuario se elimina su teléfono asociado. 
            ->onUpdate('cascade'); //Cuando se actualice la tabla de usuarios, se actualizará la de teléfonos también. */ 
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
