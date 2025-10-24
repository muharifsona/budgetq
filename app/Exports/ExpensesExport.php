<?php

namespace App\Exports;

use App\Models\Expense;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExpensesExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithColumnFormatting
{
    public function __construct(
        protected int $userId,
        protected int $year,
        protected int $month,
        protected ?int $categoryId = null
    ) {}

    public function query()
    {
        $start = now()->setDate($this->year, $this->month, 1)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        $q = Expense::query()->with('category')
            ->where('user_id', $this->userId)
            ->whereBetween('date', [$start,$end]);

        if ($this->categoryId) $q->where('category_id',$this->categoryId);

        return $q->orderBy('date','desc');
    }

    public function headings(): array
    {
        return ['Tanggal', 'Kategori', 'Catatan', 'Jumlah'];
    }

    public function map($e): array
    {
        return [
            $e->date?->format('Y-m-d'),
            $e->category?->name ?? '-',
            $e->note ?? '',
            (float)$e->amount,
        ];
    }

    public function columnFormats(): array
    {
        return ['D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1];
    }
}
