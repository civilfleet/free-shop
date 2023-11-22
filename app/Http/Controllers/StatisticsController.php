<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Services\MetricsAggregator;
use Carbon\Carbon;

class StatisticsController extends BaseController
{
    public function get(Request $request)
    {	
        return $this->sendResponse($this->monthlyStats(now()->startOfMonth()), 'OK');
    }
	
	private function monthlyStats(Carbon $monthStart): array
    {
        $aggregator = new MetricsAggregator($monthStart, $monthStart->clone()->endOfMonth());

        return [
            'month_start' => $monthStart,
            'ordersCompleted' => $aggregator->ordersCompleted(),
            'customersWithCompletedOrders' => $aggregator->customersWithCompletedOrders(),
            'totalProductsHandedOut' => $aggregator->totalProductsHandedOut(),
            'productsHandedOut' => $aggregator->productsHandedOut(),
            'averageOrderDuration' => $aggregator->averageOrderDuration(),
        ];
    }
}