<?php

namespace Includes\Controllers;

use Includes\Routing\Request;
use Includes\Routing\Response;
use Includes\Theme\Theme;
use Includes\Theme\TemplateInheritance;
use Includes\Multisite\SiteManager;

/**
 * PageController - Handles page rendering with template inheritance
 */
class PageController
{
    /**
     * @var Theme
     */
    private Theme $theme;
    
    /**
     * @var TemplateInheritance
     */
    private TemplateInheritance $templateEngine;
    
    /**
     * @var SiteManager|null
     */
    private ?SiteManager $siteManager = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->theme = new Theme();
        $this->templateEngine = new TemplateInheritance($this->theme);
        
        // Initialize site manager if multisite is enabled
        try {
            $this->siteManager = new SiteManager();
        } catch (\Exception $e) {
            // Multisite not enabled, continue without it
        }
    }
    
    /**
     * Render a page
     *
     * @param Request $request
     * @param string $slug
     * @return Response
     */
    public function show(Request $request, string $slug): Response
    {
        // Get page data (in a real implementation, this would come from a database)
        $page = $this->getPageData($slug);
        
        if (!$page) {
            return new Response('Page not found', 404);
        }
        
        try {
            // Render the page using template inheritance
            $content = $this->templateEngine->render('page', [
                'page' => $page,
                'request' => $request
            ]);
            
            return new Response($content);
        } catch (\Exception $e) {
            // Log error
            error_log('Template rendering error: ' . $e->getMessage());
            
            // Return error response
            return new Response('Error rendering page', 500);
        }
    }
    
    /**
     * Get page data
     *
     * @param string $slug
     * @return array|null
     */
    private function getPageData(string $slug): ?array
    {
        // In a real implementation, this would fetch data from a database
        // For this example, we'll return mock data
        
        $pages = [
            'home' => [
                'id' => 1,
                'title' => 'Home',
                'subtitle' => 'Welcome to our website',
                'content' => '
<p>This is the home page content.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
                'sidebar' => '<h3>Latest News</h3><ul><li>News item 1</li><li>News item 2</li></ul>'
            ],
            'about' => [
                'id' => 2,
                'title' => 'About Us',
                'subtitle' => 'Learn more about our company',
                'content' => '<p>This is the about page content.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
                'sidebar' => '<h3>Our Team</h3><ul><li>Team member 1</li><li>Team member 2</li></ul>'
            ],
            'contact' => [
                'id' => 3,
                'title' => 'Contact Us',
                'subtitle' => 'Get in touch with us',
                'content' => '<p>This is the contact page content.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
                'sidebar' => '<h3>Contact Information</h3><p>Email: info@example.com</p><p>Phone: 123-456-7890</p>'
            ]
        ];
        
        return $pages[$slug] ?? null;
    }
    
    /**
     * Set the active theme
     *
     * @param string $theme
     * @return void
     */
    public function setTheme(string $theme): void
    {
        $this->theme->setActiveTheme($theme);
    }
}
