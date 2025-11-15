<?php
/**
 * Plugin Dependency Checker
 * 
 * Checks plugin dependencies and conflicts before activation
 */
class DependencyChecker {
    private $versionComparator;
    private $plugins = [];
    private $errors = [];
    private $warnings = [];

    public function __construct() {
        $this->versionComparator = new SemanticVersionComparator();
    }

    /**
     * Load all available plugins metadata
     */
    public function loadPlugins() {
        // TODO: Implement actual plugin loading
        // For now using mock data
        $this->plugins = [
            'pluginA' => [
                'version' => '1.2.0',
                'requires' => [
                    'pluginB' => '^1.0.0',
                    'pluginC' => '^2.1.0'
                ]
            ],
            'pluginB' => [
                'version' => '1.1.5',
                'conflicts' => [
                    'pluginD' => '<3.0.0'
                ]
            ]
        ];
    }

    /**
     * Check dependencies for a specific plugin
     */
    public function checkDependencies($pluginName) {
        if (!isset($this->plugins[$pluginName])) {
            $this->errors[] = "Plugin $pluginName not found";
            return false;
        }

        $plugin = $this->plugins[$pluginName];
        $valid = true;

        // Check required dependencies
        if (isset($plugin['requires'])) {
            foreach ($plugin['requires'] as $depName => $versionConstraint) {
                if (!$this->checkDependency($pluginName, $depName, $versionConstraint)) {
                    $valid = false;
                }
            }
        }

        // Check conflicts
        if (isset($plugin['conflicts'])) {
            foreach ($plugin['conflicts'] as $conflictName => $versionConstraint) {
                if (!$this->checkConflict($pluginName, $conflictName, $versionConstraint)) {
                    $valid = false;
                }
            }
        }

        return $valid;
    }

    private function checkDependency($pluginName, $depName, $constraint) {
        if (!isset($this->plugins[$depName])) {
            $this->errors[] = "$pluginName requires $depName which is not installed";
            return false;
        }

        $depVersion = $this->plugins[$depName]['version'];
        if (!$this->versionSatisfies($depVersion, $constraint)) {
            $this->errors[] = "$pluginName requires $depName $constraint but found $depVersion";
            return false;
        }

        return true;
    }

    private function checkConflict($pluginName, $conflictName, $constraint) {
        if (!isset($this->plugins[$conflictName])) {
            return true;
        }

        $conflictVersion = $this->plugins[$conflictName]['version'];
        if ($this->versionSatisfies($conflictVersion, $constraint)) {
            $this->errors[] = "$pluginName conflicts with $conflictName $constraint (found $conflictVersion)";
            return false;
        }

        return true;
    }

    private function versionSatisfies($version, $constraint) {
        try {
            // Simple version constraint checking
            if (strpos($constraint, '^') === 0) {
                $requiredMin = substr($constraint, 1);
                return $this->versionComparator->compare($version, $requiredMin) >= 0;
            } elseif (strpos($constraint, '<') === 0) {
                $maxVersion = substr($constraint, 1);
                return $this->versionComparator->compare($version, $maxVersion) < 0;
            } elseif (strpos($constraint, '>') === 0) {
                $minVersion = substr($constraint, 1);
                return $this->versionComparator->compare($version, $minVersion) > 0;
            } else {
                // Exact version match
                return $this->versionComparator->compare($version, $constraint) === 0;
            }
        } catch (Exception $e) {
            $this->errors[] = "Version comparison error: " . $e->getMessage();
            return false;
        }
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getWarnings() {
        return $this->warnings;
    }

    public function clearMessages() {
        $this->errors = [];
        $this->warnings = [];
    }
}
