<?php
namespace CMS\Services;

use CMS\Core\OperationalTransform;

class OTService {
    private $transformer;

    public function __construct() {
        $this->transformer = new OperationalTransform();
    }

    public function applyTransformations(array $localOps, array $remoteOps): array {
        $transformedOps = [];
        
        foreach ($localOps as $op) {
            $transformedOp = $op;
            foreach ($remoteOps as $remoteOp) {
                $transformedOp = $this->transformer->transform($transformedOp, $remoteOp);
            }
            $transformedOps[] = $transformedOp;
        }
        
        return $transformedOps;
    }

    public function getTransformedOperation(array $operation, array $againstOperations): array {
        $result = $operation;
        foreach ($againstOperations as $againstOp) {
            $result = $this->transformer->transform($result, $againstOp);
        }
        return $result;
    }

    public function applyOperation(string $document, array $operation): string {
        if ($operation['type'] === 'insert') {
            return substr_replace($document, $operation['text'], $operation['position'], 0);
        } else {
            // delete operation
            return substr_replace(
                $document, 
                '', 
                $operation['position'], 
                strlen($operation['text'])
            );
        }
    }
}
