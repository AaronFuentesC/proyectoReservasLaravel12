<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'password'=> bcrypt('12345678'),
        ]);
        $admin->assignRole('admin');
        $employee = User::create([
            'name'=> 'Empleado',
            'email'=> 'empleado@empleado.com',
            'password'=> bcrypt('12345678'),
        ]);
        $employee->assignRole('employee');
        $aaron = User::create([
            'name' => 'Aarón Fuentes',
            'email' => 'aaronfuentes@ejemplo.com',
            'password'=> bcrypt('12345678'),
        ]);
        $aaron->assignRole('admin');
    }
}
