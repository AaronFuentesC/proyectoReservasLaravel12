<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;


class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Room::create([
            'name' => 'Sala de ejemplo',
            'location'=> 'Ubicación de ejemplo',
            'capacity'=> 20,
            'active' => true,
            'description' =>'Descripción de ejemplo',
        ]);
        Room::create([
            'name' => 'Sala de reuniones',
            'location'=> 'Ubicación de reuniones',
            'capacity'=> 15,
            'active' => true,
            'description' =>'Sala de reuniones pensada para equipos pequeños',
        ]);
        Room::create([
            'name' => 'Salón de actos',
            'location'=> 'Ubicación del salón de actos',
            'capacity'=> 200,
            'active' => true,
            'description' =>'Salón de actos para eventos que requieran de una gran cantidad de capacidad',
        ]);
        Room::create([
            'name' => 'Oficina simple',
            'location'=> 'Ubicación de la oficina simple',
            'capacity'=> 1,
            'active' => true,
            'description' =>'Oficina simple diseñada para una sola persona',
        ]);
        Room::create([
            'name' => 'Oficina dual',
            'location'=> 'Ubicación de la oficina dual',
            'capacity'=> 2,
            'active' => true,
            'description' =>'Oficina dual diseñada para 2 personas',
        ]);
        Room::create([
            'name' => 'Oficina para 8 personas',
            'location'=> 'Ubicación de la oficina para 8 personas',
            'capacity'=> 8,
            'active' => true,
            'description' =>'Oficina con una capacidad para 8 personas',
        ]);
    }
}
