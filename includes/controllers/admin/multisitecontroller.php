<?php

namespace Includes\Controllers\Admin;

use Includes\Multisite\SiteManager;
use Includes\Routing\Request;
use Includes\Routing\Response;
use Includes\Auth\Auth;

/**
 * MultisiteController - Handles multi-site management in the admin panel
 */
class MultisiteController
{
    /**
     * @var SiteManager
     */
    private SiteManager $siteManager;
    
    /**
     * @var Auth
     */
    private Auth $auth;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->siteManager = new SiteManager();
        $this->auth = new Auth();
    }
    
    /**
     * Display list of sites
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        // Check if multisite is enabled
        if (!$this->siteManager->isMultisiteEnabled()) {
            return new Response('Multisite is not enabled', 403);
        }
        
        // Check permissions
        if (!$this->auth->hasPermission('multisite.view')) {
            return new Response('Permission denied', 403);
        }
        
        // Get all sites
        $sites = $this->siteManager->getAllSites();
        
        // Render view
        return new Response(render_theme_view('admin/multisite/index', [
            'sites' => $sites,
            'currentSite' => $this->siteManager->getCurrentSite()
        ]));
    }
    
    /**
     * Show site creation form
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        // Check if multisite is enabled
        if (!$this->siteManager->isMultisiteEnabled()) {
            return new Response('Multisite is not enabled', 403);
        }
        
        // Check permissions
        if (!$this->auth->hasPermission('multisite.create')) {
            return new Response('Permission denied', 403);
        }
        
        // Render view
        return new Response(render_theme_view('admin/multisite/create'));
    }
    
    /**
     * Store a new site
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // Check if multisite is enabled
        if (!$this->siteManager->isMultisiteEnabled()) {
            return new Response('Multisite is not enabled', 403);
        }
        
        // Check permissions
        if (!$this->auth->hasPermission('multisite.create')) {
            return new Response('Permission denied', 403);
        }
        
        // Validate request
        $siteId = $request->input('site_id');
        $domain = $request->input('domain');
        $theme = $request->input('theme');
        $storageLimit = $request->input('storage_limit');
        
        if (empty($siteId) || empty($domain) || empty($theme)) {
            return new Response('Missing required fields', 400);
        }
        
        // Check if site ID already exists
        if ($this->siteManager->siteExists($siteId)) {
            return new Response('Site ID already exists', 400);
        }
        
        // Create site
        $result = $this->siteManager->registerSite($siteId, [
            'domain' => $domain,
            'theme' => $theme,
            'storage_limit' => $storageLimit ?? '500MB'
        ]);
        
        if (!$result) {
            return new Response('Failed to create site', 500);
        }
        
        // Redirect to site list
        return new Response('', 302, ['Location' => '/admin/multisite']);
    }
    
    /**
     * Show site edit form
     *
     * @param Request $request
     * @param string $siteId
     * @return Response
     */
    public function edit(Request $request, string $siteId): Response
    {
        // Check if multisite is enabled
        if (!$this->siteManager->isMultisiteEnabled()) {
            return new Response('Multisite is not enabled', 403);
        }
        
        // Check permissions
        if (!$this->auth->hasPermission('multisite.edit')) {
            return new Response('Permission denied', 403);
        }
        
        // Check if site exists
        if (!$this->siteManager->siteExists($siteId)) {
            return new Response('Site not found', 404);
        }
        
        // Get site configuration
        $siteConfig = $this->siteManager->getSiteConfig($siteId);
        
        // Render view
        return new Response(render_theme_view('admin/multisite/edit', [
            'siteId' => $siteId,
            'siteConfig' => $siteConfig
        ]));
    }
    
    /**
     * Update a site
     *
     * @param Request $request
     * @param string $siteId
     * @return Response
     */
    public function update(Request $request, string $siteId): Response
    {
        // Check if multisite is enabled
        if (!$this->siteManager->isMultisiteEnabled()) {
            return new Response('Multisite is not enabled', 403);
        }
        
        // Check permissions
        if (!$this->auth->hasPermission('multisite.edit')) {
            return new Response('Permission denied', 403);
        }
        
        // Check if site exists
        if (!$this->siteManager->siteExists($siteId)) {
            return new Response('Site not found', 404);
        }
        
        // Validate request
        $domain = $request->input('domain');
        $theme = $request->input('theme');
        $storageLimit = $request->input('storage_limit');
        
        if (empty($domain) || empty($theme)) {
            return new Response('Missing required fields', 400);
        }
        
        // Update site configuration
        $result = $this->siteManager->updateSiteConfig($siteId, [
            'domain' => $domain,
            'theme' => $theme,
            'storage_limit' => $storageLimit
        ]);
        
        if (!$result) {
            return new Response('Failed to update site', 500);
        }
        
        // Redirect to site list
        return new Response('', 302, ['Location' => '/admin/multisite']);
    }
    
    /**
     * Delete a site
     *
     * @param Request $request
     * @param string $siteId
     * @return Response
     */
    public function delete(Request $request, string $siteId): Response
    {
        // Check if multisite is enabled
        if (!$this->siteManager->isMultisiteEnabled()) {
            return new Response('Multisite is not enabled', 403);
        }
        
        // Check permissions
        if (!$this->auth->hasPermission('multisite.delete')) {
            return new Response('Permission denied', 403);
        }
        
        // Check if site exists
        if (!$this->siteManager->siteExists($siteId)) {
            return new Response('Site not found', 404);
        }
        
        // Cannot delete default site
        if ($siteId === $this->siteManager->getDefaultSite()) {
            return new Response('Cannot delete default site', 400);
        }
        
        // Delete site
        $result = $this->siteManager->removeSite($siteId);
        
        if (!$result) {
            return new Response('Failed to delete site', 500);
        }
        
        // Redirect to site list
        return new Response('', 302, ['Location' => '/admin/multisite']);
    }
    
    /**
     * Switch to a different site
     *
     * @param Request $request
     * @param string $siteId
     * @return Response
     */
    public function switchSite(Request $request, string $siteId): Response
    {
        // Check if multisite is enabled
        if (!$this->siteManager->isMultisiteEnabled()) {
            return new Response('Multisite is not enabled', 403);
        }
        
        // Check permissions
        if (!$this->auth->hasPermission('multisite.switch')) {
            return new Response('Permission denied', 403);
        }
        
        // Check if site exists
        if (!$this->siteManager->siteExists($siteId)) {
            return new Response('Site not found', 404);
        }
        
        // Switch site
        $result = $this->siteManager->setCurrentSite($siteId);
        
        if (!$result) {
            return new Response('Failed to switch site', 500);
        }
        
        // Redirect to dashboard
        return new Response('', 302, ['Location' => '/admin/dashboard']);
    }
}
