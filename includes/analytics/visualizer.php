<?php
declare(strict_types=1);

class AnalyticsVisualizer {
    private const CHART_WIDTH = 800;
    private const CHART_HEIGHT = 400;
    private const BAR_WIDTH = 30;
    private const COLORS = ['#4e73df', '#1cc88a', '#36b9cc'];

    public static function generateDailyChart(string $tenantId, string $date): string {
        $data = self::loadDataForDate($tenantId, $date);
        if (empty($data)) {
            return self::generateEmptyChart();
        }

        $maxValue = max(array_column($data, 'value'));
        $scaleFactor = self::CHART_HEIGHT / ($maxValue * 1.1);

        $svg = '<svg width="'.self::CHART_WIDTH.'" height="'.self::CHART_HEIGHT.'">';
        $svg .= self::generateAxes();
        
        foreach ($data as $index => $point) {
            $x = 50 + ($index * (self::BAR_WIDTH + 10));
            $height = $point['value'] * $scaleFactor;
            $y = self::CHART_HEIGHT - $height - 30;
            $color = self::COLORS[$index % count(self::COLORS)];

            $svg .= sprintf(
                '<rect x="%d" y="%d" width="%d" height="%d" fill="%s"/>',
                $x, $y, self::BAR_WIDTH, $height, $color
            );
        }

        $svg .= '</svg>';
        return $svg;
    }

    private static function loadDataForDate(string $tenantId, string $date): array {
        $path = "analytics/{$tenantId}/{$date}.json";
        if (!file_exists($path)) {
            return [];
        }
        
        $rawData = json_decode(file_get_contents($path), true);
        return array_map(function($entry) {
            return [
                'timestamp' => $entry['timestamp'],
                'value' => $entry['data']['value'] ?? 0
            ];
        }, $rawData);
    }

    private static function generateAxes(): string {
        return '
<line x1="50" y1="30" x2="50" y2="'.(self::CHART_HEIGHT-30).'" stroke="#000"/>
                <line x1="50" y1="'.(self::CHART_HEIGHT-30).'" x2="'.(self::CHART_WIDTH-50).'" y2="'.(self::CHART_HEIGHT-30).'" stroke="#000"/>';
    }

    private static
 function generateEmptyChart(): string {
        return '<svg width="'.self::CHART_WIDTH.'" height="'.self::CHART_HEIGHT.'">
                <text x="50%" y="50%" text-anchor="middle">No data available</text>
                </svg>';
    }
}
