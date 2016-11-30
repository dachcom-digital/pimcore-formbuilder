<?= $this->partial('/formbuilder/email/layout/header.php'); ?>

<?php if( $this->editmode ) { ?>

    <?php if( $this->getProperty('mail_disable_default_mail_body') !== TRUE) { ?>

        <div class="alert alert-info"><strong>Admin:</strong> <?=$this->translateAdmin('Form data will be rendered automatically.') ?></div>

    <?php } else { ?>

        <div class="alert alert-info"><strong>Admin:</strong> <?= sprintf( $this->translateAdmin('Custom style mode has been activated. Please use placeholder like %s to display form value fields.'), '<code>%Text(firstname);</code>'); ?></div>

    <?php } ?>

<?php } ?>

<table class="row">
    <tr>
        <th class="large-12 small-12 columns first last">
            <?=$this->wysiwyg('text'); ?>
        </th>
    </tr>
</table>

<?php if( $this->getProperty('mail_disable_default_mail_body') !== TRUE ) { ?>

    <table class="row">
        <tr>
            <th class="large-12 small-12 columns first last">
                <?php if( !$this->editmode ) { ?>
                    <p>%Text(body);</p>
                <?php } else { ?>
                    <p class="formbuilder-placeholder-body"><?= $this->translateAdmin('Your form content will be placed here.') ?></p>
                <?php } ?>
            </th>
        </tr>
    </table>

<?php } ?>

<?=$this->template('/formbuilder/email/layout/footer.php'); ?>