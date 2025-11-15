$(document).ready(function() {
    // Version management functionality
    const versionManager = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // View version details
            $(document).on('click', '.btn.view', this.showVersionDetails);
            
            // Restore version
            $(document).on('click', '.btn.restore', this.restoreVersion);
            
            // Toggle change notes
            $(document).on('click', '.toggle-notes', this.toggleChangeNotes);
        },

        showVersionDetails: function(e) {
            e.preventDefault();
            const versionId = $(this).data('version-id');
            
            $.ajax({
                url: '/api/v1/versions/' + versionId,
                method: 'GET',
                beforeSend: function() {
                    // Show loading state
                },
                success: function(response) {
                    if (response.success) {
                        versionManager.renderVersionDetails(response.data);
                    } else {
                        alert(response.message || 'Failed to load version details');
                    }
                },
                error: function() {
                    alert('Error loading version details');
                }
            });
        },

        renderVersionDetails: function(version) {
            const modal = $('#versionDetailsModal');
            
            // Populate modal content
            modal.find('.modal-title').text('Version ' + version.version_number);
            modal.find('.content-preview').html(version.content_preview);
            modal.find('.full-diff').html(version.diff_html || 'No changes detected');
            
            // Show modal
            modal.modal('show');
        },

        restoreVersion: function(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to restore this version?')) {
                return;
            }

            const versionId = $(this).data('version-id');
            
            $.ajax({
                url: '/api/v1/versions/' + versionId + '/restore',
                method: 'POST',
                beforeSend: function() {
                    // Show loading state
                },
                success: function(response) {
                    if (response.success) {
                        alert('Version restored successfully');
                        window.location.reload();
                    } else {
                        alert(response.message || 'Failed to restore version');
                    }
                },
                error: function() {
                    alert('Error restoring version');
                }
            });
        },

        toggleChangeNotes: function(e) {
            e.preventDefault();
            $(this).closest('.version-item').find('.change-notes').toggle();
        }
    };

    // Initialize version manager
    versionManager.init();
});