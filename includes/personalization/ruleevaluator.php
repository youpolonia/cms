<?php
declare(strict_types=1);

namespace Includes\Personalization;

class RuleEvaluator {
    private array $rules;
    private array $context;

    public function __construct(array $rules) {
        $this->rules = $rules;
        $this->context = [];
    }

    public function evaluate(array $userContext, array $contentContext): array {
        $this->context = [
            'user' => $userContext,
            'content' => $contentContext,
            'score' => $this->rules['default_score'] ?? 50
        ];

        foreach ($this->rules['evaluation_order'] ?? [] as $ruleGroup) {
            if (isset($this->rules[$ruleGroup])) {
                $this->applyRuleGroup($this->rules[$ruleGroup]);
            }
        }

        return [
            'score' => $this->context['score'],
            'matched_rules' => $this->context['matched_rules'] ?? []
        ];
    }

    private function applyRuleGroup(array $rules): void {
        foreach ($rules as $ruleName => $ruleConfig) {
            if ($this->matchesConditions($ruleConfig['conditions'] ?? [])) {
                $this->context['score'] += $ruleConfig['weight'] ?? 0;
                $this->context['matched_rules'][] = $ruleName;
            }
        }
    }

    private function matchesConditions(array $conditions): bool {
        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? '';
            $operator = $condition['operator'] ?? 'equals';
            $value = $condition['value'] ?? null;

            if (!$this->evaluateCondition($field, $operator, $value)) {
                return false;
            }
        }
        return true;
    }

    private function evaluateCondition(string $field, string $operator, $value): bool {
        $fieldValue = $this->getFieldValue($field);

        switch ($operator) {
            case 'equals':
                return $fieldValue == $value;
            case 'not_equals':
                return $fieldValue != $value;
            case 'contains':
                return is_array($fieldValue) 
                    ? in_array($value, $fieldValue)
                    : strpos((string)$fieldValue, (string)$value) !== false;
            case 'greater_than':
                return $fieldValue > $value;
            case 'less_than':
                return $fieldValue < $value;
            case 'in':
                return is_array($value) && in_array($fieldValue, $value);
            case 'not_in':
                return is_array($value) && !in_array($fieldValue, $value);
            default:
                return false;
        }
    }

    private function getFieldValue(string $fieldPath) {
        $parts = explode('.', $fieldPath);
        $value = $this->context;

        foreach ($parts as $part) {
            if (!isset($value[$part])) {
                return null;
            }
            $value = $value[$part];
        }

        return $value;
    }
}
