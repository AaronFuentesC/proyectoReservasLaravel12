<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ClearTmpAttachments extends Command
{
    // Nombre del comando que usarás por Artisan
    protected $signature = 'booking:clear-tmp';

    // Descripción del comando
    protected $description = 'Elimina archivos temporales de reservas que tengan más de 24 horas';

    /**
     * Método que se va a ejecutar cuando se llame al comando
     * @return void
     */
    public function handle()
    {
        //Mensaje de información
        $this->info("Buscando archivos temporales...");

        $files = Storage::disk('public')->files('tmp'); //Comprueba que haya archivos temporales

        $deleted = 0; //Al principio se borran 0 archivos
        $now = Carbon::now(); //Se obtiene la fecha actual

        //Por cada fichero que haya
        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(Storage::disk('public')->lastModified($file)); //Se obtiene la fecha en la que se modificó el fichero por última vez

            //En caso de que el fichero lleve sin modificarse un día o más, se elimina el fichero y se incrementa el contador de eliminados.
            if ($lastModified->diffInHours($now) > 24) {
                Storage::disk('public')->delete($file);
                $deleted++;
                $this->info("Eliminado: {$file}");
            }
        }

        $this->info("Proceso finalizado. Archivos eliminados: {$deleted}");
    }
}