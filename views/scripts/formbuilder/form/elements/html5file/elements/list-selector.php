<ul class="qq-upload-list-selector qq-upload-list" aria-live="polite" aria-relevant="additions removals">
    <li class="clearfix">
        <div class="qq-progress-bar-container-selector">
            <div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-progress-bar-selector qq-progress-bar"></div>
        </div>
        <span class="qq-upload-spinner-selector qq-upload-spinner"></span>
        <img class="qq-thumbnail-selector" qq-max-size="100" qq-server-scale>
        <span class="qq-upload-file-selector qq-upload-file"></span>
        <span class="qq-edit-filename-wrapper">
            <span class="qq-edit-filename-icon-selector qq-edit-filename-icon" aria-label="<?= $this->translate('Edit filename'); ?>"></span>
            <input class="qq-edit-filename-selector qq-edit-filename" tabindex="0" type="text">
        </span>
        <span class="qq-upload-size-selector qq-upload-size"></span>
        <span class="qq-edit-buttons-wrapper">
            <button type="button" class="qq-btn qq-upload-cancel-selector qq-upload-cancel"><?= $this->translate('Cancel'); ?></button>
            <button type="button" class="qq-btn qq-upload-retry-selector qq-upload-retry"><?= $this->translate('Retry'); ?></button>
            <button type="button" class="qq-btn qq-upload-delete-selector qq-upload-delete"><?= $this->translate('Delete'); ?></button>
         </span>
        <span role="status" class="qq-upload-status-text-selector qq-upload-status-text"></span>
    </li>
</ul>