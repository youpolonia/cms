<?php
class VariableSubstitutor {
    public function substitute($input, $context) {
        if (is_array($input)) {
            return $this->substituteArray($input, $context);
        }

        if (!is_string($input)) {
            return $input;
        }

        return preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', 
            function($matches) use ($context) {
                $varName = $matches[1];
                return $context[$varName] ?? $matches[0];
            },
            $input
        );
    }

    private function substituteArray($array, $context) {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $this->substitute($key, $context);
            $newValue = $this->substitute($value, $context);
            $result[$newKey] = $newValue;
        }
        return $result;
    }
}
