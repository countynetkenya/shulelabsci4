<?php

declare(strict_types=1);

namespace Modules\Reports\Services;

use RuntimeException;

/**
 * Service for exporting reports to various formats
 * 
 * Handles PDF, Excel, and CSV export functionality.
 */
class ExportService
{
    /**
     * Export report data to specified format
     * 
     * @param array<int, array<string, mixed>> $data
     * @param string $format
     * @param array<string, mixed> $options
     * @return array{content: string, filename: string, mime_type: string}
     */
    public function export(array $data, string $format, array $options = []): array
    {
        return match ($format) {
            'pdf' => $this->exportToPdf($data, $options),
            'excel' => $this->exportToExcel($data, $options),
            'csv' => $this->exportToCsv($data, $options),
            'json' => $this->exportToJson($data, $options),
            default => throw new RuntimeException("Unsupported export format: {$format}"),
        };
    }

    /**
     * Export to PDF format
     * 
     * @param array<int, array<string, mixed>> $data
     * @param array<string, mixed> $options
     * @return array{content: string, filename: string, mime_type: string}
     */
    private function exportToPdf(array $data, array $options): array
    {
        // Placeholder for PDF generation
        // In production, use a library like TCPDF or Dompdf
        $title = $options['title'] ?? 'Report';
        $headers = $this->extractHeaders($data);
        
        $html = $this->generateHtmlTable($data, $headers, $title);
        
        return [
            'content'   => $html, // Would be PDF binary in production
            'filename'  => $this->generateFilename($title, 'pdf'),
            'mime_type' => 'application/pdf',
        ];
    }

    /**
     * Export to Excel format
     * 
     * @param array<int, array<string, mixed>> $data
     * @param array<string, mixed> $options
     * @return array{content: string, filename: string, mime_type: string}
     */
    private function exportToExcel(array $data, array $options): array
    {
        // Placeholder for Excel generation
        // In production, use a library like PhpSpreadsheet
        $title = $options['title'] ?? 'Report';
        $csv = $this->generateCsvContent($data);
        
        return [
            'content'   => $csv, // Would be Excel binary in production
            'filename'  => $this->generateFilename($title, 'xlsx'),
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
    }

    /**
     * Export to CSV format
     * 
     * @param array<int, array<string, mixed>> $data
     * @param array<string, mixed> $options
     * @return array{content: string, filename: string, mime_type: string}
     */
    private function exportToCsv(array $data, array $options): array
    {
        $title = $options['title'] ?? 'Report';
        $csv = $this->generateCsvContent($data);
        
        return [
            'content'   => $csv,
            'filename'  => $this->generateFilename($title, 'csv'),
            'mime_type' => 'text/csv',
        ];
    }

    /**
     * Export to JSON format
     * 
     * @param array<int, array<string, mixed>> $data
     * @param array<string, mixed> $options
     * @return array{content: string, filename: string, mime_type: string}
     */
    private function exportToJson(array $data, array $options): array
    {
        $title = $options['title'] ?? 'Report';
        
        return [
            'content'   => json_encode($data, JSON_PRETTY_PRINT),
            'filename'  => $this->generateFilename($title, 'json'),
            'mime_type' => 'application/json',
        ];
    }

    /**
     * Generate CSV content from data
     * 
     * @param array<int, array<string, mixed>> $data
     * @return string
     */
    private function generateCsvContent(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $headers = $this->extractHeaders($data);
        
        $output = fopen('php://temp', 'r+');
        if ($output === false) {
            throw new RuntimeException('Failed to open temporary file for CSV generation');
        }

        // Write headers
        fputcsv($output, $headers);

        // Write data rows
        foreach ($data as $row) {
            $values = [];
            foreach ($headers as $header) {
                $values[] = $row[$header] ?? '';
            }
            fputcsv($output, $values);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv !== false ? $csv : '';
    }

    /**
     * Generate HTML table for PDF
     * 
     * @param array<int, array<string, mixed>> $data
     * @param array<string> $headers
     * @param string $title
     * @return string
     */
    private function generateHtmlTable(array $data, array $headers, string $title): string
    {
        $html = "<!DOCTYPE html>\n<html>\n<head>\n";
        $html .= "<title>" . htmlspecialchars($title) . "</title>\n";
        $html .= "<style>\n";
        $html .= "body { font-family: Arial, sans-serif; }\n";
        $html .= "h1 { color: #333; }\n";
        $html .= "table { border-collapse: collapse; width: 100%; }\n";
        $html .= "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }\n";
        $html .= "th { background-color: #4CAF50; color: white; }\n";
        $html .= "tr:nth-child(even) { background-color: #f2f2f2; }\n";
        $html .= "</style>\n</head>\n<body>\n";
        $html .= "<h1>" . htmlspecialchars($title) . "</h1>\n";
        $html .= "<p>Generated on: " . date('Y-m-d H:i:s') . "</p>\n";
        $html .= "<table>\n<thead>\n<tr>\n";

        // Headers
        foreach ($headers as $header) {
            $html .= "<th>" . htmlspecialchars($header) . "</th>\n";
        }
        $html .= "</tr>\n</thead>\n<tbody>\n";

        // Data rows
        foreach ($data as $row) {
            $html .= "<tr>\n";
            foreach ($headers as $header) {
                $value = $row[$header] ?? '';
                $html .= "<td>" . htmlspecialchars((string) $value) . "</td>\n";
            }
            $html .= "</tr>\n";
        }

        $html .= "</tbody>\n</table>\n</body>\n</html>";

        return $html;
    }

    /**
     * Extract headers from data
     * 
     * @param array<int, array<string, mixed>> $data
     * @return array<string>
     */
    private function extractHeaders(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        return array_keys($data[0]);
    }

    /**
     * Generate filename with timestamp
     * 
     * @param string $title
     * @param string $extension
     * @return string
     */
    private function generateFilename(string $title, string $extension): string
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '_', strtolower(trim($title)));
        $slug = trim($slug, '_');
        
        if (empty($slug)) {
            $slug = 'report';
        }
        
        $timestamp = date('Y-m-d_His');
        return "{$slug}_{$timestamp}.{$extension}";
    }
}
