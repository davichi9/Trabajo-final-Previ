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

        // Crear pedidos
        $pedidos = [
            [
                'estado' => 'no terminado',
                'contenido' => 'Lavado normal: 3 camisas, 2 pantalones',
                'fecha_entrada' => new DateTime('2026-05-04'),
                'fecha_salida' => null,
                'precio' => 20.00,
                'pagado' => false,
                'cliente' => $clientesList[0],
                'trabajador' => null,
            ],
            [
                'estado' => 'terminado',
                'contenido' => 'Lavado delicado: vestido de seda, ropa interior',
                'fecha_entrada' => new DateTime('2026-04-30'),
                'fecha_salida' => new DateTime('2026-05-03'),
                'precio' => 18.50,
                'pagado' => true,
                'cliente' => $clientesList[1],
                'trabajador' => $trabajadoresList[1], // Elena
            ],
            [
                'estado' => 'recogido',
                'contenido' => 'Lavado en seco: abrigo de lana, quitar manchas',
                'fecha_entrada' => new DateTime('2026-04-25'),
                'fecha_salida' => new DateTime('2026-04-28'),
                'precio' => 20.00,
                'pagado' => true,
                'cliente' => $clientesList[2],
                'trabajador' => $trabajadoresList[0], // Carlos
            ],
            [
                'estado' => 'no terminado',
                'contenido' => 'Quitar manchas: chaqueta de cuero, planchado',
                'fecha_entrada' => new DateTime('2026-05-02'),
                'fecha_salida' => null,
                'precio' => 25.00,
                'pagado' => false,
                'cliente' => $clientesList[3],
                'trabajador' => null,
            ],
            [
                'estado' => 'terminado',
                'contenido' => 'Lavado normal: sábanas, toallas, mantel',
                'fecha_entrada' => new DateTime('2026-04-29'),
                'fecha_salida' => new DateTime('2026-05-01'),
                'precio' => 15.00,
                'pagado' => true,
                'cliente' => $clientesList[0],
                'trabajador' => $trabajadoresList[2], // David
            ],
        ];

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
        $output->writeln(' 5 pedidos creados');

        $output->writeln('');
        $output->writeln('<info> Datos de prueba cargados exitosamente!</info>');

        return Command::SUCCESS;
    }
}
