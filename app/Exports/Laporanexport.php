<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

// Butuh paket maatwebsite/excel. Kelas ini baru dimuat saat ekspor Excel dipakai.
class LaporanExport implements FromView, ShouldAutoSize
{
    public function __construct(private string $view, private array $data)
    {
    }

    public function view(): View
    {
        return view($this->view, $this->data);
    }
}