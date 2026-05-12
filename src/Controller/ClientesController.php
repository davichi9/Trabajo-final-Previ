<?php

namespace App\Controller;

use App\Repository\ClientesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ClientesController extends AbstractController
{
    #[Route('/clientes', name: 'app_clientes_list', methods: ['GET'])]
    public function list(Request $request, ClientesRepository $clientesRepo): Response
    {
        $session = $request->getSession();
        $trabajador_id = $session->get('trabajador_id');

        // Redirect to login if not authenticated
        if (!$trabajador_id) {
            return $this->redirectToRoute('app_login');
        }

        $clientes = $clientesRepo->findAll();

        return $this->render('clientes/list.html.twig', [
            'clientes' => $clientes,
            'trabajador_name' => $session->get('trabajador_name'),
            'trabajador_role' => $session->get('trabajador_role'),
        ]);
    }
}
