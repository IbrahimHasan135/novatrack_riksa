<?php

namespace Core\Report;

class ReportDefinition
{
    public string $title = '';
    public string $subtitle = '';
    public string $periodLabel = '';
    public string $mode = 'month';
    public array $metrics = [];
    public array $chartRows = [];
    public array $sections = [];
    public array $sheets = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
