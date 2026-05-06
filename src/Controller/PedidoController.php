<?php

namespace App\Controller;

use App\Repository\PedidosRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PedidoController extends AbstractController
{
    #[Route('/pedido', name: 'app_pedido', methods: ['GET'])]
    public function consultar(): Response
    {
        return $this->render('pedido/consultar.html.twig');
    }

    #[Route('/pedido/resultado', name: 'app_pedido_resultado', methods: ['POST'])]
    public function resultado(Request $request, PedidosRepository $pedidosRepository): Response
    {
        $numeroPedido = $request->request->get('numeroPedido');
        $telefono = $request->request->get('telefono');

        $pedido = $pedidosRepository->findOneBy(['id' => $numeroPedido]);

        if (!$pedido || $pedido->getCliente()->getTelefonoNumero() !== $telefono) {
            $this->addFlash('error', 'No se encontró ningún pedido con ese número y teléfono.');
            return $this->redirectToRoute('app_pedido');
        }

        return $this->redirectToRoute('app_pedido_resultado_show', ['id' => $pedido->getId()]);
    }

    #[Route('/pedido/resultado/{id}', name: 'app_pedido_resultado_show', methods: ['GET'])]
    public function resultadoShow(int $id, PedidosRepository $pedidosRepository): Response
    {
        $pedido = $pedidosRepository->find($id);

        if (!$pedido) {
            return $this->redirectToRoute('app_pedido');
        }

        return $this->render('pedido/resultado.html.twig', [
            'pedido' => $pedido,
        ]);
    }
}
