<?php
/**
 * Worker System Bootstrap File
 * 
 * @package CMS
 * @subpackage Admin\Workers
 */

declare(strict_types=1);

// Core database connection
require_once __DIR__ . '/../../includes/database/connection.php';

// Base worker model
require_once __DIR__ . '/../../models/worker.php';

// Extended worker models
require_once __DIR__ . '/../../models/humanworker.php';

// Authentication system
require_once __DIR__ . '/../../auth/authcontroller.php';
