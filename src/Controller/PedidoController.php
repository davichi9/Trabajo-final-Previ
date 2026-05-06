<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PedidoController extends AbstractController
{
    #[Route('/pedido', name: 'app_pedido')]
    public function consultar(Request $request): Response
    {
        return $this->render('pedido/consultar.html.twig');
    }
}
