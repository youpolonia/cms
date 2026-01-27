<?php
/**
 * Advanced Report Filtering Component
 * 
 * Provides dynamic filtering capabilities for CMS reports
 * 
 * @package CMS\Admin\Reports
 */

namespace CMS\Admin\Reports;

class ReportFilter {
    /**
     * @var array Supported filter types
     */
    private const FILTER_TYPES = [
        'text', 'number', 'date', 'select', 'boolean', 'range'
    ];

    /**
     * @var array Current filter configuration
     */
    private $filters = [];

    /**
     * @var array Validation rules for filters
     */
    private $validationRules = [];

    /**
     * Initialize filter builder
     */
    public function __construct() {
        $this->initializeDefaultFilters();
    }

    /**
     * Add a new filter
     * 
     * @param string $name Filter name
     * @param string $type Filter type
     * @param array $options Filter options
     * @return self
     * @throws \InvalidArgumentException
     */
    public function addFilter(string $name, string $type, array $options = []): self {
        if (!in_array($type, self::FILTER_TYPES)) {
            throw new \InvalidArgumentException("Invalid filter type: $type");
        }

        $this->filters[$name] = [
            'type' => $type,
            'options' => $options,
            'value' => null
        ];

        $this->validationRules[$name] = $this->getValidationRule($type);

        return $this;
    }

    /**
     * Get validation rule for filter type
     * 
     * @param string $type Filter type
     * @return array Validation rule
     */
    private function getValidationRule(string $type): array {
        switch ($type) {
            case 'number':
            case 'range':
                return ['filter' => FILTER_VALIDATE_FLOAT];
            case 'date':
                return [
                    'filter' => FILTER_CALLBACK,
                    'options' => function($value) {
                        $date = \DateTime::createFromFormat('Y-m-d', $value);
                        return $date && $date->format('Y-m-d') === $value ? $value : false;
                    }
                ];
            case 'boolean':
                return ['filter' => FILTER_VALIDATE_BOOLEAN];
            default:
                return [
                    'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                    'flags' => FILTER_FLAG_NO_ENCODE_QUOTES
                ];
        }
    }

    /**
     * Apply filters to data
     * 
     * @param array $data Input data
     * @return array Filtered data
     */
    public function applyFilters(array $data): array {
        $filteredData = [];

        foreach ($data as $row) {
            $require_once = true;
            
            foreach ($this->filters as $name => $filter) {
                if ($filter['value'] !== null && !$this->matchesFilter($row, $name, $filter)) {
                    $require_once = false;
                    break;
                }
            }

            if ($require_once) {
                $filteredData[] = $row;
            }
        }

        return $filteredData;
    }

    /**
     * Check if row matches filter
     */
    private function matchesFilter(array $row, string $name, array $filter): bool {
        if (!isset($row[$name])) {
            return false;
        }

        $value = $row[$name];
        $filterValue = $filter['value'];

        switch ($filter['type']) {
            case 'text':
                $cleanValue = htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
                $cleanFilter = htmlspecialchars(strip_tags($filterValue), ENT_QUOTES, 'UTF-8');
                return stripos($cleanValue, $cleanFilter) !== false;
            case 'number':
                return $value == $filterValue;
            case 'date':
                return $value == $filterValue;
            case 'select':
                return in_array($value, (array)$filterValue);
            case 'boolean':
                return (bool)$value === (bool)$filterValue;
            case 'range':
                return $value >= $filterValue['min'] && $value <= $filterValue['max'];
            default:
                return true;
        }
    }

    /**
     * Render filter UI
     * 
     * @return string HTML output
     */
    public function renderFilterUI(): string {
        $html = '<div class="report-filters">';

        foreach ($this->filters as $name => $filter) {
            $html .= $this->renderFilterField($name, $filter);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render individual filter field
     */
    private function renderFilterField(string $name, array $filter): string {
        $type = $filter['type'];
        $options = $filter['options'];
        $value = $filter['value'] ?? '';

        $label = $options['label'] ?? ucfirst(str_replace('_', ' ', $name));
        $placeholder = $options['placeholder'] ?? '';

        $html = '<div class="filter-field filter-' . $type . '">';
        $html .= '<label>' . htmlspecialchars($label) . '</label>';

        switch ($type) {
            case 'text':
                $html .= '<input type="text" name="filters[' . $name . ']" value="' . htmlspecialchars($value) . '" placeholder="' . htmlspecialchars($placeholder) . '">';
                break;
            case 'number':
                $html .= '<input type="number" name="filters[' . $name . ']" value="' . htmlspecialchars($value) . '">';
                break;
            case 'date':
                $html .= '<input type="date" name="filters[' . $name . ']" value="' . htmlspecialchars($value) . '">';
                break;
            case 'select':
                $html .= '<select name="filters[' . $name . ']">';
                foreach ($options['choices'] ?? [] as $key => $choice) {
                    $selected = $value == $key ? ' selected' : '';
                    $html .= '<option value="' . htmlspecialchars($key) . '"' . $selected . '>' . htmlspecialchars($choice) . '</option>';
                }
                $html .= '</select>';
                break;
            case 'boolean':
                $checked = $value ? ' checked' : '';
                $html .= '<input type="checkbox" name="filters[' . $name . ']" value="1"' . $checked . '>';
                break;
            case 'range':
                $min = $value['min'] ?? '';
                $max = $value['max'] ?? '';
                $html .= '<div class="range-fields">';
                $html .= '<input type="number" name="filters[' . $name . '][min]" value="' . htmlspecialchars($min) . '" placeholder="Min">';
                $html .= '<input type="number" name="filters[' . $name . '][max]" value="' . htmlspecialchars($max) . '" placeholder="Max">';
                $html .= '</div>';
                break;
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Export filtered data to specified format
     * 
     * @param array $data Data to export
     * @param string $format Export format (csv, excel, json)
     * @return string Exported data
     */
    public function exportData(array $data, string $format = 'csv'): string {
        switch (strtolower($format)) {
            case 'csv':
                return $this->exportToCSV($data);
            case 'excel':
                return $this->exportToExcel($data);
            case 'json':
                return json_encode($data, JSON_PRETTY_PRINT);
            default:
                throw new \InvalidArgumentException("Unsupported export format: $format");
        }
    }

    /**
     * Export data to CSV
     */
    private function exportToCSV(array $data): string {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'w');
        
        // Write headers
        fputcsv($output, array_keys($data[0]));

        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Export data to Excel (using simple HTML table format)
     */
    private function exportToExcel(array $data): string {
        if (empty($data)) {
            return '';
        }

        $html = '<table>';

        // Write headers
        $html .= '<tr>';
        foreach (array_keys($data[0]) as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr>';

        // Write data
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }

    /**
     * Initialize default filters based on report type
     */
    private function initializeDefaultFilters(): void {
        // Example default filters - can be extended based on specific report needs
        $this->addFilter('date_from', 'date', ['label' => 'From Date']);
        $this->addFilter('date_to', 'date', ['label' => 'To Date']);
        $this->addFilter('status', 'select', [
            'label' => 'Status',
            'choices' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'pending' => 'Pending'
            ]
        ]);
    }

    /**
     * Load filters from request
     * 
     * @param array $request Request data
     */
    public function loadFromRequest(array $request): void {
        if (!isset($request['filters'])) {
            return;
        }

        foreach ($request['filters'] as $name => $value) {
            if (isset($this->filters[$name])) {
                $this->filters[$name]['value'] = $this->validateFilterValue($name, $value);
            }
        }

        // Validate date range if both dates are set
        if (isset($this->filters['date_from']['value'], $this->filters['date_to']['value'])) {
            $from = \DateTime::createFromFormat('Y-m-d', $this->filters['date_from']['value']);
            $to = \DateTime::createFromFormat('Y-m-d', $this->filters['date_to']['value']);
            
            if ($from > $to) {
                throw new \InvalidArgumentException("End date cannot be before start date");
            }
        }
    }

    /**
     * Validate filter value
     */
    private function validateFilterValue(string $name, $value) {
        if (!isset($this->validationRules[$name])) {
            return $value;
        }

        $rule = $this->validationRules[$name];
        $validated = filter_var($value, $rule['filter'], $rule['options'] ?? null);

        if ($validated === false) {
            throw new \InvalidArgumentException("Invalid value for filter $name");
        }

        return $validated;
    }

    /**
     * Get current filter values
     * 
     * @return array Filter values
     */
    public function getFilterValues(): array {
        $values = [];
        
        foreach ($this->filters as $name => $filter) {
            if ($filter['value'] !== null) {
                $values[$name] = $filter['value'];
            }
        }

        return $values;
    }

    /**
     * Check if any filters are active
     * 
     * @return bool True if filters are active
     */
    public function hasActiveFilters(): bool {
        foreach ($this->filters as $filter) {
            if ($filter['value'] !== null) {
                return true;
            }
        }
        return false;
    }

    /**
     * Prepare filters for scheduled report
     * 
     * @return array Serializable filter data
     */
    public function prepareForScheduling(): array {
        return [
            'filters' => $this->filters,
            'validation_rules' => $this->validationRules
        ];
    }

    /**
     * Load filters from scheduled configuration
     * 
     * @param array $config Scheduled configuration
     */
    public function loadFromScheduled(array $config): void {
        $this->filters = $config['filters'] ?? [];
        $this->validationRules = $config['validation_rules'] ?? [];
    }
}
