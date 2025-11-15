<?php
namespace Includes\Validation;

class Validator {
    /**
     * Validate content title
     * @param string $title
     * @return array [bool $valid, string $error]
     */
    public static function validateTitle(string $title): array {
        if (empty($title)) {
            return [false, "Title is required"];
        }

        if (strlen($title) > 255) {
            return [false, "Title must be 255 characters or less"];
        }

        if (!preg_match('/^[\w\s\-.,:;!?()\'"]+$/u', $title)) {
            return [false, "Title contains invalid characters"];
        }

        return [true, ""];
    }

    /**
     * Validate content body
     * @param string $content
     * @return array [bool $valid, string $error]
     */
    public static function validateContent(string $content): array {
        if (empty($content)) {
            return [false, "Content is required"];
        }

        if (strlen($content) > 10000) {
            return [false, "Content must be 10,000 characters or less"];
        }

        return [true, ""];
    }

    /**
     * Validate content type
     * @param string $type
     * @return array [bool $valid, string $error]
     */
    public static function validateContentType(string $type): array {
        $validTypes = ['page', 'post'];
        if (!in_array($type, $validTypes)) {
            return [false, "Invalid content type"];
        }
        return [true, ""];
    }

    /**
     * Validate status
     * @param string $status
     * @return array [bool $valid, string $error]
     */
    public static function validateStatus(string $status): array {
        $validStatuses = ['draft', 'published'];
        if (!in_array($status, $validStatuses)) {
            return [false, "Invalid status"];
        }
        return [true, ""];
    }

    /**
     * Validate lifecycle state
     * @param string $state
     * @return array [bool $valid, string $error]
     */
    public static function validateLifecycleState(string $state): array {
        $validStates = ['draft', 'review', 'published', 'archived'];
        if (!in_array($state, $validStates)) {
            return [false, "Invalid lifecycle state"];
        }
        return [true, ""];
    }

    /**
     * Validate schedule datetime
     * @param string $datetime
     * @return array [bool $valid, string $error]
     */
    public static function validateScheduleDatetime(string $datetime): array {
        if (empty($datetime)) {
            return [true, ""];
        }

        if (!strtotime($datetime)) {
            return [false, "Invalid datetime format"];
        }

        if (strtotime($datetime) < time()) {
            return [false, "Scheduled datetime must be in the future"];
        }

        return [true, ""];
    }

    /**
     * Validate all content fields
     * @param array $data
     * @return array [bool $valid, array $errors]
     */
    public static function validateContentData(array $data): array {
        $errors = [];
        $valid = true;

        // Validate title
        [$titleValid, $titleError] = self::validateTitle($data['title'] ?? '');
        if (!$titleValid) {
            $errors['title'] = $titleError;
            $valid = false;
        }

        // Validate content
        [$contentValid, $contentError] = self::validateContent($data['content'] ?? '');
        if (!$contentValid) {
            $errors['content'] = $contentError;
            $valid = false;
        }

        // Validate content type
        [$typeValid, $typeError] = self::validateContentType($data['content_type'] ?? '');
        if (!$typeValid) {
            $errors['content_type'] = $typeError;
            $valid = false;
        }

        // Validate status
        [$statusValid, $statusError] = self::validateStatus($data['status'] ?? '');
        if (!$statusValid) {
            $errors['status'] = $statusError;
            $valid = false;
        }

        // Validate lifecycle state if provided
        if (isset($data['lifecycle_state'])) {
            [$stateValid, $stateError] = self::validateLifecycleState($data['lifecycle_state']);
            if (!$stateValid) {
                $errors['lifecycle_state'] = $stateError;
                $valid = false;
            }
        }

        // Validate schedule datetime if provided
        if (isset($data['schedule_datetime'])) {
            [$datetimeValid, $datetimeError] = self::validateScheduleDatetime($data['schedule_datetime']);
            if (!$datetimeValid) {
                $errors['schedule_datetime'] = $datetimeError;
                $valid = false;
            }
        }

        return [$valid, $errors];
    }
}
