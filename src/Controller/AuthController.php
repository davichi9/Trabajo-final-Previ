<?php

namespace App\Controller;

use App\Repository\TrabajadoresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request, TrabajadoresRepository $trabajadoresRepo): Response
    {
        $session = $request->getSession();
        
        // If already logged in, redirect to dashboard
        if ($session->get('trabajador_id')) {
            return $this->redirectToRoute('app_dashboard');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            // Find trabajador by email
            $trabajador = $trabajadoresRepo->findOneBy(['email' => $email]);

            // Verify password
            if ($trabajador && password_verify($password, $trabajador->getContraseña())) {
                // Login successful - set session
                $session = $request->getSession();
                $session->set('trabajador_id', $trabajador->getId());
                $session->set('trabajador_name', $trabajador->getNombre());
                $session->set('trabajador_role', $trabajador->getRol());

                return $this->redirectToRoute('app_dashboard');
            } else {
                $error = 'Email o contraseña inválidos';
            }
        }

        return $this->render('auth/login.html.twig', [
            'error' => $error,
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function dashboard(Request $request): Response
    {
        $session = $request->getSession();
        $trabajador_id = $session->get('trabajador_id');

        // Redirect to login if not authenticated
        if (!$trabajador_id) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('dashboard/index.html.twig', [
            'trabajador_name' => $session->get('trabajador_name'),
            'trabajador_role' => $session->get('trabajador_role'),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        $session->invalidate();

        return $this->redirectToRoute('app_home');
    }
}
