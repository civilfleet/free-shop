<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;

class MetricsAggregator
{
    public ?string $date_start = null;
    public ?string $date_end = null;

    public function __construct(?string $date_start, ?string $date_end)
    {
        $this->date_start = $date_start;
        $this->date_end = $date_end;
    }

    public function customersRegistered()
    {
        return Customer::registeredInDateRange($this->date_start, $this->date_end)
            ->count();
    }

    public function ordersCompleted()
    {
        return Order::completedInDateRange($this->date_start, $this->date_end)
            ->count();
    }

    public function customersWithCompletedOrders()
    {
        return Customer::whereHas('orders', fn ($qry) => $qry->completedInDateRange($this->date_start, $this->date_end))
            ->count();
    }

    public function totalProductsHandedOut()
    {
        return Order::completedInDateRange($this->date_start, $this->date_end)
            ->get()
            ->map(fn ($order) => $order->numberOfProducts())
            ->sum();
    }

    public function productsHandedOut()
    {
        return Product::whereHas('orders', fn ($qry) => $qry->completedInDateRange($this->date_start, $this->date_end))
            ->get()
            ->map(fn ($product) => [
                'name' => $product->name,
                'quantity' => $product->orders()->completedInDateRange($this->date_start, $this->date_end)->sum('quantity')
            ])
            ->sortByDesc('quantity');
    }

    public function averageOrderDuration()
    {
        return Order::completedInDateRange($this->date_start, $this->date_end)
            ->select('completed_at', 'created_at')
            ->get()
            ->map(fn ($order) => $order->completed_at->diffInDays($order->created_at))
            ->avg();
    }
}
