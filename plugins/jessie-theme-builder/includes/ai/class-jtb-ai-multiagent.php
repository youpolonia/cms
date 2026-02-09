<?php
/**
 * JTB AI Multi-Agent Orchestrator
 *
 * Manages the multi-agent website generation flow:
 * 1. Mockup phase - quick HTML preview for user approval
 * 2. Build phase - convert approved mockup to JTB JSON
 *
 * FIXED 2026-02-06: Using file-based session storage instead of $_SESSION
 * to persist data across HTTP requests (REST API pattern)
 *
 * @package JessieThemeBuilder
 * @since 2.0.0
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_MultiAgent
{
    /**
     * Session storage directory
     */
    private const SESSION_DIR = '/tmp/jtb_sessions';

    /**
     * Session expiry in seconds (30 minutes)
     */
    private const SESSION_EXPIRY = 1800;

    // =========================================================================
    // SESSION MANAGEMENT (FILE-BASED)
    // =========================================================================

    /**
     * Ensure session directory exists
     */
    private static function ensureSessionDir(): void
    {
        if (!is_dir(self::SESSION_DIR)) {
            mkdir(self::SESSION_DIR, 0777, true);
            chmod(self::SESSION_DIR, 0777);
        }
    }

    /**
     * Get session file path
     */
    private static function getSessionPath(string $sessionId): string
    {
        self::ensureSessionDir();
        return self::SESSION_DIR . '/' . $sessionId . '.json';
    }

    /**
     * Save session to file
     */
    private static function saveSession(array $session): void
    {
        $path = self::getSessionPath($session['id']);
        $json = json_encode($session, JSON_PRETTY_PRINT);
        file_put_contents($path, $json, LOCK_EX);
        chmod($path, 0666);
    }

    /**
     * Get session from file
     */
    public static function getSession(string $sessionId): ?array
    {
        $path = self::getSessionPath($sessionId);
        
        if (!file_exists($path)) {
            return null;
        }

        $json = file_get_contents($path);
        $session = json_decode($json, true);

        if (!$session) {
            return null;
        }

        // Check expiry
        if (time() > ($session['expires_at'] ?? 0)) {
            unlink($path);
            return null;
        }

        return $session;
    }

    /**
     * Update session with new data
     */
    public static function updateSession(string $sessionId, array $updates): bool
    {
        $session = self::getSession($sessionId);
        if (!$session) {
            return false;
        }

        foreach ($updates as $key => $value) {
            if ($key === 'stats' && is_array($value)) {
                $session['stats'] = array_merge($session['stats'] ?? [], $value);
            } else {
                $session[$key] = $value;
            }
        }

        // Extend expiry on activity
        $session['expires_at'] = time() + self::SESSION_EXPIRY;

        self::saveSession($session);
        return true;
    }

    /**
     * Delete session
     */
    public static function deleteSession(string $sessionId): void
    {
        $path = self::getSessionPath($sessionId);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Start a new multi-agent session
     */
    public static function startSession(string $prompt, array $options = []): array
    {
        $sessionId = 'ma_' . bin2hex(random_bytes(8));

        // Detect industry from prompt if not specified
        $industry = $options['industry'] ?? 'general';
        if ($industry === 'auto' || empty($industry)) {
            $industry = self::detectIndustry($prompt);
        }

        // Build steps list based on pages
        $pages = $options['pages'] ?? ['home', 'about', 'services', 'contact'];
        $steps = self::buildStepsList($pages);

        $session = [
            'id' => $sessionId,
            'created_at' => time(),
            'expires_at' => time() + self::SESSION_EXPIRY,
            'status' => 'initialized',
            'phase' => 'mockup',

            // User input
            'prompt' => $prompt,
            'industry' => $industry,
            'style' => $options['style'] ?? 'modern',
            'pages' => $pages,
            'options' => $options,

            // AI configuration
            'ai_provider' => $options['ai_provider'] ?? null,
            'ai_model' => $options['ai_model'] ?? null,
            'language' => $options['language'] ?? '',

            // Steps for build phase
            'steps' => $steps,
            'current_step_index' => 0,

            // Mockup phase output
            'mockup_html' => null,
            'structure' => null,
            'mockup_iterations' => [],

            // Build phase outputs
            'skeleton' => null,
            'path_map' => null,
            'color_scheme' => [],
            'content' => [],
            'styles' => [],
            'seo' => [],
            'images' => [],

            // Stats
            'stats' => [
                'total_time_ms' => 0,
                'total_tokens' => 0,
                'steps_completed' => 0,
                'mockup_iterations' => 0
            ]
        ];

        self::saveSession($session);

        return [
            'ok' => true,
            'session_id' => $sessionId,
            'industry' => $industry,
            'style' => $session['style'],
            'pages' => $pages,
            'total_steps' => count($steps),
            'steps' => $steps
        ];
    }

    // =========================================================================
    // MOCKUP PHASE
    // =========================================================================

    /**
     * Generate HTML mockup
     */
    public static function generateMockup(string $sessionId): array
    {
        $session = self::getSession($sessionId);
        if (!$session) {
            return ['ok' => false, 'error' => 'Session not found or expired'];
        }

        $startTime = microtime(true);

        // Call Mockup Agent
        $result = JTB_AI_Agent_Mockup::generate($session);

        if (!$result['ok']) {
            return $result;
        }

        // Update session
        $timeMs = (int)((microtime(true) - $startTime) * 1000);

        self::updateSession($sessionId, [
            'status' => 'mockup_ready',
            'mockup_html' => $result['mockup_html'],
            'structure' => $result['structure'] ?? [],
            'stats' => [
                'total_time_ms' => ($session['stats']['total_time_ms'] ?? 0) + $timeMs,
                'total_tokens' => ($session['stats']['total_tokens'] ?? 0) + ($result['tokens_used'] ?? 0)
            ]
        ]);

        return [
            'ok' => true,
            'session_id' => $sessionId,
            'mockup_html' => $result['mockup_html'],
            'structure' => $result['structure'] ?? [],
            'stats' => [
                'time_ms' => $timeMs,
                'tokens_used' => $result['tokens_used'] ?? 0
            ]
        ];
    }

    /**
     * Iterate on mockup
     */
    public static function iterateMockup(string $sessionId, string $instruction): array
    {
        $session = self::getSession($sessionId);
        if (!$session) {
            return ['ok' => false, 'error' => 'Session not found or expired'];
        }

        if (!$session['mockup_html']) {
            return ['ok' => false, 'error' => 'No mockup to iterate on'];
        }

        $startTime = microtime(true);
        $result = JTB_AI_Agent_Mockup::iterate($session, $instruction);

        if (!$result['ok']) {
            return $result;
        }

        $timeMs = (int)((microtime(true) - $startTime) * 1000);
        $iterations = $session['mockup_iterations'] ?? [];
        $iterations[] = ['instruction' => $instruction, 'timestamp' => time()];

        self::updateSession($sessionId, [
            'mockup_html' => $result['mockup_html'],
            'structure' => $result['structure'] ?? $session['structure'],
            'mockup_iterations' => $iterations,
            'stats' => [
                'total_time_ms' => ($session['stats']['total_time_ms'] ?? 0) + $timeMs,
                'total_tokens' => ($session['stats']['total_tokens'] ?? 0) + ($result['tokens_used'] ?? 0),
                'mockup_iterations' => count($iterations)
            ]
        ]);

        return [
            'ok' => true,
            'session_id' => $sessionId,
            'mockup_html' => $result['mockup_html'],
            'iteration_count' => count($iterations)
        ];
    }

    /**
     * Accept mockup and transition to build phase
     */
    public static function acceptMockup(string $sessionId): array
    {
        $session = self::getSession($sessionId);
        if (!$session) {
            return ['ok' => false, 'error' => 'Session not found or expired'];
        }

        if (empty($session['mockup_html'])) {
            return ['ok' => false, 'error' => 'No mockup to accept. Generate mockup first.'];
        }

        // Rebuild steps based on pages
        $pages = $session['pages'] ?? ['home', 'about', 'services', 'contact'];
        $steps = self::buildStepsList($pages);

        self::updateSession($sessionId, [
            'status' => 'mockup_accepted',
            'phase' => 'build',
            'steps' => $steps,
            'current_step_index' => 0
        ]);

        return [
            'ok' => true,
            'session_id' => $sessionId,
            'phase' => 'build',
            'total_steps' => count($steps),
            'steps' => $steps
        ];
    }

    // =========================================================================
    // BUILD PHASE
    // =========================================================================

    /**
     * Run a specific build step
     */
    public static function runBuildStep(string $sessionId, string $step, ?string $page = null): array
    {
        // Acquire exclusive lock to prevent race conditions
        $lockFile = self::getSessionPath($sessionId) . '.lock';
        $lockHandle = fopen($lockFile, 'c');
        if (!flock($lockHandle, LOCK_EX)) {
            fclose($lockHandle);
            return ['ok' => false, 'error' => 'Could not acquire session lock'];
        }
        
        $session = self::getSession($sessionId);
        if (!$session) {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
            return ['ok' => false, 'error' => 'Session not found or expired'];
        }

        if ($session['phase'] !== 'build') {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
            return ['ok' => false, 'error' => 'Session not in build phase'];
        }

        $startTime = microtime(true);
        $result = ['ok' => false, 'error' => 'Unknown step'];

        try {
            switch ($step) {
                case 'architect':
                    $result = self::runArchitect($session);
                    break;
                case 'content':
                    $result = self::runContent($session, $page ?? 'header_footer');
                    break;
                case 'stylist':
                    $result = self::runStylist($session);
                    break;
                case 'seo':
                    $result = self::runSeo($session);
                    break;
                case 'images':
                    $result = self::runImages($session);
                    break;
                case 'assemble':
                    $result = self::assemble($session);
                    break;
            }

            $timeMs = (int)((microtime(true) - $startTime) * 1000);

            if ($result['ok']) {
                self::updateSession($sessionId, [
                    'stats' => [
                        'total_time_ms' => ($session['stats']['total_time_ms'] ?? 0) + $timeMs,
                        'total_tokens' => ($session['stats']['total_tokens'] ?? 0) + ($result['tokens_used'] ?? 0),
                        'steps_completed' => ($session['stats']['steps_completed'] ?? 0) + 1
                    ]
                ]);
            }

            $result['stats'] = ['time_ms' => $timeMs];
            return $result;
            
        } finally {
            // Always release session lock
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    /**
     * Run Architect agent - parse mockup HTML to JTB skeleton
     */
    private static function runArchitect(array $session): array
    {
        if (empty($session['mockup_html'])) {
            return ['ok' => false, 'error' => 'No mockup HTML to architect'];
        }

        $result = JTB_AI_Agent_Architect::execute($session);

        if (!$result['ok']) {
            return $result;
        }

        self::updateSession($session['id'], [
            'status' => 'architect_done',
            'skeleton' => $result['skeleton'] ?? [],
            'path_map' => $result['path_map'] ?? [],
            'color_scheme' => $result['color_scheme'] ?? []
        ]);

        return [
            'ok' => true,
            'summary' => $result['stats'] ?? [],
            'tokens_used' => 0
        ];
    }

    /**
     * Run Content agent for a page
     */
    private static function runContent(array $session, string $page): array
    {
        // Refresh session
        $session = self::getSession($session['id']);

        if (empty($session['skeleton']) || empty($session['path_map'])) {
            return ['ok' => false, 'error' => 'Architect step must run first'];
        }

        $result = JTB_AI_Agent_Content::executeForPage($session, $page);

        if (!$result['ok']) {
            return $result;
        }

        $existingContent = $session['content'] ?? [];
        $mergedContent = array_merge($existingContent, $result['content'] ?? []);

        self::updateSession($session['id'], [
            'content' => $mergedContent
        ]);

        return [
            'ok' => true,
            'tokens_used' => $result['tokens_used'] ?? 0
        ];
    }

    /**
     * Run Stylist agent
     */
    private static function runStylist(array $session): array
    {
        $session = self::getSession($session['id']);

        if (empty($session['path_map'])) {
            return ['ok' => false, 'error' => 'No path_map found. Run architect step first.'];
        }

        $result = JTB_AI_Agent_Stylist::execute($session);

        if (!$result['ok']) {
            return $result;
        }

        self::updateSession($session['id'], [
            'styles' => $result['styles'] ?? []
        ]);

        return [
            'ok' => true,
            'tokens_used' => $result['tokens_used'] ?? 0
        ];
    }

    /**
     * Run SEO agent
     */
    private static function runSeo(array $session): array
    {
        $session = self::getSession($session['id']);

        $result = JTB_AI_Agent_SEO::execute($session);

        if (!$result['ok']) {
            return $result;
        }

        self::updateSession($session['id'], [
            'seo' => $result['seo'] ?? []
        ]);

        return [
            'ok' => true,
            'tokens_used' => $result['tokens_used'] ?? 0
        ];
    }

    /**
     * Run Images agent
     */
    private static function runImages(array $session): array
    {
        $session = self::getSession($session['id']);

        $result = JTB_AI_Agent_Images::execute($session);

        if (!$result['ok']) {
            return $result;
        }

        self::updateSession($session['id'], [
            'images' => $result['images'] ?? []
        ]);

        return [
            'ok' => true,
            'tokens_used' => $result['tokens_used'] ?? 0
        ];
    }

    /**
     * Assemble final website
     */
    private static function assemble(array $session): array
    {
        $session = self::getSession($session['id']);

        if (empty($session['skeleton'])) {
            return ['ok' => false, 'error' => 'No skeleton to assemble'];
        }

        $skeleton = $session['skeleton'];
        $pathMap = $session['path_map'] ?? [];
        $content = $session['content'] ?? [];
        $styles = $session['styles'] ?? [];
        $images = $session['images'] ?? [];
        $seo = $session['seo'] ?? [];

        // Merge all data into skeleton
        $website = self::mergeAllIntoSkeleton($skeleton, $pathMap, $content, $styles, $images);
        $website['seo'] = $seo;

        // Add theme settings
        if (!empty($session['color_scheme'])) {
            $website['theme_settings'] = [
                'colors' => $session['color_scheme'],
                'style' => $session['style'] ?? 'modern'
            ];
        }

        // Add metadata
        $website['_meta'] = [
            'generated_at' => date('c'),
            'generator' => 'JTB Multi-Agent AI',
            'industry' => $session['industry'] ?? 'general',
            'style' => $session['style'] ?? 'modern'
        ];

        self::updateSession($session['id'], [
            'status' => 'complete',
            'final_website' => $website
        ]);

        return [
            'ok' => true,
            'website' => $website,
            'stats' => $session['stats'] ?? []
        ];
    }

    /**
     * Merge all agent outputs into skeleton
     */
    private static function mergeAllIntoSkeleton(
        array $skeleton,
        array $pathMap,
        array $content,
        array $styles,
        array $images
    ): array {
        $idToPath = array_flip($pathMap);

        $merge = function(&$element) use ($content, $styles, $images, $idToPath, &$merge) {
            if (!is_array($element)) return;

            if (isset($element['id']) && isset($idToPath[$element['id']])) {
                $path = $idToPath[$element['id']];
                if (isset($content[$path])) {
                    $element['attrs'] = array_merge($element['attrs'] ?? [], $content[$path]);
                }
                if (isset($styles[$path])) {
                    $element['attrs'] = array_merge($element['attrs'] ?? [], $styles[$path]);
                }
                if (isset($images[$path]) || isset($images[$element['id']])) {
                    $imgData = $images[$path] ?? $images[$element['id']] ?? [];
                    $element['attrs'] = array_merge($element['attrs'] ?? [], $imgData);
                }
            }

            if (isset($element['children'])) {
                foreach ($element['children'] as &$child) {
                    $merge($child);
                }
            }
            if (isset($element['sections'])) {
                foreach ($element['sections'] as &$section) {
                    $merge($section);
                }
            }
        };

        if (isset($skeleton['header'])) $merge($skeleton['header']);
        if (isset($skeleton['footer'])) $merge($skeleton['footer']);
        if (isset($skeleton['pages'])) {
            foreach ($skeleton['pages'] as &$page) {
                $merge($page);
            }
        }

        return $skeleton;
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private static function buildStepsList(array $pages): array
    {
        $steps = ['architect', 'content:header_footer'];
        foreach ($pages as $page) {
            $steps[] = "content:{$page}";
        }
        $steps = array_merge($steps, ['stylist', 'seo', 'images', 'assemble']);
        return $steps;
    }

    private static function detectIndustry(string $prompt): string
    {
        $prompt = strtolower($prompt);
        $industries = [
            'legal' => ['law', 'lawyer', 'attorney', 'legal'],
            'healthcare' => ['health', 'medical', 'doctor', 'clinic', 'dental'],
            'technology' => ['tech', 'software', 'app', 'saas', 'startup'],
            'restaurant' => ['restaurant', 'food', 'cafe', 'catering'],
            'real_estate' => ['real estate', 'property', 'realty'],
            'fitness' => ['fitness', 'gym', 'workout', 'yoga'],
            'agency' => ['agency', 'marketing', 'creative'],
            'ecommerce' => ['shop', 'store', 'ecommerce', 'retail'],
            'education' => ['education', 'school', 'course', 'learning']
        ];

        foreach ($industries as $industry => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($prompt, $keyword) !== false) {
                    return $industry;
                }
            }
        }
        return 'general';
    }

    public static function cancelSession(string $sessionId): bool
    {
        $session = self::getSession($sessionId);
        if (!$session) return false;
        self::deleteSession($sessionId);
        return true;
    }

    public static function getAvailableModules(): array
    {
        try {
            $modules = [];
            foreach (JTB_Registry::all() as $slug => $className) {
                $instance = JTB_Registry::get($slug);
                if ($instance) {
                    $modules[$slug] = [
                        'name' => $instance->getName(),
                        'category' => $instance->category ?? 'content'
                    ];
                }
            }
            return $modules;
        } catch (\Exception $e) {
            return [];
        }
    }
}
