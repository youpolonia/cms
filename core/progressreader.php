<?php
class ProgressReader {
    const MAX_LINES = 50;
    
    public static function read($line_range = null) {
        $file = __DIR__.'/../logs/progress.md';
        if(!file_exists($file)) {
            throw new Exception('Progress file not found');
        }

        if($line_range === null) {
            return self::getLastEntries();
        }

        $lines = file($file);
        $total = count($lines);
        
        if(strpos($line_range, '-') !== false) {
            list($start, $end) = explode('-', $line_range);
            $start = max(1, (int)$start);
            $end = min($total, (int)$end);
            
            if($end - $start > self::MAX_LINES) {
                throw new Exception('Maximum '.self::MAX_LINES.' lines per read');
            }
            
            return array_slice($lines, $start-1, $end-$start+1);
        }
        
        throw new Exception('Invalid line range format');
    }
    
    private static function getLastEntries() {
        $file = __DIR__.'/../logs/progress.md';
        $lines = file($file);
        return array_slice($lines, -self::MAX_LINES);
    }
}
