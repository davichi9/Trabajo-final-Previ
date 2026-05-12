<?php

namespace App\Command;

use App\Entity\Clientes;
use App\Entity\Prendas;
use App\Entity\Trabajadores;
use App\Entity\Pedidos;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:load-fixtures',
    description: 'Carga datos de prueba en la base de datos',
)]
class LoadFixturesCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Cargando datos de prueba...');

        // Limpiar datos existentes
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $this->entityManager->getConnection()->executeStatement('TRUNCATE TABLE pedidos');
        $this->entityManager->getConnection()->executeStatement('TRUNCATE TABLE clientes');
        $this->entityManager->getConnection()->executeStatement('TRUNCATE TABLE trabajadores');
        $this->entityManager->getConnection()->executeStatement('TRUNCATE TABLE prendas');
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        // Crear prendas
        $prendas = [
            ['nombre' => 'Pantalón', 'precio' => 15.00],
            ['nombre' => 'Camisa', 'precio' => 12.00],
            ['nombre' => 'Vestido', 'precio' => 25.00],
            ['nombre' => 'Abrigo', 'precio' => 30.00],
            ['nombre' => 'Falda', 'precio' => 18.00],
        ];

        foreach ($prendas as $prenda) {
            $p = new Prendas();
            $p->setNombre($prenda['nombre']);
            $p->setPrecio($prenda['precio']);
            $this->entityManager->persist($p);
        }
        $this->entityManager->flush();
        $output->writeln(' 5 prendas creadas');

        // Crear clientes
        $clientes = [
            [
                'nombre' => 'Juan',
                'apellidos' => 'García López',
                'telefono' => '612345678',
                'email' => 'juan@email.com',
                'domicilio' => 'Calle Principal 123, Madrid',
            ],
            [
                'nombre' => 'María',
                'apellidos' => 'Rodríguez Pérez',
                'telefono' => '687654321',
                'email' => 'maria@email.com',
                'domicilio' => 'Avenida Central 456, Barcelona',
            ],
            [
                'nombre' => 'Carlos',
                'apellidos' => 'Martínez González',
                'telefono' => '698765432',
                'email' => 'carlos@email.com',
                'domicilio' => 'Calle Secundaria 789, Valencia',
            ],
            [
                'nombre' => 'Ana',
                'apellidos' => 'López Sánchez',
                'telefono' => '623456789',
                'email' => 'ana@email.com',
                'domicilio' => 'Plaza Mayor 321, Sevilla',
            ],
        ];

        $clientesList = [];
        foreach ($clientes as $cliente) {
            $c = new Clientes();
            $c->setNombre($cliente['nombre']);
            $c->setApellidos($cliente['apellidos']);
            $c->setTelefonoNumero($cliente['telefono']);
            $c->setEmail($cliente['email']);
            $c->setDomicilio($cliente['domicilio']);
            $this->entityManager->persist($c);
            $clientesList[] = $c;
        }
        $this->entityManager->flush();
        $output->writeln(' 4 clientes creados');

        // Crear trabajadores
        $trabajadores = [
            [
                'nombre' => 'Carlos',
                'apellidos' => 'Hernández Díaz',
                'telefono' => '912345678',
                'email' => 'carlos.hernandez@empresa.com',
                'contraseña' => 'password123',
                'rol' => 'admin',
            ],
            [
                'nombre' => 'Elena',
                'apellidos' => 'Fernández Ruiz',
                'telefono' => '912345679',
                'email' => 'elena.fernandez@empresa.com',
                'contraseña' => 'password123',
                'rol' => 'supervisor',
            ],
            [
                'nombre' => 'David',
                'apellidos' => 'Jiménez Moreno',
                'telefono' => '912345680',
                'email' => 'david.jimenez@empresa.com',
                'contraseña' => 'password123',
                'rol' => 'worker',
            ],
        ];

        foreach ($trabajadores as $trabajador) {
            $t = new Trabajadores();
            $t->setNombre($trabajador['nombre']);
            $t->setApellidos($trabajador['apellidos']);
            $t->setTelefonoNumero($trabajador['telefono']);
            $t->setEmail($trabajador['email']);
            $t->setContraseña(password_hash($trabajador['contraseña'], PASSWORD_BCRYPT));
            $t->setRol($trabajador['rol']);
            $this->entityManager->persist($t);
        }
        $this->entityManager->flush();
        $output->writeln(' 3 trabajadores creados');

        // Get trabajadores from database
        $trabajadoresList = $this->entityManager->getRepository(Trabajadores::class)->findAll();

        // Crear muchos pedidos para testing de reportes
        $pedidos = [];
        $contents = [
            'Lavado normal: 3 camisas, 2 pantalones',
            'Lavado delicado: vestido de seda, ropa interior',
            'Lavado en seco: abrigo de lana, quitar manchas',
            'Quitar manchas: chaqueta de cuero, planchado',
            'Lavado normal: sábanas, toallas, mantel',
            'Planchado express: 5 camisas de trabajo',
            'Limpieza profunda: edredón, almohadas',
            'Lavado especial: ropa deportiva, zapatos',
            'Tintorería: pantalón formal, corbata',
            'Lavado a mano: prendas delicadas, encaje',
        ];

        // FEBRUARY 2026 data (4 weeks)
        $feb_weeks = [
            [1, 7],     // Week 1: Feb 1-7
            [8, 14],    // Week 2: Feb 8-14
            [15, 21],   // Week 3: Feb 15-21
            [22, 28],   // Week 4: Feb 22-28
        ];
        
        $idx = 0;
        foreach ($feb_weeks as $week) {
            for ($i = 0; $i < 5; $i++) {
                $day = $week[0] + ($i % 7);
                if ($day > $week[1]) $day = $week[1];
                $salida_day = min($day + 1, $week[1]);
                $pedidos[] = [
                    'estado' => 'recogido',
                    'contenido' => $contents[($idx + $i) % count($contents)],
                    'fecha_entrada' => new DateTime('2026-02-' . str_pad($day, 2, '0', STR_PAD_LEFT)),
                    'fecha_salida' => new DateTime('2026-02-' . str_pad($salida_day, 2, '0', STR_PAD_LEFT)),
                    'precio' => 12 + rand(0, 20),
                    'pagado' => true,
                    'cliente' => $clientesList[rand(0, 3)],
                    'trabajador' => $trabajadoresList[rand(0, 2)],
                ];
            }
            $idx += 5;
        }

        // MARCH 2026 data (4 weeks + partial)
        $mar_weeks = [
            [1, 7],     // Week 1: Mar 1-7
            [8, 14],    // Week 2: Mar 8-14
            [15, 21],   // Week 3: Mar 15-21
            [22, 28],   // Week 4: Mar 22-28
            [29, 31],   // Week 5: Mar 29-31
        ];
        
        $idx = 0;
        foreach ($mar_weeks as $week) {
            for ($i = 0; $i < 6; $i++) {
                $day = $week[0] + ($i % 7);
                if ($day > $week[1]) $day = $week[1];
                $salida_day = min($day + 1, $week[1]);
                $pedidos[] = [
                    'estado' => 'recogido',
                    'contenido' => $contents[($idx + $i) % count($contents)],
                    'fecha_entrada' => new DateTime('2026-03-' . str_pad($day, 2, '0', STR_PAD_LEFT)),
                    'fecha_salida' => new DateTime('2026-03-' . str_pad($salida_day, 2, '0', STR_PAD_LEFT)),
                    'precio' => 14 + rand(0, 22),
                    'pagado' => true,
                    'cliente' => $clientesList[rand(0, 3)],
                    'trabajador' => $trabajadoresList[rand(0, 2)],
                ];
            }
            $idx += 6;
        }

        // APRIL 2026 data (4 weeks)
        $apr_weeks = [
            [1, 7],     // Week 1: Apr 1-7
            [8, 14],    // Week 2: Apr 8-14
            [15, 21],   // Week 3: Apr 15-21
            [22, 28],   // Week 4: Apr 22-28
            [29, 30],   // Week 5: Apr 29-30
        ];
        
        $idx = 0;
        foreach ($apr_weeks as $week) {
            for ($i = 0; $i < 5; $i++) {
                $day = $week[0] + ($i % 7);
                if ($day > $week[1]) $day = $week[1];
                $salida_day = min($day + 1, $week[1]);
                $pedidos[] = [
                    'estado' => 'recogido',
                    'contenido' => $contents[($idx + $i) % count($contents)],
                    'fecha_entrada' => new DateTime('2026-04-' . str_pad($day, 2, '0', STR_PAD_LEFT)),
                    'fecha_salida' => new DateTime('2026-04-' . str_pad($salida_day, 2, '0', STR_PAD_LEFT)),
                    'precio' => 13 + rand(0, 25),
                    'pagado' => true,
                    'cliente' => $clientesList[rand(0, 3)],
                    'trabajador' => $trabajadoresList[rand(0, 2)],
                ];
            }
            $idx += 5;
        }

        // MAY 2026 data - Proper weekly alignment
        // Week 1 (May 1-7)
        for ($i = 0; $i < 8; $i++) {
            $day = 1 + ($i % 7);
            $salida_day = min($day + 1 + ($i % 2), 7);
            $pedidos[] = [
                'estado' => 'recogido',
                'contenido' => $contents[$i % count($contents)],
                'fecha_entrada' => new DateTime('2026-05-' . str_pad($day, 2, '0', STR_PAD_LEFT)),
                'fecha_salida' => new DateTime('2026-05-' . str_pad($salida_day, 2, '0', STR_PAD_LEFT)),
                'precio' => 15 + rand(0, 20),
                'pagado' => true,
                'cliente' => $clientesList[rand(0, 3)],
                'trabajador' => $trabajadoresList[rand(0, 2)],
            ];
        }

        // Week 2 (May 8-14)
        for ($i = 0; $i < 12; $i++) {
            $day = 8 + ($i % 7);
            $salida_day = min($day + 1 + ($i % 2), 14);
            $pedidos[] = [
                'estado' => 'recogido',
                'contenido' => $contents[($i + 2) % count($contents)],
                'fecha_entrada' => new DateTime('2026-05-' . str_pad($day, 2, '0', STR_PAD_LEFT)),
                'fecha_salida' => new DateTime('2026-05-' . str_pad($salida_day, 2, '0', STR_PAD_LEFT)),
                'precio' => 12 + rand(0, 25),
                'pagado' => true,
                'cliente' => $clientesList[rand(0, 3)],
                'trabajador' => $trabajadoresList[rand(0, 2)],
            ];
        }

        // Week 3 (May 15-21)
        for ($i = 0; $i < 15; $i++) {
            $day = 15 + ($i % 7);
            $salida_day = min($day + 1 + ($i % 2), 21);
            $pedidos[] = [
                'estado' => 'recogido',
                'contenido' => $contents[($i + 4) % count($contents)],
                'fecha_entrada' => new DateTime('2026-05-' . str_pad($day, 2, '0', STR_PAD_LEFT)),
                'fecha_salida' => new DateTime('2026-05-' . str_pad($salida_day, 2, '0', STR_PAD_LEFT)),
                'precio' => 18 + rand(0, 22),
                'pagado' => true,
                'cliente' => $clientesList[rand(0, 3)],
                'trabajador' => $trabajadoresList[rand(0, 2)],
            ];
        }

        // Week 4 (May 22-28)
        for ($i = 0; $i < 14; $i++) {
            $day = 22 + ($i % 7);
            $salida_day = min($day + 1 + ($i % 2), 28);
            $pedidos[] = [
                'estado' => 'recogido',
                'contenido' => $contents[($i + 6) % count($contents)],
                'fecha_entrada' => new DateTime('2026-05-' . str_pad($day, 2, '0', STR_PAD_LEFT)),
                'fecha_salida' => new DateTime('2026-05-' . str_pad($salida_day, 2, '0', STR_PAD_LEFT)),
                'precio' => 14 + rand(0, 28),
                'pagado' => true,
                'cliente' => $clientesList[rand(0, 3)],
                'trabajador' => $trabajadoresList[rand(0, 2)],
            ];
        }

        // Week 5 (May 29-31)
        for ($i = 0; $i < 9; $i++) {
            $day = 29 + ($i % 3);
            $salida_day = min($day + 1, 31);
            $pedidos[] = [
                'estado' => 'recogido',
                'contenido' => $contents[($i + 8) % count($contents)],
                'fecha_entrada' => new DateTime('2026-05-' . str_pad($day, 2, '0', STR_PAD_LEFT)),
                'fecha_salida' => new DateTime('2026-05-' . str_pad($salida_day, 2, '0', STR_PAD_LEFT)),
                'precio' => 16 + rand(0, 24),
                'pagado' => true,
                'cliente' => $clientesList[rand(0, 3)],
                'trabajador' => $trabajadoresList[rand(0, 2)],
            ];
        }

        foreach ($pedidos as $pedido) {
            $pe = new Pedidos();
            $pe->setEstado($pedido['estado']);
            $pe->setContenido($pedido['contenido']);
            $pe->setFechaEntrada($pedido['fecha_entrada']);
            $pe->setFechaSalida($pedido['fecha_salida']);
            $pe->setPrecio($pedido['precio']);
            $pe->setPagado($pedido['pagado']);
            $pe->setCliente($pedido['cliente']);
            $pe->setTrabajador($pedido['trabajador']);
            $this->entityManager->persist($pe);
        }
        $this->entityManager->flush();
        $output->writeln(' ' . count($pedidos) . ' pedidos creados');

        $output->writeln('');
        $output->writeln('<info> Datos de prueba cargados exitosamente!</info>');

        return Command::SUCCESS;
    }
}
