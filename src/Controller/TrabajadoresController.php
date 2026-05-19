<?php

namespace App\Controller;

use App\Repository\TrabajadoresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TrabajadoresController extends AbstractController
{
    #[Route('/trabajadores', name: 'app_trabajadores_list', methods: ['GET'])]
    public function list(Request $request, TrabajadoresRepository $trabajadoresRepo): Response
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

        return $this->render('trabajadores/list.html.twig', [
            'trabajadores' => $trabajadores,
            'searchTerm' => $searchTerm,
            'trabajador_name' => $session->get('trabajador_name'),
            'trabajador_role' => $session->get('trabajador_role'),
        ]);
    }
}
