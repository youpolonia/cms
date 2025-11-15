<?php
declare(strict_types=1);

class DiffEngine {
    public static function compareText(string $old, string $new): array {
        return [
            'changes' => $old !== $new,
            'diff' => $old !== $new ? 'Text content changed' : 'No text changes'
        ];
    }

    public static function compareJson(array $old, array $new): array {
        $diff = [
            'added' => [],
            'changed' => [],
            'removed' => []
        ];

        foreach ($new as $key => $value) {
            if (!array_key_exists($key, $old)) {
                $diff['added'][$key] = $value;
            } elseif ($old[$key] !== $value) {
                if (is_array($value) && is_array($old[$key])) {
                    $diff['changed'][$key] = self::compareJson($old[$key], $value);
                } else {
                    $diff['changed'][$key] = [
                        'old' => $old[$key],
                        'new' => $value
                    ];
                }
            }
        }

        foreach ($old as $key => $value) {
            if (!array_key_exists($key, $new)) {
                $diff['removed'][$key] = $value;
            }
        }

        return $diff;
    }
}
