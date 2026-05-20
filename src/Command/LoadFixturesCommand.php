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
                'rol' => 'supervisor',
            ],
            [
                'nombre' => 'Elena',
                'apellidos' => 'Fernández Ruiz',
                'telefono' => '912345679',
                'email' => 'elena.fernandez@empresa.com',
                'contraseña' => 'password123',
                'rol' => 'trabajador',
            ],
            [
                'nombre' => 'David',
                'apellidos' => 'Jiménez Moreno',
                'telefono' => '912345680',
                'email' => 'david.jimenez@empresa.com',
                'contraseña' => 'password123',
                'rol' => 'trabajador',
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

        // Get prendas from database
        $prendasList = $this->entityManager->getRepository(Prendas::class)->findAll();

        // Helper function to generate random prendas selection with contenido and price
        $generatePrendas = function() use ($prendasList) {
            $selectedPrendas = [];
            $totalPrice = 0;
            $numPrendas = rand(2, 5); // Random number of different prendas

            // Shuffle and pick random prendas
            $shuffled = $prendasList;
            shuffle($shuffled);
            
            for ($i = 0; $i < min($numPrendas, count($prendasList)); $i++) {
                $cantidad = rand(1, 8);
                $selectedPrendas[] = [
                    'cantidad' => $cantidad,
                    'nombre' => $shuffled[$i]->getNombre(),
                    'precio' => $shuffled[$i]->getPrecio(),
                ];
                $totalPrice += $cantidad * $shuffled[$i]->getPrecio();
            }

            // Generate contenido string
            $contenidoParts = [];
            foreach ($selectedPrendas as $prenda) {
                $nombre = $prenda['nombre'];
                // Simple pluralization: add 's' if not already ending with 's'
                if ($prenda['cantidad'] > 1 && !str_ends_with(strtolower($nombre), 's')) {
                    $nombre .= 's';
                }
                $contenidoParts[] = $prenda['cantidad'] . 'x ' . $nombre;
            }
            $contenido = implode(' ', $contenidoParts);

            return [
                'contenido' => $contenido,
                'precio' => round($totalPrice, 2),
            ];
        };

        // Crear 100 pedidos para testing de reportes
        $pedidos = [];
        $cl = count($clientesList) - 1;
        $tl = count($trabajadoresList) - 1;

        // FEBRERO 2026 — 18 pedidos (recogido + pagado)
        $febDates = [
            '2026-02-02','2026-02-03','2026-02-04','2026-02-05','2026-02-07',
            '2026-02-09','2026-02-10','2026-02-11','2026-02-12','2026-02-14',
            '2026-02-16','2026-02-17','2026-02-18','2026-02-19','2026-02-21',
            '2026-02-23','2026-02-24','2026-02-26',
        ];
        foreach ($febDates as $date) {
            $dIn  = new DateTime($date);
            $dOut = (clone $dIn)->modify('+' . rand(1, 3) . ' days');
            $prendas = $generatePrendas();
            $pedidos[] = ['estado'=>'recogido','contenido'=>$prendas['contenido'],'fecha_entrada'=>$dIn,'fecha_salida'=>$dOut,'precio'=>$prendas['precio'],'pagado'=>true,'cliente'=>$clientesList[rand(0,$cl)],'trabajador'=>$trabajadoresList[rand(0,$tl)]];
        }

        // MARZO 2026 — 22 pedidos (recogido + pagado)
        $marDates = [
            '2026-03-02','2026-03-03','2026-03-05','2026-03-06','2026-03-07',
            '2026-03-09','2026-03-10','2026-03-11','2026-03-12','2026-03-13',
            '2026-03-16','2026-03-17','2026-03-18','2026-03-19','2026-03-20',
            '2026-03-23','2026-03-24','2026-03-25','2026-03-26','2026-03-27',
            '2026-03-28','2026-03-30',
        ];
        foreach ($marDates as $date) {
            $dIn  = new DateTime($date);
            $dOut = (clone $dIn)->modify('+' . rand(1, 3) . ' days');
            $prendas = $generatePrendas();
            $pedidos[] = ['estado'=>'recogido','contenido'=>$prendas['contenido'],'fecha_entrada'=>$dIn,'fecha_salida'=>$dOut,'precio'=>$prendas['precio'],'pagado'=>true,'cliente'=>$clientesList[rand(0,$cl)],'trabajador'=>$trabajadoresList[rand(0,$tl)]];
        }

        // ABRIL 2026 — 25 pedidos (recogido + pagado)
        $aprDates = [
            '2026-04-01','2026-04-02','2026-04-03','2026-04-06','2026-04-07',
            '2026-04-07','2026-04-08','2026-04-09','2026-04-13','2026-04-14',
            '2026-04-14','2026-04-15','2026-04-16','2026-04-17','2026-04-20',
            '2026-04-21','2026-04-21','2026-04-22','2026-04-23','2026-04-24',
            '2026-04-25','2026-04-27','2026-04-28','2026-04-29','2026-04-30',
        ];
        foreach ($aprDates as $date) {
            $dIn  = new DateTime($date);
            $dOut = (clone $dIn)->modify('+' . rand(1, 3) . ' days');
            $prendas = $generatePrendas();
            $pedidos[] = ['estado'=>'recogido','contenido'=>$prendas['contenido'],'fecha_entrada'=>$dIn,'fecha_salida'=>$dOut,'precio'=>$prendas['precio'],'pagado'=>true,'cliente'=>$clientesList[rand(0,$cl)],'trabajador'=>$trabajadoresList[rand(0,$tl)]];
        }

        // MAYO 1-12 — 15 pedidos (recogido + pagado)
        $mayEDates = [
            '2026-05-01','2026-05-02','2026-05-04','2026-05-04','2026-05-05',
            '2026-05-06','2026-05-07','2026-05-07','2026-05-08','2026-05-09',
            '2026-05-10','2026-05-10','2026-05-11','2026-05-12','2026-05-12',
        ];
        foreach ($mayEDates as $date) {
            $dIn  = new DateTime($date);
            $dOut = (clone $dIn)->modify('+' . rand(1, 2) . ' days');
            $prendas = $generatePrendas();
            $pedidos[] = ['estado'=>'recogido','contenido'=>$prendas['contenido'],'fecha_entrada'=>$dIn,'fecha_salida'=>$dOut,'precio'=>$prendas['precio'],'pagado'=>true,'cliente'=>$clientesList[rand(0,$cl)],'trabajador'=>$trabajadoresList[rand(0,$tl)]];
        }

        // MAYO 13-17 — 10 pedidos terminados (listos, no pagados aún)
        $mayTermDates = [
            '2026-05-13','2026-05-13','2026-05-14','2026-05-14','2026-05-15',
            '2026-05-15','2026-05-16','2026-05-17','2026-05-17','2026-05-17',
        ];
        foreach ($mayTermDates as $date) {
            $dOut = new DateTime($date);
            $dIn  = (clone $dOut)->modify('-' . rand(1, 2) . ' days');
            $prendas = $generatePrendas();
            $pedidos[] = ['estado'=>'terminado','contenido'=>$prendas['contenido'],'fecha_entrada'=>$dIn,'fecha_salida'=>$dOut,'precio'=>$prendas['precio'],'pagado'=>false,'cliente'=>$clientesList[rand(0,$cl)],'trabajador'=>$trabajadoresList[rand(0,$tl)]];
        }

        // MAYO 16-18 — 10 pedidos en curso (no terminados, no pagados)
        for ($i = 0; $i < 10; $i++) {
            $dIn = new DateTime('2026-05-' . str_pad(rand(16, 18), 2, '0', STR_PAD_LEFT));
            $prendas = $generatePrendas();
            $pedidos[] = ['estado'=>'no terminado','contenido'=>$prendas['contenido'],'fecha_entrada'=>$dIn,'fecha_salida'=>null,'precio'=>$prendas['precio'],'pagado'=>false,'cliente'=>$clientesList[rand(0,$cl)],'trabajador'=>$trabajadoresList[rand(0,$tl)]];
        }

        foreach ($pedidos as $pedido) {
            $pe = new Pedidos();
            $pe->setEstado($pedido['estado']);
            $pe->setContenido($pedido['contenido']);
            $pe->setFechaEntrada($pedido['fecha_entrada']);
            $pe->setFechaSalida($pedido['fecha_salida'] ?? null);
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
