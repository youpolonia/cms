<?php
/**
 * Simple Test Runner for Jessie CMS
 * No PHPUnit dependency - pure PHP
 * 
 * @package JessieCMS
 * @since 2026-02-08
 */

class TestRunner
{
    private array $tests = [];
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function addTest(string $name, callable $fn): self
    {
        $this->tests[$name] = $fn;
        return $this;
    }

    public function run(): void
    {
        echo "=== Running " . count($this->tests) . " tests ===\n\n";

        foreach ($this->tests as $name => $fn) {
            try {
                $fn();
                $this->passed++;
                echo "✅ $name\n";
            } catch (Throwable $e) {
                $this->failed++;
                $this->failures[$name] = $e->getMessage();
                echo "❌ $name: " . $e->getMessage() . "\n";
            }
        }

        echo "\n=== Results ===\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo "Total:  " . count($this->tests) . "\n";

        if ($this->failed > 0) {
            echo "\nFailures:\n";
            foreach ($this->failures as $name => $msg) {
                echo "  - $name: $msg\n";
            }
        }
    }

    public static function assert(bool $condition, string $message = ''): void
    {
        if (!$condition) {
            throw new Exception($message ?: 'Assertion failed');
        }
    }

    public static function assertEquals($expected, $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            $msg = $message ?: "Expected " . var_export($expected, true) . ", got " . var_export($actual, true);
            throw new Exception($msg);
        }
    }

    public static function assertNotEmpty($value, string $message = ''): void
    {
        if (empty($value)) {
            throw new Exception($message ?: 'Value is empty');
        }
    }

    public static function assertInstanceOf(string $class, $object, string $message = ''): void
    {
        if (!$object instanceof $class) {
            throw new Exception($message ?: "Expected instance of $class");
        }
    }
}
