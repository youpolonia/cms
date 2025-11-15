<?php
require_once __DIR__ . '/../../../core/csrf.php';

namespace App\Controllers\Admin;

/**
 * @deprecated Since 2025-05-31 - Use api/controllers/ContentController.php instead
 * This controller will be removed in version 2.0.0
 */
class ContentController
{
    /**
     * Display a listing of the resource.
     *
     * @return void
     */
    public function index(): void
    {
        // In a real application, you would fetch content from a database here.
        // For now, we'll just load the view.
        $title = "Content Management"; // This can be passed to the view if needed.

        // The view will be responsible for its own layout integration.
        require_once __DIR__ . '/../../../../admin/content/index.php';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
     */
    public function create(): void
    {
        // The view will be responsible for its own layout integration.
        // It sets $title and $content internally.
        require_once __DIR__ . '/../../../../admin/content/create.php';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return void
     */
    public function store(): void
    {
        csrf_validate_or_403();
        // For now, just dump the POST data
        echo '<pre>';
        var_dump($_POST);
        echo '</pre>';

        // In a real application, you would validate and save the data.
        // Then redirect to the content list or show a success message.
        echo '<p>Content submitted successfully (simulated).</p>';
        echo '<a href="/admin/content">Back to Content List</a>';
        // Or, to redirect:
        // header('Location: /admin/content');
        // exit;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return void
     */
    public function edit(int $id): void
    {
        // In a real application, you would fetch the content item by $id from a database.
        // For now, we'll simulate it and pass the $id to the view.
        // The view (admin/content/edit.php) will use this $id to pre-fill the form
        // with placeholder data or fetch actual data if it were implemented.

        // Make $id available to the view
        // The view itself has placeholder data, but it can use this $id if needed.
        // No need to fetch full $contentItem here as the view handles placeholder logic for now.
        
        require_once __DIR__ . '/../../../../admin/content/edit.php';
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return void
     */
    public function update(int $id): void
    {
        csrf_validate_or_403();
        // For now, just dump the POST data and the ID
        echo '<h2>Update Action Called</h2>';
        echo '<p>Content ID to update: ' . htmlspecialchars($id) . '</p>';
        echo '<pre>';
        echo '$_POST data: <br>';
        var_dump($_POST);
        echo '</pre>';

        // In a real application, you would validate and update the data in the database.
        // Then redirect to the content list or show a success message.
        echo '<p>Content update submitted successfully (simulated).</p>';
        echo '<a href="/admin/content">Back to Content List</a>';
        // Or, to redirect:
        // header('Location: /admin/content');
        // exit;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return void
     */
    public function delete(int $id): void
    {
        csrf_validate_or_403();
        // In a real application, you would delete the content item from the database.
        // For now, we'll simulate it.
        // You might want to set a flash message here to inform the user.
        // For example, using a session-based flash message system.
        // Session::setFlash('success', "Content item with ID: {$id} would be deleted.");

        error_log("Simulated deletion of content item with ID: {$id}"); // Log for now

        // Redirect back to the content list
        header('Location: /admin/content');
        exit;
    }
}
