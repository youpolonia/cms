<?php

namespace Includes\Controllers;

require_once __DIR__ . '/../../config.php';

use Core\ResponseHandler;
use PDO;
use PDOException;

/**
 * Company Controller
 * Handles company-related operations
 */
class CompanyController
{
    /**
     * Show company profile
     *
     * @param string $slug Company slug
     */
    /**
     * Show company profile
     *
     * @param string $slug Company slug
     * @return void
     */
    public function show(string $slug): void
    {
        // Get company data from database
        $company = $this->getCompanyBySlug($slug);
        
        try {
            if (!$company) {
                ResponseHandler::error('Company not found', 404);
                return;
            }

            // Render company profile template
            require_once 'templates/company_profile.php';
        } catch (PDOException $e) {
            error_log('Company error: ' . $e->getMessage());
            ResponseHandler::error('Failed to load company', 500);
        }
    }

    /**
     * Get company data by slug
     *
     * @param string $slug Company slug
     * @return array|null Company data or null if not found
     */
    /**
     * Get company data by slug
     *
     * @param string $slug Company slug
     * @return array|null Company data or null if not found
     * @throws PDOException On database error
     */
    private function getCompanyBySlug(string $slug): ?array
    {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("
            SELECT
                c.id,
                c.name,
                c.slug,
                c.description,
                c.website,
                m.path AS logo
            FROM companies c
            LEFT JOIN media m ON c.logo_id = m.id
            WHERE c.slug = :slug
            AND c.status = 'active'
            LIMIT 1
        ");
        
        $stmt->execute([':slug' => $slug]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($company) {
            // Format data for template
            $company['logo'] = $company['logo'] ? '/uploads/' . $company['logo'] : null;
        }

        return $company ?: null;
    }
}
