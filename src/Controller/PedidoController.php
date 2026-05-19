<?php

namespace App\Controller;

use App\Repository\PedidosRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
            $this->addFlash('error', 'Rellena todos los campos.');
            return $this->redirectToRoute('app_pedido');
        }

        $pedido = $pedidosRepository->findOneBy(['id' => $numeroPedido]);

        if (!$pedido) {
            $this->addFlash('error', 'Pedido no encontrado.');
            return $this->redirectToRoute('app_pedido');
        }

        if ($pedido->getCliente()->getTelefonoNumero() !== $telefono) {
            $this->addFlash('error', 'Teléfono incorrecto.');
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

    #[Route('/pedido/resultado/{id}/reporte', name: 'app_pedido_reporte', methods: ['POST'])]
    public function reporte(int $id, Request $request, PedidosRepository $pedidosRepository, MailerInterface $mailer): Response
    {
        $pedido = $pedidosRepository->find($id);

        if (!$pedido) {
            return $this->redirectToRoute('app_pedido');
        }

        if (!$this->isCsrfTokenValid('reporte', $request->request->get('_csrf_token'))) {
            $this->addFlash('reporte_error', 'Token inválido.');
            return $this->redirectToRoute('app_pedido_resultado_show', ['id' => $id]);
        }

        $mensaje = trim($request->request->get('mensaje', ''));

        if (empty($mensaje)) {
            $this->addFlash('reporte_error', 'El mensaje no puede estar vacío.');
            return $this->redirectToRoute('app_pedido_resultado_show', ['id' => $id]);
        }

        try {
            $mailer->send((new Email())
                ->from('tintoreriaprueba@gmail.com')
                ->to('tintoreriaprueba@gmail.com')
                ->subject('Reporte – Pedido #' . $pedido->getId())
                ->text("Pedido: #{$pedido->getId()}\nCliente: {$pedido->getCliente()->getNombre()} {$pedido->getCliente()->getApellidos()}\nTeléfono: {$pedido->getCliente()->getTelefonoNumero()}\n\n{$mensaje}")
            );
            $this->addFlash('reporte_ok', 'Reporte enviado.');
        } catch (\Exception $e) {
            $this->addFlash('reporte_error', 'Error al enviar el reporte.');
        }

        $request->getSession()->set('pedido_autorizado', $pedido->getId());
        return $this->redirectToRoute('app_pedido_resultado_show', ['id' => $id]);
    }
}
