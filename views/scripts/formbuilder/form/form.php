<?php if ($this->form) { ?>

    <div class="row">

        <div class="form-wrapper">

            <div class="col-xs-12">
                <?=$this->form;?>
            </div>

        </div>

    </div>

<?php } else { ?>

    <?= $this->translate('No form found'); ?>

<?php } ?>