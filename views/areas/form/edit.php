<div class="form-config-window">

    <div class="row">

        <div class="col-xs-6">

            <div class="form-group">
                <label for="form"><?= $this->translateAdmin('form') ?></label><br>
                <?= $this->select('formName', ['width' => 240, 'store' => $this->availableForms]) ?>
            </div>

        </div>

        <div class="col-xs-6">

            <div class="form-group">
                <label for="formType"><?= $this->translateAdmin('form type') ?></label><br>
                <?= $this->select('formType', ['width' => 240, 'store' => $this->availableFormTypes]) ?>
            </div>

        </div>

    </div>

    <?php if( !empty( $this->availableFormPresets ) ) { ?>

        <div class="row">

            <div class="col-xs-6">

                <div class="form-group">
                    <label for="form"><?= $this->translateAdmin('form preset') ?></label><br>
                    <?= $this->select('formPreset', ['width' => 240, 'store' => $this->availableFormPresets]) ?>
                </div>

            </div>

        </div>

        <div class="row">

            <div class="col-xs-12">

                <div class="preview-fields clearfix">

                    <h5><?= $this->translateAdmin('Preset Info'); ?></h5>

                    <?php foreach( $this->formPresetsInfo as $formPresetPreview ) { ?>

                        <div class="preview-field" data-name="<?=  $formPresetPreview['presetName'] ?>">

                            <?php if( !empty( $formPresetPreview['description'] ) ) { ?>
                                <div class="description"><?= $formPresetPreview['description']; ?></div>
                            <?php } ?>

                        </div>

                    <?php } ?>

                </div>

            </div>

        </div>

    <?php }?>

    <div class="row">

        <div class="col-xs-12">

            <div class="form-group">
                <label for="sendMailTemplate"><?= $this->translateAdmin('mail template') ?></label><br>
                <?= $this->href('sendMailTemplate', ['width' => 505, 'types' => ['document'], 'subtypes' => [ 'document' => ['email'] ]]) ?>
            </div>

        </div>

    </div>

    <div class="row">

        <div class="col-xs-6">

            <div class="form-group">
                <label for="userCopy"><?= $this->translateAdmin('send copy to user') ?></label><br>
                <?= $this->checkbox('userCopy') ?>
            </div>

        </div>

        <div class="col-xs-6">

            <div class="form-group">
                <label for="sendCopyMailTemplate"><?= $this->translateAdmin('copy mail template') ?></label><br>
                <?= $this->href('sendCopyMailTemplate', ['width' => 240, 'types' => ['document'], 'subtypes' => [ 'document' => ['email'] ]]) ?>
            </div>

        </div>

    </div>

</div>