<?php

namespace App\Service;

use App\Repository\ReportRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use TCPDF;

class ExportGeneratorService
{
    private string $exportDir;

    public function __construct(
        private readonly ReportRepository $reportRepository,
        private readonly ReportGeneratorService $reportGeneratorService,
        private readonly LoggerInterface $logger,
        string $projectDir
    ) {
        $this->exportDir = $projectDir . '/var/exports';

        // Ensure the export directory exists
        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->exportDir)) {
            $filesystem->mkdir($this->exportDir);
        }
    }

    public function generateExport(
        string $type,
        string $format,
        string $filename,
        ?string $reportId = null,
        ?array $filters = null
    ): ?string {
        // Get the data to export
        $data = $this->getDataToExport($type, $reportId, $filters);

        if (!$data) {
            $this->logger->error(sprintf('Failed to get data for export type "%s"', $type));
            return null;
        }

        // Generate the export file
        $filePath = $this->exportDir . '/' . $filename;

        switch (strtolower($format)) {
            case 'pdf':
                $success = $this->generatePdf($data, $filePath);
                break;
            case 'xlsx':
                $success = $this->generateExcel($data, $filePath);
                break;
            case 'csv':
                $success = $this->generateCsv($data, $filePath);
                break;
            default:
                $this->logger->error(sprintf('Unsupported export format: %s', $format));
                return null;
        }

        if (!$success) {
            $this->logger->error(sprintf('Failed to generate export file: %s', $filePath));
            return null;
        }

        return $filePath;
    }

    private function getDataToExport(string $type, ?string $reportId, ?array $filters): ?array
    {
        if ($reportId) {
            // If a report ID is provided, use the report data
            $report = $this->reportRepository->find($reportId);
            if (!$report) {
                $this->logger->error(sprintf('Report with ID "%s" not found', $reportId));
                return null;
            }

            // If the report doesn't have a result, generate it
            if (!$report->getResult()) {
                $report = $this->reportGeneratorService->generateReport($reportId);
                if (!$report || !$report->getResult()) {
                    $this->logger->error(sprintf('Failed to generate report with ID "%s"', $reportId));
                    return null;
                }
            }

            return $report->getResult();
        }

        // Otherwise, generate data based on the type and filters
        // This is a placeholder implementation
        return [
            'type' => $type,
            'filters' => $filters,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
            'data' => [
                // Sample data for demonstration
                ['id' => 1, 'name' => 'Item 1', 'value' => 100],
                ['id' => 2, 'name' => 'Item 2', 'value' => 200],
                ['id' => 3, 'name' => 'Item 3', 'value' => 300],
            ]
        ];
    }

    private function generatePdf(array $data, string $filePath): bool
    {
        try {
            // Create new PDF document
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            // Set document information
            $pdf->SetCreator('Cashbox');
            $pdf->SetAuthor('Cashbox System');
            $pdf->SetTitle('Cashbox Export');
            $pdf->SetSubject('Cashbox Export');

            // Set default header data
            $pdf->SetHeaderData('', 0, 'Cashbox Export', 'Generated on ' . date('Y-m-d H:i:s'));

            // Set margins
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);

            // Set auto page breaks
            $pdf->SetAutoPageBreak(true, 25);

            // Add a page
            $pdf->AddPage();

            // Set font
            $pdf->SetFont('helvetica', '', 10);

            // Add content
            $html = $this->generateHtmlFromData($data);
            $pdf->writeHTML($html, true, false, true, false, '');

            // Save the PDF
            $pdf->Output($filePath, 'F');

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error generating PDF: ' . $e->getMessage());
            return false;
        }
    }

    private function generateExcel(array $data, string $filePath): bool
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Add headers
            $headers = $this->getHeadersFromData($data);
            foreach ($headers as $col => $header) {
                $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
            }

            // Add data
            $rows = $this->getRowsFromData($data);
            foreach ($rows as $rowIndex => $row) {
                foreach ($row as $colIndex => $value) {
                    $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 2, $value);
                }
            }

            // Save the Excel file
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error generating Excel: ' . $e->getMessage());
            return false;
        }
    }

    private function generateCsv(array $data, string $filePath): bool
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Add headers
            $headers = $this->getHeadersFromData($data);
            foreach ($headers as $col => $header) {
                $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
            }

            // Add data
            $rows = $this->getRowsFromData($data);
            foreach ($rows as $rowIndex => $row) {
                foreach ($row as $colIndex => $value) {
                    $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 2, $value);
                }
            }

            // Save the CSV file
            $writer = new Csv($spreadsheet);
            $writer->save($filePath);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error generating CSV: ' . $e->getMessage());
            return false;
        }
    }

    private function generateHtmlFromData(array $data): string
    {
        $html = '<h1>Cashbox Export</h1>';
        $html .= '<p>Generated on ' . date('Y-m-d H:i:s') . '</p>';

        if (isset($data['type'])) {
            $html .= '<p>Type: ' . $data['type'] . '</p>';
        }

        if (isset($data['data']) && is_array($data['data'])) {
            $html .= '<table border="1" cellpadding="5">';

            // Add headers
            $html .= '<tr>';
            $headers = $this->getHeadersFromData($data);
            foreach ($headers as $header) {
                $html .= '<th>' . $header . '</th>';
            }
            $html .= '</tr>';

            // Add rows
            $rows = $this->getRowsFromData($data);
            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach ($row as $value) {
                    $html .= '<td>' . $value . '</td>';
                }
                $html .= '</tr>';
            }

            $html .= '</table>';
        } else {
            $html .= '<p>No data available</p>';
        }

        return $html;
    }

    private function getHeadersFromData(array $data): array
    {
        if (isset($data['data'][0]) && is_array($data['data'][0])) {
            return array_keys($data['data'][0]);
        }

        return ['No Data'];
    }

    private function getRowsFromData(array $data): array
    {
        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        return [];
    }
}
