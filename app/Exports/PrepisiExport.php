<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PrepisiExport implements WithMultipleSheets
{
    protected $summaryData;
    protected $studentsByYear;

    public function __construct($summaryData, $studentsByYear)
    {
        $this->summaryData = $summaryData;
        $this->studentsByYear = $studentsByYear;
    }

    public function sheets(): array
    {
        $sheets = [];

        /* ===== SUMARNO ===== */
        $sheets[] = new class($this->summaryData) implements FromCollection, WithHeadings, WithEvents {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return [
                    'Godina', 'Fakultet', 'Ukupno', 'Muško', 'Žensko', '%Muško', '%Žensko'
                ];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $sheet->setTitle('Sumarno');

                        // Naslov
                        $sheet->insertNewRowBefore(1, 2);
                        $sheet->setCellValue('A1', 'Izvještaj o prepisima');
                        $sheet->mergeCells('A1:G1');
                        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                        $sheet->getStyle('A1')->getAlignment()
                              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        // Auto-size kolona
                        foreach (range('A', 'G') as $col) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }

                        // Zaglavlje sa bojom i debelim okvirima
                        $sheet->getStyle('A3:G3')->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FF4F81BD'],
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['argb' => 'FF000000'],
                                ],
                            ],
                        ]);

                        // Deblji okvir oko cijele tabele
                        $lastRow = 3 + count($this->data);
                        $sheet->getStyle('A3:G' . $lastRow)->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['argb' => 'FF000000'],
                                ],
                            ],
                        ]);
                    }
                ];
            }
        };

        /* ===== STUDENTI PO GODINAMA ===== */
        foreach ($this->studentsByYear as $year => $students) {

            $sheets[] = new class($students, $year) implements FromCollection, WithHeadings, WithEvents {
                protected $students;
                protected $year;

                public function __construct($students, $year)
                {
                    $this->students = $students;
                    $this->year = $year;
                }

                public function collection()
                {
                    return collect($this->students);
                }

                public function headings(): array
                {
                    return [
                        'Ime', 'Prezime', 'Pol',
                        'Nivo studija', 'Fakultet',
                        'Nivo studija', 'Fakultet',
                        'Univerzitet', 'Datum finalizacije'
                    ];
                }

                public function registerEvents(): array
                {
                    return [
                        AfterSheet::class => function (AfterSheet $event) {
                            $sheet = $event->sheet->getDelegate();
                            $sheet->setTitle('Studenti ' . $this->year);

                            $sheet->insertNewRowBefore(1, 2);
                            $sheet->setCellValue('A1', 'Izvještaj za studente - Godina ' . $this->year);
                            $sheet->mergeCells('A1:G1');
                            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                            $sheet->getStyle('A1')->getAlignment()
                                  ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                            // Auto-size kolona
                            foreach (range('A', 'G') as $col) {
                                $sheet->getColumnDimension($col)->setAutoSize(true);
                            }

                            // Zaglavlje sa bojom i debelim okvirima
                            $sheet->getStyle('A3:G3')->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['argb' => 'FF4F81BD'],
                                ],
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['argb' => 'FF000000'],
                                    ],
                                ],
                            ]);

                            // Deblji okvir oko cijele tabele
                            $lastRow = 3 + count($this->students);
                            $sheet->getStyle('A3:G' . $lastRow)->applyFromArray([
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['argb' => 'FF000000'],
                                    ],
                                ],
                            ]);
                        }
                    ];
                }
            };
        }

        return $sheets;
    }
}