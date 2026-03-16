<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Criando o Usuário ADMIN inicial
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@betalent.tech',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
        ]);

        // Criando os outros níveis para facilitar seu teste
        User::create([
            'name' => 'Financeiro',
            'email' => 'finance@betalent.tech',
            'password' => Hash::make('password'),
            'role' => UserRole::FINANCE,
        ]);

        // Gateway 1
        Gateway::updateOrCreate(['name' => 'Gateway 1'], [
            'api_url'       => config('gateways.g1.url'),
            'client_id'     => config('gateways.g1.client'),
            'client_secret' => config('gateways.g1.secret'),
            'priority'      => 1,
        ]);

        // Gateway 2
        Gateway::updateOrCreate(['name' => 'Gateway 2'], [
            'api_url'       => config('gateways.g2.url'),
            'client_id'     => config('gateways.g2.client'),
            'client_secret' => config('gateways.g2.secret'),
            'priority'      => 2,
        ]);

        // Populando Produtos Iniciais
        Product::create(['name' => 'Curso Laravel Clean Architecture', 'amount' => 50000]);
        Product::create(['name' => 'Mentoria Back-end', 'amount' => 150000]);
    }
}
