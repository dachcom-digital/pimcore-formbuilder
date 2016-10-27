<div class="formbuilder-html5File">

    <div class="formbuilder-content" data-size-limit="<?= $this->sizeLimit; ?>" data-allowed-extensions="<?= implode(',', $this->allowedExtensions); ?>"></div>

    <div class="formbuilder-template">

        <div class="qq-uploader-selector qq-uploader" qq-drop-area-text="<?= $this->translate('Drop files here'); ?>">

            <div class="qq-total-progress-bar-container-selector qq-total-progress-bar-container">
                <div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-total-progress-bar-selector qq-progress-bar qq-total-progress-bar"></div>
            </div>
            <div class="qq-upload-drop-area-selector qq-upload-drop-area" qq-hide-dropzone>
                <span class="qq-upload-drop-area-text-selector"></span>
            </div>
            <div class="qq-upload-button-selector qq-upload-button">
                <div><?= $this->translate('Upload a file'); ?></div>
            </div>
            <span class="qq-drop-processing-selector qq-drop-processing">
                <span><?= $this->translate('Processing dropped files...'); ?></span>
                <span class="qq-drop-processing-spinner-selector qq-drop-processing-spinner"></span>
            </span>

            <ul class="qq-upload-list-selector qq-upload-list" aria-live="polite" aria-relevant="additions removals">
                <li>
                    <div class="qq-progress-bar-container-selector">
                        <div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-progress-bar-selector qq-progress-bar"></div>
                    </div>
                    <span class="qq-upload-spinner-selector qq-upload-spinner"></span>
                    <img class="qq-thumbnail-selector" qq-max-size="100" qq-server-scale>
                    <span class="qq-upload-file-selector qq-upload-file"></span>
                    <span class="qq-edit-filename-icon-selector qq-edit-filename-icon" aria-label="<?= $this->translate('Edit filename'); ?>"></span>
                    <input class="qq-edit-filename-selector qq-edit-filename" tabindex="0" type="text">
                    <span class="qq-upload-size-selector qq-upload-size"></span>
                    <button type="button" class="qq-btn qq-upload-cancel-selector qq-upload-cancel"><?= $this->translate('Cancel'); ?></button>
                    <button type="button" class="qq-btn qq-upload-retry-selector qq-upload-retry"><?= $this->translate('Retry'); ?></button>
                    <button type="button" class="qq-btn qq-upload-delete-selector qq-upload-delete"><?= $this->translate('Delete'); ?></button>
                    <span role="status" class="qq-upload-status-text-selector qq-upload-status-text"></span>
                </li>
            </ul>

            <input type="hidden" name="js-messages" value="<?= htmlspecialchars( json_encode( $this->message ) ); ?>">

            <?= $this->partial('formbuilder/form/elements/html5file/dialog/alert.php'); ?>
            <?= $this->partial('formbuilder/form/elements/html5file/dialog/confirm.php'); ?>
            <?= $this->partial('formbuilder/form/elements/html5file/dialog/prompt.php'); ?>

        </div>

    </div>

</div>