<?php

namespace App\Controller;

use App\Repository\PedidosRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PedidosDashboardController extends AbstractController
{
    #[Route('/dashboard/pedidos', name: 'app_pedidos_list', methods: ['GET'])]
    public function index(Request $request, PedidosRepository $pedidosRepo): Response
    {
        $session = $request->getSession();

        if (!$session->get('trabajador_id')) {
            return $this->redirectToRoute('app_login');
        }

        $searchTerm = $request->query->get('search', '');
        $estados = $request->query->all('estado');
        $pagados = $request->query->all('pagado');

        return $this->render('pedidos/index.html.twig', [
            'pedidos' => $pedidosRepo->searchPedidos($searchTerm, $estados, $pagados),
            'searchTerm' => $searchTerm,
            'estadosFiltro' => $estados,
            'pagadosFiltro' => $pagados,
            'trabajador_name' => $session->get('trabajador_name'),
            'trabajador_role' => $session->get('trabajador_role'),
        ]);
    }
}
