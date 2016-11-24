<div class="formbuilder-html5File">

    <?php /** NEVER (!) remove those data attributes! **/ ?>
    <div class="formbuilder-content" data-field-name="<?= $this->fieldName ?>" data-size-limit="<?= $this->sizeLimit; ?>" data-allowed-extensions="<?= implode(',', $this->allowedExtensions); ?>"></div>

    <div class="formbuilder-template">

        <div class="qq-uploader-selector qq-uploader" qq-drop-area-text="<?= $this->translate('Drop files here'); ?>">

            <?= $this->partial('formbuilder/form/elements/html5file/elements/total-progress-bar.php'); ?>
            <?= $this->partial('formbuilder/form/elements/html5file/elements/upload-drop-area-selector.php'); ?>
            <?= $this->partial('formbuilder/form/elements/html5file/elements/upload-button-selector.php'); ?>
            <?= $this->partial('formbuilder/form/elements/html5file/elements/list-selector.php'); ?>

            <?= $this->partial('formbuilder/form/elements/html5file/dialog/alert.php'); ?>
            <?= $this->partial('formbuilder/form/elements/html5file/dialog/confirm.php'); ?>
            <?= $this->partial('formbuilder/form/elements/html5file/dialog/prompt.php'); ?>

            <input type="hidden" name="js-messages" value="<?= htmlspecialchars( json_encode( $this->message ) ); ?>">
        </div>

    </div>

</div>