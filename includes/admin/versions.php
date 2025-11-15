<?php
/**
 * Version Control Template
 * 
 * Interface for managing content versions
 * 
 * @package CMS
 * @subpackage Admin
 * @version 1.0.0
 */

?><div class="version-control">
    <h1>Version Control</h1>
    
    <div class="version-actions">
        <form class="filter-form">
            <select name="content_id">
                <option value="">All Content</option>
                <option value="1">Home Page</option>
                <option value="2">About Us</option>
                <option value="3">Contact</option>
            </select>
            <button type="submit">Filter</button>
        </form>
    </div>

    <table class="version-table">
        <thead>
            <tr>
                <th>Version ID</th>
                <th>Content</th>
                <th>Modified By</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>142</td>
                <td>Home Page</td>
                <td>admin</td>
                <td>2025-05-10 14:30</td>
                <td>
                    <a href="?section=versions&action=compare&id=142">Compare</a>
                    <a href="?section=versions&action=restore&id=142">Restore</a>
                </td>
            </tr>
            <tr>
                <td>141</td>
                <td>About Us</td>
                <td>editor</td>
                <td>2025-05-09 10:15</td>
                <td>
                    <a href="?section=versions&action=compare&id=141">Compare</a>
                    <a href="?section=versions&action=restore&id=141">Restore</a>
                </td>
            </tr>
            <tr>
                <td>140</td>
                <td>Contact</td>
                <td>admin</td>
                <td>2025-05-08 16:45</td>
                <td>
                    <a href="?section=versions&action=compare&id=140">Compare</a>
                    <a href="?section=versions&action=restore&id=140">Restore</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
