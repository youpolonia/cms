<?php
declare(strict_types=1);

namespace Services;

class TestRunner {
    private array $testSuites = [];
    private array $testResults = [];

    public function addTestSuite(string $name, callable $suite): void {
        $this->testSuites[$name] = $suite;
    }

    public function runAllTests(): array {
        $this->testResults = [];
        foreach ($this->testSuites as $name => $suite) {
            $this->testResults[$name] = $this->runTestSuite($name, $suite);
        }
        return $this->testResults;
    }

    public function runTestSuite(string $name, callable $suite): array {
        $start = microtime(true);
        $result = [
            'status' => 'passed',
            'assertions' => 0,
            'failures' => []
        ];

        try {
            $suite(function(bool $condition, string $message = '') use (&$result) {
                $result['assertions']++;
                if (!$condition) {
                    $result['status'] = 'failed';
                    $result['failures'][] = $message;
                }
            });
        } catch (\Throwable $e) {
            $result['status'] = 'error';
            $result['failures'][] = $e->getMessage();
        }

        $result['duration'] = microtime(true) - $start;
        return $result;
    }

    public function stressTest(callable $test, int $iterations = 1000): array {
        $start = microtime(true);
        $successCount = 0;
        $failures = [];

        for ($i = 0; $i < $iterations; $i++) {
            try {
                $test();
                $successCount++;
            } catch (\Throwable $e) {
                $failures[] = $e->getMessage();
            }
        }

        return [
            'iterations' => $iterations,
            'success_rate' => ($successCount / $iterations) * 100,
            'failures' => $failures,
            'duration' => microtime(true) - $start
        ];
    }
}
