# Testing Guide

This document outlines the basic testing framework and conventions for the CMS project. Given the project's constraints (framework-free, no Composer/Node), we use a simple, custom PHP-based testing approach.

## Overview

The testing setup relies on:
*   A `TestRunner` class ([`tests/TestRunner.php`](tests/TestRunner.php:1)) that executes test methods within test classes.
*   A bootstrap file ([`tests/bootstrap.php`](tests/bootstrap.php:1)) to set up the testing environment and include necessary files.
*   A script to run all tests ([`tests/run_all_tests.php`](tests/run_all_tests.php:1)).
*   Test files are PHP classes typically ending with `Test.php` or `_test.php`.

## Writing Tests

1.  **File Location**: Place your test files in the `tests/` directory or appropriate subdirectories (e.g., `tests/Unit/`, `tests/Integration/`, `tests/Services/`).
2.  **File Naming**: Test files should be named after the class they are testing, suffixed with `Test.php`. For example, if you are testing `MyClass.php`, the test file should be `MyClassTest.php`.
3.  **Class Naming**: The test class name must match the filename (without the `.php` extension). For example, `MyClassTest.php` must contain `class MyClassTest`.
4.  **Test Methods**:
    *   Individual test methods within the class MUST be prefixed with `test`. For example, `public function testMyFunctionality()`.
    *   Each test method should ideally test one specific aspect or unit of behavior.
5.  **Assertions**:
    *   There isn't a built-in assertion library like PHPUnit's. Tests should perform checks and throw an `Exception` if a check fails. The `TestRunner` will catch this exception and mark the test as FAILED.
    *   If a test method completes without throwing an exception, it is considered PASSED.
    *   Example:
        ```php
        // In MyClassTest.php
        class MyClassTest {
            public function testAddition() {
                $myClass = new MyClass();
                $result = $myClass->add(2, 2);
                if ($result !== 4) {
                    throw new Exception("Addition test failed: Expected 4, got " . $result);
                }
                // If no exception, test passes
            }

            public function testSomethingElse() {
                // ... test logic ...
                if (/* some condition is false */ false) {
                    throw new Exception("Something else failed because of X, Y, Z.");
                }
            }
        }
        ```
6.  **Setup/Teardown**: If you need setup before each test or teardown after, you can use a constructor (`__construct()`) for one-time setup per test file, or implement your own `setUp()` and `tearDown()` methods and call them explicitly at the beginning/end of your test methods if needed. The current `TestRunner` does not automatically call `setUp/tearDown` methods.

## Running Tests

1.  **Run All Tests**:
    To run all test files found in the `tests/` directory and its subdirectories, execute the following command from the project root:
    ```bash
    php tests/run_all_tests.php
    ```
    This script will:
    *   Discover files ending in `Test.php` or `_test.php`.
    *   Instantiate `TestRunner` for each test file.
    *   Execute all `test*` methods in each test class.
    *   Output PASS/FAIL status for each test method to the console.
    *   Save a summary of the test run to a new Markdown file in `memory-bank/` (e.g., `memory-bank/test_run_summary_YYYY-MM-DD_HH-MM-SS.md`).
    *   Exit with status code 0 if all tests pass, or 1 if any test fails.

2.  **Run a Specific Test File (using TestRunner directly - advanced)**:
    While `run_all_tests.php` is preferred, you could theoretically run a single test file by creating a small script that instantiates and uses `TestRunner` directly for that file.
    ```php
    // Example: run_single.php
    // require_once 'tests/bootstrap.php'; // Includes TestRunner
    // $runner = new TestRunner('tests/Unit/MySpecificTest.php');
    // $results = $runner->run();
    // print_r($results);
    ```
    Note: The `TestRunner` itself also appends results to `memory-bank/progress.md`. The `run_all_tests.php` script provides a more comprehensive summary in a separate file.

## Test Environment

*   The [`tests/bootstrap.php`](tests/bootstrap.php:1) script sets up the basic environment. It includes the main [`core/bootstrap.php`](core/bootstrap.php:1), so core functionalities like logging and configuration should be available.
*   A `TESTING_ENVIRONMENT_LOADED` constant is defined, which can be used in your application code to alter behavior during tests (e.g., use a test database, mock external services).
*   A `storage/testing/` directory is available if tests need to write temporary files.

## Best Practices

*   Write tests for all new features and bug fixes.
*   Keep tests small and focused.
*   Ensure tests are independent and can be run in any order.
*   Avoid tests that rely on external services or a specific environment state that cannot be easily reproduced (or mock these dependencies).
*   Regularly run all tests to catch regressions.