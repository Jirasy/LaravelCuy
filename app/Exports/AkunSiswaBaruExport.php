<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Rekap akun siswa hasil import: satu baris per siswa dengan kolom Nama, Kelas, Email login, Password sementara.
 * Semua sel disimpan sebagai teks (StringValueBinder) supaya password acak yang diawali "=", "+", "-" atau "@"
 * tidak dibaca Excel sebagai rumus.
 */
class AkunSiswaBaruExport extends StringValueBinder implements FromArray, ShouldAutoSize, WithCustomValueBinder, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  list<array{0: string, 1: string, 2: string, 3: string}>  $baris  [nama, kelas, email login, password sementara]
     */
    public function __construct(private array $baris) {}

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: string}>
     */
    public function array(): array
    {
        return $this->baris;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return ['Nama', 'Kelas', 'Email login', 'Password sementara'];
    }

    public function title(): string
    {
        return 'Akun Siswa Baru';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        $terakhir = count($this->baris) + 1;

        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:D{$terakhir}");

        $garis = $sheet->getStyle("A1:D{$terakhir}")->getBorders()->getAllBorders();
        $garis->setBorderStyle(Border::BORDER_THIN);
        $garis->getColor()->setRGB('D1D5DB');

        // Font monospasi di kolom password supaya karakter mirip (O/0, l/1) mudah dibedakan
        $sheet->getStyle("D2:D{$terakhir}")->getFont()->setName('Consolas');

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            ],
        ];
    }
}
