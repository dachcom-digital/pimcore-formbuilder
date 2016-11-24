<?php if ($this->form) { ?>

    <?php if( !empty( $this->messages ) ) { ?>
        <?= $this->messages; ?>
    <?php } ?>

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