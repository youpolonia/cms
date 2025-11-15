/**
 * Localization JavaScript for CMS Admin Panel
 * Handles language switching and management
 */

document.addEventListener('DOMContentLoaded', function() {
    // Language selector change handler
    const languageSelector = document.getElementById('language-selector');
    if (languageSelector) {
        languageSelector.addEventListener('change', function() {
            const langCode = this.value;
            setLanguage(langCode);
        });
    }

    // Add new language button
    const addLanguageBtn = document.getElementById('add-language');
    if (addLanguageBtn) {
        addLanguageBtn.addEventListener('click', function() {
            showLanguageModal();
        });
    }

    // Edit language buttons
    document.querySelectorAll('.edit-language').forEach(btn => {
        btn.addEventListener('click', function() {
            const langCode = this.dataset.code;
            editLanguage(langCode);
        });
    });

    // Delete language buttons
    document.querySelectorAll('.delete-language').forEach(btn => {
        btn.addEventListener('click', function() {
            const langCode = this.dataset.code;
            deleteLanguage(langCode);
        });
    });

    // Save language button in modal
    const saveLanguageBtn = document.getElementById('save-language');
    if (saveLanguageBtn) {
        saveLanguageBtn.addEventListener('click', saveLanguage);
    }
});

/**
 * Set current language via AJAX
 */
function setLanguage(langCode) {
    fetch('/admin/api/set-language.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ language: langCode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to change language');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while changing language');
    });
}

/**
 * Show language modal for adding/editing
 */
function showLanguageModal(langData = null) {
    const modal = $('#language-modal');
    const form = document.getElementById('language-form');

    if (langData) {
        // Editing existing language
        document.getElementById('language-code').value = langData.code;
        document.getElementById('language-name').value = langData.name;
        document.getElementById('language-locale').value = langData.locale;
        document.getElementById('language-default').checked = langData.is_default;
    } else {
        // Adding new language
        form.reset();
        document.getElementById('language-code').value = '';
    }

    modal.modal('show');
}

/**
 * Edit existing language
 */
function editLanguage(langCode) {
    fetch(`/admin/api/get-language.php?code=${encodeURIComponent(langCode)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showLanguageModal(data.language);
            } else {
                alert(data.message || 'Failed to load language data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while loading language data');
        });
}

/**
 * Save language (add new or update existing)
 */
function saveLanguage() {
    const form = document.getElementById('language-form');
    const formData = {
        code: document.getElementById('language-code').value,
        name: document.getElementById('language-name').value,
        locale: document.getElementById('language-locale').value,
        is_default: document.getElementById('language-default').checked
    };

    const endpoint = formData.code ? '/admin/api/update-language.php' : '/admin/api/add-language.php';

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#language-modal').modal('hide');
            location.reload();
        } else {
            alert(data.message || 'Failed to save language');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving language');
    });
}

/**
 * Delete language confirmation and handling
 */
function deleteLanguage(langCode) {
    if (!confirm('Are you sure you want to delete this language? This cannot be undone.')) {
        return;
    }

    fetch('/admin/api/delete-language.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ code: langCode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to delete language');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting language');
    });
}