<?php

namespace App\Exports\Sheets;

use App\Exports\DefaultWorksheetStyles;
use App\Models\Order;
use Carbon\Carbon;
use donatj\UserAgent\UserAgentParser;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Throwable;

class OrdersSheet implements FromQuery, WithMapping, WithHeadings, WithColumnFormatting, ShouldAutoSize, WithStyles
{
    use DefaultWorksheetStyles;

    protected $worksheetTitle = 'Orders';

    public function __construct(
        private ?Carbon $startDate = null
    ) {
    }

    public function query()
    {
        return Order::orderBy('created_at', 'desc')
            ->when($this->startDate !== null, fn ($qry) => $qry->whereDate('created_at', '>=', $this->startDate))
            ->with('customer', 'products');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Status',
            'Customer ID',
            'Customer',
            'IP Address',
            'Browser',
            'Operating System',
            'Order',
            'Remarks',
            'Registered',
            'Updated',
        ];
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->status,
            isset($order->customer) ? $order->customer->id : null,
            isset($order->customer) ? ($order->customer->name . ', ' . $order->customer->id_number . ', ' . $this->mapPhone($order->customer->phone)) : null,
            $order->ip_address,
            $this->mapBrowser($order->user_agent),
            $this->mapOS($order->user_agent),
            $order->products
                ->sortBy('name')
                ->map(fn ($product) => sprintf('%dx %s', $product->pivot->quantity, $product->name))
                ->join(', '),
            $order->remarks,
            $this->mapDateTime($order->created_at),
            $this->mapDateTime($order->updated_at),
        ];
    }

    private function mapPhone($value)
    {
        try {
            return phone($value)->formatInternational();
        } catch (Throwable $ignored) {
            return ' ' . $value;
        }
    }

    private function mapBrowser($value)
    {
        $userAgent = (new UserAgentParser())->parse($value);

        return $userAgent->browser() . ' ' . $userAgent->browserVersion();
    }

    private function mapOS($value)
    {
        $userAgent = (new UserAgentParser())->parse($value);

        return $userAgent->platform();
    }

    private function mapDateTime($value)
    {
        return $value !== null
            ? Date::dateTimeToExcel($value->toUserTimezone())
            : null;
    }

    public function columnFormats(): array
    {
        return [
            'J' => NumberFormat::FORMAT_DATE_YYYYMMDD . ' ' . NumberFormat::FORMAT_DATE_TIME3,
            'K' => NumberFormat::FORMAT_DATE_YYYYMMDD . ' ' . NumberFormat::FORMAT_DATE_TIME3,
        ];
    }
}
