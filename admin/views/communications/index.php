<?php
/** @var array $threads */
/** @var array $pagination */
?>
<div class="container">
    <h1>Messages</h1>
    <a href="/admin/communications/create" class="btn btn-primary mb-3">New Message</a>
    
    <!-- Filters -->
    <form id="threadFilters" class="mb-4">
        <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="row">
            <div class="col-md-3">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date_from" class="form-control" placeholder="From Date">
            </div>
            <div class="col-md-3">
                <input type="date" name="date_to" class="form-control" placeholder="To Date">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Thread List -->
    <div id="threadList" class="list-group">
        <?php if (empty($threads)): ?>
            <div class="alert alert-info">No messages found</div>
        <?php else: ?>            <?php foreach ($threads as $thread): ?>
                <a href="/admin/communications/thread/<?= htmlspecialchars($thread['id']) ?>"
                   class="list-group-item list-group-item-action">
?>                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?= htmlspecialchars($thread['subject']) ?></h5>
                        <small><?= date('M j, Y g:i a', strtotime($thread['last_message_date'])) ?></small>
                    </div>
                    <p class="mb-1"><?= htmlspecialchars(substr($thread['last_message'], 0, 100)) ?>...</p>
                </a>
            <?php endforeach;  ?>        <?php endif;  ?>
    </div>

    <!-- Pagination -->
    <nav id="threadPagination" class="mt-4">
        <ul class="pagination">
            <?php if ($pagination['page'] > 1): ?>
                <li class="page-item"><a class="page-link" href="#" data-page="<?= $pagination['page'] - 1 ?>">Previous</a></li>
            <?php endif;  ?>            
            <?php for ($i = 1; $i <= $pagination['totalPages']; $i++):  ?>
                <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor;  ?>            
            <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                <li class="page-item"><a class="page-link" href="#" data-page="<?= $pagination['page'] + 1 ?>">Next</a></li>
            <?php endif;  ?>
        </ul>
    </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const threadList = document.getElementById('threadList');
    const filtersForm = document.getElementById('threadFilters');
    const pagination = document.getElementById('threadPagination');

    // Handle filter form submission
    filtersForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loadThreads(1);
    });

    // Handle pagination clicks
    pagination.addEventListener('click', function(e) {
        if (e.target.classList.contains('page-link')) {
            e.preventDefault();
            loadThreads(parseInt(e.target.dataset.page));
        }
    });

    // AJAX thread loading
    function loadThreads(page) {
        const formData = new FormData(filtersForm);
        formData.append('page', page);

        fetch(window.location.href, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            // Update thread list
            if (data.threads.length === 0) {
                threadList.innerHTML = '
<div class="alert alert-info">No messages found</div>';
            }
 else {
                threadList.innerHTML = data.threads.map(thread => `
                    <a href="/admin/communications/thread/${thread.id}"
                       class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">${escapeHtml(thread.subject)}</h5>
                            <small>${formatDate(thread.last_message_date)}</small>
                        </div>
                        <p class="mb-1">${escapeHtml(substr(thread.last_message, 0, 100))}...</p>
                    </a>
                `).join('');
            }

            // Update pagination
            updatePagination(data.pagination);
        });
    }

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&")
            .replace(/</g, "<")
            .replace(/>/g, ">")
            .replace(/"/g, """)
            .replace(/'/g, "&#039;");
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    }

    function updatePagination(pagination) {
        let html = '';
        
        if (pagination.page > 1) {
            html += `
<li class="page-item"><a class="page-link" href="#" data-page="${pagination.page - 1}">Previous</a></li>`;
        }
        
        for (let i = 1; i <= pagination.totalPages; i++) {
            html += `
<li class="page-item ${i == pagination.page ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        }
        
        if (pagination.page < pagination.totalPages) {
            html += `
<li class="page-item"><a class="page-link" href="#" data-page="${pagination.page + 1}">Next</a></li>`;
        }
        
        pagination.querySelector('ul').innerHTML = html;
    }
});
</script>
