<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Log;

class AIAnalyticsExport implements FromArray, WithMultipleSheets
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function sheets(): array
    {
        try {
            return [
                new AISummarySheet($this->data),
                new AIGenerationUsageSheet($this->data['generation_usage'] ?? []),
                new BlockSuggestionsSheet($this->data['block_suggestions'] ?? []),
                new PromptTypesSheet($this->data['prompt_types'] ?? [])
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate AI analytics export sheets', [
                'error' => $e->getMessage(),
                'data' => array_keys($this->data)
            ]);
            throw $e;
        }
    }
}

class AISummarySheet implements FromArray, WithTitle, WithHeadings, ShouldAutoSize
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        try {
            return [
                ['Total AI-Generated Content', $this->data['total_ai_generated'] ?? 0],
                ['AI Generation Adoption Rate', $this->formatPercentage($this->data['adoption_rate'] ?? 0)],
                ['Average AI Blocks per Content', $this->formatDecimal($this->data['avg_ai_blocks'] ?? 0)],
                ['Block Suggestion Usage Rate', $this->formatPercentage($this->data['suggestion_usage_rate'] ?? 0)]
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate AI summary sheet', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function title(): string
    {
        return 'AI Summary';
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    protected function formatPercentage(float $value): string
    {
        return number_format($value, 2) . '%';
    }

    protected function formatDecimal(float $value): string
    {
        return number_format($value, 2);
    }
}

class AIGenerationUsageSheet implements FromArray, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data as $contentType => $usage) {
            $rows[] = [
                $contentType,
                $usage['count'],
                number_format($usage['percentage'], 2) . '%',
                $usage['avg_blocks']
            ];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Generation Usage';
    }

    public function headings(): array
    {
        return ['Content Type', 'Count', 'Usage Rate (%)', 'Avg AI Blocks'];
    }
}

class BlockSuggestionsSheet implements FromArray, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data as $blockType => $metrics) {
            $rows[] = [
                $blockType,
                $metrics['suggested'],
                $metrics['used'],
                number_format($metrics['adoption_rate'], 2) . '%'
            ];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Block Suggestions';
    }

    public function headings(): array
    {
        return ['Block Type', 'Suggested', 'Used', 'Adoption Rate (%)'];
    }
}

class PromptTypesSheet implements FromArray, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data as $promptType => $count) {
            $rows[] = [$promptType, $count];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Prompt Types';
    }

    public function headings(): array
    {
        return ['Prompt Type', 'Usage Count'];
    }
}