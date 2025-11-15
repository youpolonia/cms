<?php
// Check admin access
require_once __DIR__ . '/../../auth/authcontroller.php';
$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: /admin/login');
    exit;
}

require_once __DIR__ . '/../../models/client.php';
require_once __DIR__ . '/../../includes/database/connection.php';

$connection = new Connection();
$clientModel = new Client($connection);

$clients = $clientModel->getAll();

// Prepare view
$title = 'Manage Clients';
ob_start();
?><h2>Manage Clients</h2>

<div class="client-controls">
    <a href="/admin/clients/create" class="button">Add New Client</a>
    <div class="search-box">
        <input type="text" id="clientSearch" placeholder="Search clients...">
    </div>
</div>

<table class="data-table" id="clientsTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clients as $client): ?>
        <tr data-client-id="<?= $client['id'] ?>">
            <td><?= htmlspecialchars($client['id']) ?></td>
            <td><?= htmlspecialchars($client['name']) ?></td>
            <td><?= htmlspecialchars($client['email'] ?? '') ?></td>
            <td><?= htmlspecialchars($client['phone'] ?? '') ?></td>
            <td>
                <select class="status-select" data-client-id="<?= $client['id'] ?>">
                    <option value="active" <?= $client['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $client['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="pending" <?= $client['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </td>
            <td class="actions">
                <a href="/admin/clients/edit?client_id=<?= $client['id'] ?>" class="button">Edit</a>
                <button class="button danger delete-btn" data-client-id="<?= $client['id'] ?>">Delete</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div id="deleteModal" class="modal" style="display:none;">
    <div class="modal-content">
        <p>Are you sure you want to delete this client?</p>
        <div class="modal-actions">
            <button id="confirmDelete" class="button danger">Delete</button>
            <button id="cancelDelete" class="button">Cancel</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('clientSearch');
    const tableRows = document.querySelectorAll('#clientsTable tbody tr');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        tableRows.forEach(row => {
            const name = row.cells[1].textContent.toLowerCase();
            const email = row.cells[2].textContent.toLowerCase();
            const phone = row.cells[3].textContent.toLowerCase();
            
            if (name.includes(searchTerm) || email.includes(searchTerm) || phone.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Status change handler
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            const clientId = this.dataset.clientId;
            const newStatus = this.value;
            
            fetch('/admin/clients/update_status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
                },
                body: JSON.stringify({
                    client_id: clientId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Failed to update status');
                    // Revert to previous value
                    this.value = this.dataset.previousValue;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.value = this.dataset.previousValue;
            });
            
            // Store previous value in case of failure
            this.dataset.previousValue = this.value;
        });
    });

    // Delete confirmation modal
    let currentClientIdToDelete = null;
    const deleteModal = document.getElementById('deleteModal');
    
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentClientIdToDelete = this.dataset.clientId;
            deleteModal.style.display = 'block';
        });
    });
    
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (currentClientIdToDelete) {
            fetch('/admin/clients/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
                },
                body: JSON.stringify({
                    client_id: currentClientIdToDelete
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`tr[data-client-id="${currentClientIdToDelete}"]`).remove();
                } else {
                    alert('Failed to delete client');
                }
                deleteModal.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                deleteModal.style.display = 'none';
            });
        }
    });
    
    document.getElementById('cancelDelete').addEventListener('click', function() {
        deleteModal.style.display = 'none';
    });
});
</script>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 5px;
    max-width: 400px;
    width: 100%;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.client-controls {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    align-items: center;
}

.search-box input {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 250px;
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/layout.php';
