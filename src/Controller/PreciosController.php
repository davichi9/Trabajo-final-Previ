<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PreciosController extends AbstractController
{
    #[Route('/precios', name: 'app_precios')]
    public function index(): Response
    {
        $prendas = [
            ['nombre' => 'Americana',  'precio' => 9.50,  'desde' => false, 'unidad' => 'ud'],
            ['nombre' => 'Pantalón',   'precio' => 8.50,  'desde' => false, 'unidad' => 'ud'],
            ['nombre' => 'Corbata',    'precio' => 6.00,  'desde' => false, 'unidad' => 'ud'],
            ['nombre' => 'Vestido',    'precio' => 15.00, 'desde' => true,  'unidad' => 'ud'],
            ['nombre' => 'Abrigo',     'precio' => 17.00, 'desde' => true,  'unidad' => 'ud'],
            ['nombre' => 'Cazadora',   'precio' => 15.00, 'desde' => false, 'unidad' => 'ud'],
            ['nombre' => 'Chaquetón',  'precio' => 17.00, 'desde' => false, 'unidad' => 'ud'],
            ['nombre' => 'Gabardina',  'precio' => 19.00, 'desde' => false, 'unidad' => 'ud'],
            ['nombre' => 'Blusa',      'precio' => 10.00, 'desde' => false, 'unidad' => 'ud'],
            ['nombre' => 'Falda',      'precio' => 9.00,  'desde' => true,  'unidad' => 'ud'],
            ['nombre' => 'Manta',      'precio' => 24.00, 'desde' => true,  'unidad' => 'ud'],
            ['nombre' => 'Edredón',    'precio' => 28.00, 'desde' => true,  'unidad' => 'ud'],
            ['nombre' => 'Alfombra',   'precio' => 12.00, 'desde' => true,  'unidad' => 'm²'],
        ];

        return $this->render('precios/index.html.twig', [
            'prendas' => $prendas,
        ]);
    }
}
