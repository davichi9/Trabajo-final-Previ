<?php

namespace App\Controller;

use App\Entity\Trabajadores;
use App\Repository\TrabajadoresRepository;
use App\Repository\PedidosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTime;

class TrabajadoresController extends AbstractController
{
    #[Route('/trabajadores', name: 'app_trabajadores_list', methods: ['GET'])]
    public function list(Request $request, TrabajadoresRepository $trabajadoresRepo, PedidosRepository $pedidosRepo): Response
    {
        $session = $request->getSession();
        $trabajador_id = $session->get('trabajador_id');

        // Redirect to login if not authenticated
        if (!$trabajador_id) {
            return $this->redirectToRoute('app_login');
        }

        // Check if user is supervisor
        $trabajador_role = $session->get('trabajador_role');
        if ($trabajador_role !== 'supervisor') {
            return $this->redirectToRoute('app_dashboard');
        }

        // Get search query from request
        $searchTerm = $request->query->get('search', '');

        // Get trabajadores filtered by search term
        $trabajadores = $trabajadoresRepo->searchTrabajadores($searchTerm);

        // Get current month and year
        $now = new DateTime();
        $currentMonth = (int) $now->format('m');
        $currentYear = (int) $now->format('Y');
        
        // Count pedidos per trabajador for current month
        $pedidosCount = [];
        foreach ($trabajadores as $trabajador) {
            $count = $pedidosRepo->countPedidosByTrabajadorAndMonth($trabajador->getId(), $currentYear, $currentMonth);
            $pedidosCount[$trabajador->getId()] = $count;
        }

        return $this->render('trabajadores/list.html.twig', [
            'trabajadores' => $trabajadores,
            'searchTerm' => $searchTerm,
            'trabajador_name' => $session->get('trabajador_name'),
            'trabajador_role' => $session->get('trabajador_role'),
            'pedidosCount' => $pedidosCount,
        ]);
    }

    #[Route('/trabajadores/crear', name: 'app_trabajadores_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $trabajador_id = $session->get('trabajador_id');

        // Redirect to login if not authenticated
        if (!$trabajador_id) {
            return $this->redirectToRoute('app_login');
        }

        // Check if user is supervisor
        $trabajador_role = $session->get('trabajador_role');
        if ($trabajador_role !== 'supervisor') {
            return $this->redirectToRoute('app_dashboard');
        }

        // Handle form submission
        if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');
            $apellidos = $request->request->get('apellidos');
            $email = $request->request->get('email');
            $telefono = $request->request->get('telefono');
            $rol = $request->request->get('rol');
            $password = $request->request->get('password');

            // Validate required fields
            if (!$nombre || !$apellidos || !$email || !$rol || !$password) {
                $this->addFlash('error', 'Por favor completa todos los campos requeridos.');
                return $this->render('trabajadores/create.html.twig', [
                    'trabajador_name' => $session->get('trabajador_name'),
                    'trabajador_role' => $session->get('trabajador_role'),
                ]);
            }

            // Create new Trabajador entity
            $trabajador = new Trabajadores();
            $trabajador->setNombre($nombre);
            $trabajador->setApellidos($apellidos);
            $trabajador->setEmail($email);
            $trabajador->setTelefonoNumero($telefono ?? '');
            $trabajador->setRol($rol);
            $trabajador->setContraseña(password_hash($password, PASSWORD_BCRYPT));
            $trabajador->setActivo(true);

            // Save to database
            $entityManager->persist($trabajador);
            $entityManager->flush();

            $this->addFlash('success', 'Trabajador creado exitosamente.');
            return $this->redirectToRoute('app_trabajadores_list');
        }

        return $this->render('trabajadores/create.html.twig', [
            'trabajador_name' => $session->get('trabajador_name'),
            'trabajador_role' => $session->get('trabajador_role'),
        ]);
    }

    #[Route('/trabajadores/{id}/editar', name: 'app_trabajadores_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, TrabajadoresRepository $trabajadoresRepo, EntityManagerInterface $entityManager): Response
    {
        $session = $request->getSession();
        $trabajador_id = $session->get('trabajador_id');

        // Redirect to login if not authenticated
        if (!$trabajador_id) {
            return $this->redirectToRoute('app_login');
        }

        // Check if user is supervisor
        $trabajador_role = $session->get('trabajador_role');
        if ($trabajador_role !== 'supervisor') {
            return $this->redirectToRoute('app_dashboard');
        }

        // Get the trabajador to edit
        $trabajador = $trabajadoresRepo->find($id);
        if (!$trabajador) {
            $this->addFlash('error', 'Trabajador no encontrado.');
            return $this->redirectToRoute('app_trabajadores_list');
        }

        // Handle form submission
        if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');
            $apellidos = $request->request->get('apellidos');
            $email = $request->request->get('email');
            $telefono = $request->request->get('telefono');
            $rol = $request->request->get('rol');
            $password = $request->request->get('password');
            $activo = $request->request->get('activo');

            // Validate required fields
            if (!$nombre || !$apellidos || !$email || !$rol || $activo === '') {
                $this->addFlash('error', 'Por favor completa todos los campos requeridos.');
                return $this->render('trabajadores/edit.html.twig', [
                    'trabajador' => $trabajador,
                    'trabajador_name' => $session->get('trabajador_name'),
                    'trabajador_role' => $session->get('trabajador_role'),
                ]);
            }

            // Update Trabajador entity
            $trabajador->setNombre($nombre);
            $trabajador->setApellidos($apellidos);
            $trabajador->setEmail($email);
            $trabajador->setTelefonoNumero($telefono ?? '');
            $trabajador->setRol($rol);
            $trabajador->setActivo((bool) $activo);
            
            // Only update password if provided
            if ($password) {
                $trabajador->setContraseña(password_hash($password, PASSWORD_BCRYPT));
            }

            // Save to database
            $entityManager->flush();

            $this->addFlash('success', 'Trabajador actualizado exitosamente.');
            return $this->redirectToRoute('app_trabajadores_list');
        }

        return $this->render('trabajadores/edit.html.twig', [
            'trabajador' => $trabajador,
            'trabajador_name' => $session->get('trabajador_name'),
            'trabajador_role' => $session->get('trabajador_role'),
        ]);
    }
}
