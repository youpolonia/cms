<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class UrlsController
{
    public function index(Request $request): void
    {
        $pdo = db();
        $stmt = $pdo->query("SELECT * FROM redirects ORDER BY created_at DESC");
        $redirects = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/urls/index', [
            'redirects' => $redirects,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function create(Request $request): void
    {
        render('admin/urls/form', [
            'redirect' => null,
            'action' => 'create'
        ]);
    }

    public function store(Request $request): void
    {
        $sourceUrl = trim($request->post('source_url', ''));
        $targetUrl = trim($request->post('target_url', ''));
        $statusCode = in_array((int)$request->post('status_code'), [301, 302, 307, 308]) ? (int)$request->post('status_code') : 301;
        $isActive = $request->post('is_active') ? 1 : 0;

        if (empty($sourceUrl)) {
            Session::flash('error', 'Source URL is required.');
            Response::redirect('/admin/urls/create');
        }

        if (empty($targetUrl)) {
            Session::flash('error', 'Target URL is required.');
            Response::redirect('/admin/urls/create');
        }

        // Normalize source URL
        $sourceUrl = '/' . ltrim($sourceUrl, '/');

        $pdo = db();

        $stmt = $pdo->prepare("SELECT id FROM redirects WHERE source_url = ?");
        $stmt->execute([$sourceUrl]);
        if ($stmt->fetch()) {
            Session::flash('error', 'A redirect for this source URL already exists.');
            Response::redirect('/admin/urls/create');
        }

        $stmt = $pdo->prepare("INSERT INTO redirects (source_url, target_url, status_code, is_active, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$sourceUrl, $targetUrl, $statusCode, $isActive]);

        Session::flash('success', 'Redirect created successfully.');
        Response::redirect('/admin/urls');
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $redirect = $this->findRedirect($id);

        if (!$redirect) {
            Session::flash('error', 'Redirect not found.');
            Response::redirect('/admin/urls');
        }

        render('admin/urls/form', [
            'redirect' => $redirect,
            'action' => 'edit'
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $redirect = $this->findRedirect($id);

        if (!$redirect) {
            Session::flash('error', 'Redirect not found.');
            Response::redirect('/admin/urls');
        }

        $sourceUrl = trim($request->post('source_url', ''));
        $targetUrl = trim($request->post('target_url', ''));
        $statusCode = in_array((int)$request->post('status_code'), [301, 302, 307, 308]) ? (int)$request->post('status_code') : 301;
        $isActive = $request->post('is_active') ? 1 : 0;

        if (empty($sourceUrl) || empty($targetUrl)) {
            Session::flash('error', 'Source and target URLs are required.');
            Response::redirect("/admin/urls/{$id}/edit");
        }

        $sourceUrl = '/' . ltrim($sourceUrl, '/');

        $pdo = db();

        $stmt = $pdo->prepare("SELECT id FROM redirects WHERE source_url = ? AND id != ?");
        $stmt->execute([$sourceUrl, $id]);
        if ($stmt->fetch()) {
            Session::flash('error', 'A redirect for this source URL already exists.');
            Response::redirect("/admin/urls/{$id}/edit");
        }

        $stmt = $pdo->prepare("UPDATE redirects SET source_url = ?, target_url = ?, status_code = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$sourceUrl, $targetUrl, $statusCode, $isActive, $id]);

        Session::flash('success', 'Redirect updated successfully.');
        Response::redirect('/admin/urls');
    }

    public function toggle(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("UPDATE redirects SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Redirect status updated.');
        Response::redirect('/admin/urls');
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM redirects WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Redirect deleted successfully.');
        Response::redirect('/admin/urls');
    }

    private function findRedirect(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM redirects WHERE id = ?");
        $stmt->execute([$id]);
        $redirect = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $redirect ?: null;
    }
}
