<?php

namespace Database\Seeders;

use App\ItemState;
use App\ItemType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Item::create([
            'name' => 'Proyector nuevo',
            'type' => ItemType::Projector,
            'serial_number' => 'IT0001',
            'state' => ItemState::ok,
            'quantity' => 5,
            'active' => true,
        ]);
        Item::create([
            'name' => 'Portátil de última generación',
            'type' => ItemType::Laptop,
            'serial_number' => 'IT0002',
            'state' => ItemState::ok,
            'quantity' => 2,
            'active' => true,
        ]);
        Item::create([
            'name' => 'Coche nuevo',
            'type' => ItemType::Car,
            'serial_number' => 'IT0003',
            'state' => ItemState::ok,
            'quantity' => 1,
            'active' => true,
        ]);
        Item::create([
            'name' => 'Proyector portátil',
            'type' => ItemType::Projector,
            'serial_number' => 'IT0004',
            'state' => ItemState::ok,
            'quantity' => 3,
            'active' => true,
        ]);
        Item::create([
            'name' => 'Portátil de oficina',
            'type' => ItemType::Laptop,
            'serial_number' => 'IT0005',
            'state' => ItemState::maintenance,
            'quantity' => 5,
            'active' => true,
        ]);
        Item::create([
            'name' => 'Coche antiguo',
            'type' => ItemType::Car,
            'serial_number' => 'IT0006',
            'state' => ItemState::not_available,
            'quantity' => 1,
            'active' => false,
        ]);
    }
}
