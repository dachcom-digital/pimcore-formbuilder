<?php if ($this->form) { ?>

    <div class="row">

        <div class="form-wrapper">
            <?=$this->form;?>
        </div>

    </div>

<?php } else { ?>

    <?= $this->translate('No form found'); ?>

<?php } ?>