<?php if ($this->form) { ?>

    <div class="row">

        <?=$this->form;?>

    </div>

<?php } else { ?>

    <?= $this->translate('No form found'); ?>

<?php } ?>