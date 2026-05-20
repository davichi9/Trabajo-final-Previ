<?php

namespace App\Controller;

use App\Entity\Pedidos;
use App\Repository\ClientesRepository;
use App\Repository\PedidosRepository;
use App\Repository\PrendasRepository;
use App\Repository\TrabajadoresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PedidosDashboardController extends AbstractController
{
    private function requireLogin(Request $request): ?Response
    {
        if (!$request->getSession()->get('trabajador_id')) {
            return $this->redirectToRoute('app_login');
        }
        return null;
    }

    #[Route('/dashboard/pedidos', name: 'app_pedidos_list', methods: ['GET'])]
    public function index(Request $request, PedidosRepository $pedidosRepo): Response
    {
        if ($r = $this->requireLogin($request)) return $r;

        $session = $request->getSession();
        $searchTerm = $request->query->get('search', '');
        $estados = $request->query->all('estado');
        $pagados = $request->query->all('pagado');
        $perPage = 10;
        $page = max(1, (int) $request->query->get('page', 1));

        $total = $pedidosRepo->countPedidos($searchTerm, $estados, $pagados);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);

        return $this->render('pedidos/index.html.twig', [
            'pedidos' => $pedidosRepo->searchPedidos($searchTerm, $estados, $pagados, $page, $perPage),
            'searchTerm' => $searchTerm,
            'estadosFiltro' => $estados,
            'pagadosFiltro' => $pagados,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'trabajador_name' => $session->get('trabajador_name'),
            'trabajador_role' => $session->get('trabajador_role'),
        ]);
    }

    #[Route('/dashboard/pedidos/crear', name: 'app_pedido_create', methods: ['GET'])]
    public function create(Request $request, ClientesRepository $clientesRepo, TrabajadoresRepository $trabajadoresRepo, PrendasRepository $prendasRepo): Response
    {
        if ($r = $this->requireLogin($request)) return $r;
        $s = $request->getSession();
        return $this->render('pedidos/detalle.html.twig', [
            'pedido' => null,
            'clientes' => $clientesRepo->findBy([], ['nombre' => 'ASC']),
            'trabajadores' => $trabajadoresRepo->findBy([], ['nombre' => 'ASC']),
            'prendas' => $prendasRepo->findBy([], ['nombre' => 'ASC']),
            'trabajador_name' => $s->get('trabajador_name'),
            'trabajador_role' => $s->get('trabajador_role'),
        ]);
    }

    #[Route('/dashboard/pedidos/crear', name: 'app_pedido_store', methods: ['POST'])]
    public function store(Request $request, ClientesRepository $clientesRepo, TrabajadoresRepository $trabajadoresRepo, EntityManagerInterface $em): Response
    {
        if ($r = $this->requireLogin($request)) return $r;

        $cliente = $clientesRepo->find((int) $request->request->get('cliente_id'));
        if (!$cliente) {
            $this->addFlash('error', 'Debe seleccionar un cliente válido.');
            return $this->redirectToRoute('app_pedido_create');
        }

        $fechaEntrada = \DateTime::createFromFormat('d/m/Y H:i', $request->request->get('fecha_entrada'));
        $fechaSalidaStr = $request->request->get('fecha_salida');
        $fechaSalida = $fechaSalidaStr ? \DateTime::createFromFormat('d/m/Y H:i', $fechaSalidaStr) : null;
        if (!$fechaEntrada || ($fechaSalidaStr && !$fechaSalida)) {
            $this->addFlash('error', 'Fecha inválida. Usa dd/mm/aaaa hh:mm');
            return $this->redirectToRoute('app_pedido_create');
        }

        $trabajadorId = $request->request->get('trabajador_id');
        $pedido = (new Pedidos())
            ->setCliente($cliente)
            ->setEstado($request->request->get('estado') ?: 'no terminado')
            ->setContenido($request->request->get('contenido') ?: '')
            ->setFechaEntrada($fechaEntrada)
            ->setFechaSalida($fechaSalida)
            ->setPrecio((float) str_replace(',', '.', $request->request->get('precio') ?: 0))
            ->setPagado((bool) $request->request->get('pagado'))
            ->setTrabajador($trabajadorId ? $trabajadoresRepo->find((int) $trabajadorId) : null)
            ->setObservaciones($request->request->get('observaciones') ?: null);

        $em->persist($pedido);
        $em->flush();

        $this->addFlash('success', 'Pedido #' . $pedido->getId() . ' creado.');
        return $this->redirectToRoute('app_pedido_detail', ['id' => $pedido->getId()]);
    }

    #[Route('/dashboard/pedidos/{id}', name: 'app_pedido_detail', methods: ['GET'])]
    public function detail(int $id, Request $request, PedidosRepository $pedidosRepo, ClientesRepository $clientesRepo, TrabajadoresRepository $trabajadoresRepo, PrendasRepository $prendasRepo): Response
    {
        if ($r = $this->requireLogin($request)) return $r;

        $pedido = $pedidosRepo->find($id);
        if (!$pedido) {
            throw $this->createNotFoundException('Pedido no encontrado');
        }

        $session = $request->getSession();
        return $this->render('pedidos/detalle.html.twig', [
            'pedido' => $pedido,
            'clientes' => $clientesRepo->findBy([], ['nombre' => 'ASC']),
            'trabajadores' => $trabajadoresRepo->findBy([], ['nombre' => 'ASC']),
            'prendas' => $prendasRepo->findBy([], ['nombre' => 'ASC']),
            'trabajador_name' => $session->get('trabajador_name'),
            'trabajador_role' => $session->get('trabajador_role'),
        ]);
    }

    #[Route('/dashboard/pedidos/{id}/editar', name: 'app_pedido_edit', methods: ['POST'])]
    public function edit(int $id, Request $request, PedidosRepository $pedidosRepo, ClientesRepository $clientesRepo, TrabajadoresRepository $trabajadoresRepo, EntityManagerInterface $em): Response
    {
        if ($r = $this->requireLogin($request)) return $r;

        $pedido = $pedidosRepo->find($id);
        if (!$pedido) {
            throw $this->createNotFoundException('Pedido no encontrado');
        }

        $pedido->setEstado($request->request->get('estado'));
        $pedido->setContenido($request->request->get('contenido'));

        $fechaEntrada = \DateTime::createFromFormat('d/m/Y H:i', $request->request->get('fecha_entrada'));
        $fechaSalidaStr = $request->request->get('fecha_salida');
        $fechaSalida = $fechaSalidaStr ? \DateTime::createFromFormat('d/m/Y H:i', $fechaSalidaStr) : null;

        if (!$fechaEntrada || ($fechaSalidaStr && !$fechaSalida)) {
            $this->addFlash('error', 'Fecha inválida. Usa dd/mm/aaaa hh:mm');
            return $this->redirectToRoute('app_pedido_detail', ['id' => $id]);
        }

        $pedido->setFechaEntrada($fechaEntrada);
        $pedido->setFechaSalida($fechaSalida);
        $pedido->setPrecio((float) str_replace(',', '.', $request->request->get('precio')));
        $pedido->setPagado((bool) $request->request->get('pagado'));

        $cliente = $clientesRepo->find((int) $request->request->get('cliente_id'));
        if ($cliente) {
            $pedido->setCliente($cliente);
        }

        $trabajadorId = $request->request->get('trabajador_id');
        $pedido->setTrabajador($trabajadorId ? $trabajadoresRepo->find((int) $trabajadorId) : null);
        $pedido->setObservaciones($request->request->get('observaciones') ?: null);

        $em->flush();
        $this->addFlash('success', 'Pedido actualizado.');

        return $this->redirectToRoute('app_pedido_detail', ['id' => $id]);
    }
}
