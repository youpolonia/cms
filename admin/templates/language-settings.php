<?php
require_once __DIR__ . '/../../includes/localization/languagemanager.php';
require_once __DIR__ . '/../../includes/localization/translator.php';

$languages = CMS\Localization\LanguageManager::getLanguages();
$currentLanguage = CMS\Localization\LanguageManager::getCurrentLanguage();

?><div class="language-settings">
    <h2><?= __('Language Settings') ?></h2>
    <div class="language-switcher">
        <h3><?= __('Current Language') ?></h3>
        <select id="language-selector" class="form-control">
            <?php foreach ($languages as $code => $lang): ?>                <option value="<?= $code ?>" <?= $code === $currentLanguage ? 'selected' : '' ?>>
                    <?= $lang['name'] ?> (<?= $code ?>)
                </option>
            <?php endforeach;  ?>
        </select>
    </div>

    <div class="language-management">
        <h3><?= __('Manage Languages') ?></h3>
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('Code') ?></th>
                    <th><?= __('Name') ?></th>
                    <th><?= __('Locale') ?></th>
                    <th><?= __('Default') ?></th>
                    <th><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($languages as $code => $lang): ?>
                    <tr>
                        <td><?= $code ?></td>
                        <td><?= $lang['name'] ?></td>
                        <td><?= $lang['locale'] ?></td>
                        <td><?= $lang['is_default'] ? __('Yes') : __('No') ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-language" data-code="<?= $code ?>">
                                <?= __('Edit')  ?>
                            </button>
                            <?php if (!$lang['is_default']): ?>
                                <button class="btn btn-sm btn-danger delete-language" data-code="<?= $code ?>">
                                    <?= __('Delete')  ?>
                                </button>
                            <?php endif;  ?>
                        </td>
                    </tr>
                <?php endforeach;  ?>
            </tbody>
        </table>
        <button id="add-language" class="btn btn-success"><?= __('Add New Language') ?></button>
    </div>

    <div id="language-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= __('Language Settings') ?></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="language-form">
                        <input type="hidden" id="language-code">
                        <div class="form-group">
                            <label for="language-name"><?= __('Language Name') ?></label>
                            <input type="text" class="form-control" id="language-name" required>                        </div>
                        <div class="form-group">
                            <label for="language-locale"><?= __('Locale') ?></label>
                            <input type="text" class="form-control" id="language-locale" required>
?>                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="language-default">
                            <label class="form-check-label" for="language-default"><?= __('Set as default language') ?></label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= __('Cancel') ?></button>
                    <button type="button" class="btn btn-primary" id="save-language"><?= __('Save') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
