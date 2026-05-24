<?php

namespace Core\Report;

class ReportManager
{
    public function output(ReportDefinition $report, string $format, string $filename): void
    {
        if ($format === 'excel') {
            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
            echo (new ExcelExporter())->render($report);
            return;
        }

        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: inline; filename="' . $filename . '.html"');
        echo (new HtmlReportRenderer())->render($report);
    }
}
