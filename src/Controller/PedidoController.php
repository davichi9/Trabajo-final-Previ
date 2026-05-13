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

        if (empty($numeroPedido) || empty($telefono)) {
            $this->addFlash('error', 'Por favor, rellena todos los campos antes de continuar.');
            return $this->redirectToRoute('app_pedido');
        }

        try {
            $pedido = $pedidosRepository->findOneBy(['id' => $numeroPedido]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error de servidor. Inténtalo de nuevo más tarde.');
            return $this->redirectToRoute('app_pedido');
        }

        if (!$pedido) {
            $this->addFlash('error', 'No existe ningún pedido con ese número.');
            return $this->redirectToRoute('app_pedido');
        }

        if ($pedido->getCliente()->getTelefonoNumero() !== $telefono) {
            $this->addFlash('error', 'El número de teléfono no coincide con el pedido.');
            return $this->redirectToRoute('app_pedido');
        }

        $request->getSession()->set('pedido_autorizado', $pedido->getId());
        return $this->redirectToRoute('app_pedido_resultado_show', ['id' => $pedido->getId()]);
    }

    #[Route('/pedido/resultado/{id}', name: 'app_pedido_resultado_show', methods: ['GET'])]
    public function resultadoShow(int $id, Request $request, PedidosRepository $pedidosRepository): Response
    {
        $pedido = $pedidosRepository->find($id);

        if (!$pedido || $request->getSession()->get('pedido_autorizado') !== $pedido->getId()) {
            return $this->redirectToRoute('app_pedido');
        }

        $request->getSession()->remove('pedido_autorizado');

        return $this->render('pedido/resultado.html.twig', [
            'pedido' => $pedido,
        ]);
    }
}
