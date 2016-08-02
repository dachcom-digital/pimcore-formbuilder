<?=$this->template('formbuilder/email/layout/header.php'); ?>

<?=$this->wysiwyg('text'); ?>

<?php if( $this->getProperty('mail_disable_default_mail_body') !== TRUE) { ?>

    <?php if( !$this->editmode ) { ?>
        <p>%Text(body);</p>
    <?php } else { ?>
        <p class="formbuilder-placeholder-body"><?=$this->translate('Your email content will be placed here.') ?></p>
    <?php } ?>

<?php } ?>

<?=$this->template('formbuilder/email/layout/footer.php'); ?>