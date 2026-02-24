<?php
/**
 * Jessie Portfolio — Admin Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');

$pluginDir = CMS_ROOT . '/plugins/jessie-portfolio';
require_once $pluginDir . '/includes/class-portfolio-project.php';
require_once $pluginDir . '/includes/class-portfolio-category.php';
require_once $pluginDir . '/includes/class-portfolio-testimonial.php';

\Core\Session::requireRole('admin');

$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/admin/portfolio' || $uri === '/admin/portfolio/') { require $pluginDir . '/views/admin/dashboard.php'; exit; }

// ─── PROJECTS ───
if ($uri === '/admin/portfolio/projects') { require $pluginDir . '/views/admin/projects.php'; exit; }
if ($uri === '/admin/portfolio/projects/create') { $project = null; require $pluginDir . '/views/admin/project-form.php'; exit; }

if (preg_match('#^/admin/portfolio/projects/(\d+)/edit$#', $uri, $m)) {
    $project = \PortfolioProject::get((int)$m[1]);
    if (!$project) { \Core\Session::flash('error', 'Not found.'); \Core\Response::redirect('/admin/portfolio/projects'); }
    require $pluginDir . '/views/admin/project-form.php'; exit;
}

if ($uri === '/admin/portfolio/projects/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    $id = \PortfolioProject::create($data);
    \Core\Session::flash('success', 'Project created.'); \Core\Response::redirect('/admin/portfolio/projects/' . $id . '/edit');
}

if (preg_match('#^/admin/portfolio/projects/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \PortfolioProject::update((int)$m[1], $data);
    \Core\Session::flash('success', 'Project updated.'); \Core\Response::redirect('/admin/portfolio/projects/' . $m[1] . '/edit');
}

if (preg_match('#^/admin/portfolio/projects/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \PortfolioProject::delete((int)$m[1]);
    \Core\Session::flash('success', 'Project deleted.'); \Core\Response::redirect('/admin/portfolio/projects');
}

// ─── CATEGORIES ───
if ($uri === '/admin/portfolio/categories') { require $pluginDir . '/views/admin/categories.php'; exit; }

if ($uri === '/admin/portfolio/categories/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \PortfolioCategory::create($data);
    \Core\Session::flash('success', 'Category created.'); \Core\Response::redirect('/admin/portfolio/categories');
}

if (preg_match('#^/admin/portfolio/categories/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \PortfolioCategory::delete((int)$m[1]);
    \Core\Session::flash('success', 'Category deleted.'); \Core\Response::redirect('/admin/portfolio/categories');
}

// ─── TESTIMONIALS ───
if ($uri === '/admin/portfolio/testimonials') { require $pluginDir . '/views/admin/testimonials.php'; exit; }

if ($uri === '/admin/portfolio/testimonials/store' && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \PortfolioTestimonial::create($data);
    \Core\Session::flash('success', 'Testimonial created.'); \Core\Response::redirect('/admin/portfolio/testimonials');
}

if (preg_match('#^/admin/portfolio/testimonials/(\d+)/update$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    $data = $_POST; unset($data['csrf_token']);
    \PortfolioTestimonial::update((int)$m[1], $data);
    \Core\Session::flash('success', 'Testimonial updated.'); \Core\Response::redirect('/admin/portfolio/testimonials');
}

if (preg_match('#^/admin/portfolio/testimonials/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \PortfolioTestimonial::delete((int)$m[1]);
    \Core\Session::flash('success', 'Testimonial deleted.'); \Core\Response::redirect('/admin/portfolio/testimonials');
}

if (preg_match('#^/admin/portfolio/testimonials/(\d+)/approve$#', $uri, $m) && $method === 'POST') {
    csrf_validate_or_403();
    \PortfolioTestimonial::approve((int)$m[1]);
    \Core\Session::flash('success', 'Testimonial approved.'); \Core\Response::redirect('/admin/portfolio/testimonials');
}
