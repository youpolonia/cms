<?php
/**
 * Robots.txt Configuration
 * 
 * Provides centralized management of robots.txt rules with helper functions
 */

if (!defined('ROBOTS_CONFIG_LOADED')) {
    define('ROBOTS_CONFIG_LOADED', true);

    /**
     * Default robots.txt rules
     * @var array
     */
    $robotsRules = [
        '*' => [
            'User-agent' => '*',
            'Disallow' => [
                '/admin/',
                '/includes/',
                '/config/'
            ],
            'Allow' => [
                '/public/',
                '/assets/'
            ],
            'Sitemap' => '/sitemap.xml'
        ],
        'crawlers' => [
            'User-agent' => 'Googlebot',
            'Disallow' => [],
            'Crawl-delay' => 10
        ]
    ];

    /**
     * Get all robots.txt rules
     * @return array
     */
    function getRobotsRules(): array {
        global $robotsRules;
        return $robotsRules;
    }

    /**
     * Set robots.txt rules
     * @param array $rules New rules to set
     */
    function setRobotsRules(array $rules): void {
        global $robotsRules;
        $robotsRules = $rules;
    }

    /**
     * Add a rule for specific user agent
     * @param string $userAgent
     * @param array $directives
     */
    function addRobotsRule(string $userAgent, array $directives): void {
        global $robotsRules;
        $robotsRules[$userAgent] = $directives;
    }

    /**
     * Remove rule for specific user agent
     * @param string $userAgent
     */
    function removeRobotsRule(string $userAgent): void {
        global $robotsRules;
        unset($robotsRules[$userAgent]);
    }
}
