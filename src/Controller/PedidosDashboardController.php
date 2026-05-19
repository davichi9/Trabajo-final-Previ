<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PedidosDashboardController extends AbstractController
{
    #[Route('/dashboard/pedidos', name: 'app_pedidos_list', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $session = $request->getSession();

        if (!$session->get('trabajador_id')) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('pedidos/index.html.twig', [
            'trabajador_name' => $session->get('trabajador_name'),
            'trabajador_role' => $session->get('trabajador_role'),
        ]);
    }
}
