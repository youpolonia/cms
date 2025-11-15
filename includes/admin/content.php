<?php
/**
 * Content Management Template
 * 
 * Interface for managing CMS content
 * 
 * @package CMS
 * @subpackage Admin
 * @version 1.0.0
 */

?><div class="content-management">
    <h1>Content Management</h1>
    
    <div class="content-actions">
        <a href="?section=content&action=create" class="button">Create New</a>
        <form class="search-form">
            <input type="text" name="search" placeholder="Search content...">
            <button type="submit">Search</button>
        </form>
    </div>

    <table class="content-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Type</th>
                <th>Last Modified</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Home Page</td>
                <td>Page</td>
                <td>2025-05-10 14:30</td>
                <td>
                    <a href="?section=content&action=edit&id=1">Edit</a>
                    <a href="?section=content&action=delete&id=1">Delete</a>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>About Us</td>
                <td>Page</td>
                <td>2025-05-09 10:15</td>
                <td>
                    <a href="?section=content&action=edit&id=2">Edit</a>
                    <a href="?section=content&action=delete&id=2">Delete</a>
                </td>
            </tr>
            <tr>
                <td>3</td>
                <td>Contact</td>
                <td>Page</td>
                <td>2025-05-08 16:45</td>
                <td>
                    <a href="?section=content&action=edit&id=3">Edit</a>
                    <a href="?section=content&action=delete&id=3">Delete</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
