<?php
// database/seeders/ClienteSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'nomCli' => 'Juan Pérez García',
                'tipDocCli' => 'CC',
                'docCli' => '12345678',
                'telCli' => '3001234567',
                'emailCli' => 'juan.perez@email.com',
                'dirCli' => 'Calle 123 #45-67, Bogotá',
                'tipCli' => 'particular',
                'estCli' => 'activo',
                'obsCli' => 'Cliente frecuente',
            ],
            [
                'nomCli' => 'María López Rodríguez',
                'tipDocCli' => 'CC',
                'docCli' => '87654321',
                'telCli' => '3109876543',
                'emailCli' => 'maria.lopez@email.com',
                'dirCli' => 'Carrera 98 #76-54, Medellín',
                'tipCli' => 'particular',
                'estCli' => 'activo',
                'obsCli' => null,
            ],
            [
                'nomCli' => 'Empresa ABC S.A.S.',
                'tipDocCli' => 'NIT',
                'docCli' => '900123456-1',
                'telCli' => '6015551234',
                'emailCli' => 'contacto@empresaabc.com',
                'dirCli' => 'Av. Principal #100-200, Cali',
                'tipCli' => 'empresa',
                'estCli' => 'activo',
                'obsCli' => 'Cliente corporativo',
            ],
            [
                'nomCli' => 'Carlos Martínez Silva',
                'tipDocCli' => 'CC',
                'docCli' => '11223344',
                'telCli' => '3201112233',
                'emailCli' => 'carlos.martinez@email.com',
                'dirCli' => 'Diagonal 45 #23-89, Barranquilla',
                'tipCli' => 'particular',
                'estCli' => 'activo',
                'obsCli' => null,
            ],
            [
                'nomCli' => 'Tech Solutions Ltda.',
                'tipDocCli' => 'NIT',
                'docCli' => '800567890-2',
                'telCli' => '6017778899',
                'emailCli' => 'info@techsolutions.com',
                'dirCli' => 'Torre Empresarial, Piso 15, Bucaramanga',
                'tipCli' => 'empresa',
                'estCli' => 'activo',
                'obsCli' => 'Cliente de tecnología',
            ],
            [
                'nomCli' => 'Ana Sofía Morales',
                'tipDocCli' => 'CE',
                'docCli' => 'CE-1234567',
                'telCli' => '3154445566',
                'emailCli' => 'ana.morales@email.com',
                'dirCli' => 'Zona Rosa, Apartamento 301, Pereira',
                'tipCli' => 'particular',
                'estCli' => 'activo',
                'obsCli' => 'Cliente extranjero',
            ],
            [
                'nomCli' => 'Cliente Inactivo Test',
                'tipDocCli' => 'CC',
                'docCli' => '99999999',
                'telCli' => '3009999999',
                'emailCli' => 'inactivo@test.com',
                'dirCli' => 'Dirección de prueba',
                'tipCli' => 'particular',
                'estCli' => 'inactivo',
                'obsCli' => 'Cliente de prueba inactivo',
            ],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
    }
}