<?php

namespace App\Controller;

use App\Repository\PedidosRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReportesController extends AbstractController
{
    #[Route('/reportes', name: 'app_reportes')]
    public function index(Request $request, PedidosRepository $pedidosRepository): Response
    {
        $now = new \DateTime();
        $year = (int)$request->query->get('year', $now->format('Y'));
        $month = (int)$request->query->get('month', $now->format('m'));

        // Validate month
        if ($month < 1 || $month > 12) {
            $month = (int)$now->format('m');
        }

        $weeklyData = $pedidosRepository->getWeeklyRevenueByMonth($year, $month);

        // Format data for Chart.js
        $chartLabels = [];
        $chartData = [];

        foreach ($weeklyData as $data) {
            $weekStart = $data['weekStart'];
            $weekEnd = $data['weekEnd'];
            
            $label = $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m');
            $chartLabels[] = $label;
            $chartData[] = round((float)$data['totalRevenue'], 2);
        }

        // Calculate dynamic min/max for Y-axis (50% margin)
        $yMax = 0;
        $yMin = 0;
        
        if (!empty($chartData)) {
            $maxRevenue = max($chartData);
            $minRevenue = min($chartData);
            
            $yMax = ceil($maxRevenue * 1.5 * 100) / 100;
            $yMin = max(0, floor($minRevenue * 0.5 * 100) / 100);
        }

        return $this->render('reportes/index.html.twig', [
            'year' => $year,
            'month' => $month,
            'chartLabels' => json_encode($chartLabels),
            'chartData' => json_encode($chartData),
            'yMin' => $yMin,
            'yMax' => $yMax,
            'currentMonth' => $now->format('Y-m'),
        ]);
    }
}
