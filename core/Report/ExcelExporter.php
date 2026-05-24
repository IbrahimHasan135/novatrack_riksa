<?php

namespace Core\Report;

class ExcelExporter
{
    public function render(ReportDefinition $report): string
    {
        $xml = '<?xml version="1.0"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        foreach ($report->sheets as $sheet) {
            $xml .= '<Worksheet ss:Name="' . $this->e($this->sheetName($sheet['title'])) . '"><Table>';
            $xml .= '<Row>';
            foreach ($sheet['columns'] as $column) {
                $xml .= '<Cell><Data ss:Type="String">' . $this->e($column['label']) . '</Data></Cell>';
            }
            $xml .= '</Row>';
            foreach ($sheet['rows'] as $row) {
                $xml .= '<Row>';
                foreach ($sheet['columns'] as $column) {
                    $type = $column['type'] ?? 'text';
                    $value = $row[$column['key']] ?? '';
                    $xml .= $this->cell($value, $type);
                }
                $xml .= '</Row>';
            }
            $xml .= '</Table></Worksheet>';
        }
        $xml .= '</Workbook>';
        return $xml;
    }

    private function cell(mixed $value, string $type): string
    {
        if (in_array($type, ['money', 'number'], true)) {
            return '<Cell><Data ss:Type="Number">' . (float)$value . '</Data></Cell>';
        }
        return '<Cell><Data ss:Type="String">' . $this->e((string)$value) . '</Data></Cell>';
    }

    private function sheetName(string $value): string
    {
        return substr(preg_replace('/[\\\\\\/\\?\\*\\[\\]\\:]/', '-', $value), 0, 31);
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
