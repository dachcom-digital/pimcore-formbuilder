<div class="configWindow">

    <div class="--row">

        <div class="col-xs-6">

            <div class="form-group">
                <label for="form"><?= $this->translateAdmin('form') ?></label><br>
                <?= $this->select('formName', ['width' => '300', 'class' => 'form-control', 'placeholder' => $this->translateAdmin('form'), 'id' => 'formName', 'store' => $this->availableForms]) ?>
            </div>

        </div>

        <div class="col-xs-6">

            <div class="form-group">
                <label for="formType"><?= $this->translateAdmin('form type') ?></label><br>
                <?= $this->select('formType', ['width' => '300', 'class' => 'form-control', 'placeholder' => $this->translateAdmin('form'), 'id' => 'formType', 'store' => $this->availableFormTypes]) ?>
            </div>

        </div>

        <div class="col-xs-6">

            <div class="form-group">
                <label for="userCopy"><?= $this->translateAdmin('send copy to user') ?></label><br>
                <?= $this->checkbox('userCopy', ['width' => '300', 'class' => 'form-control', 'id' => 'userCopy']) ?>
            </div>

        </div>

        <div class="col-xs-6">

            <div class="form-group">
                <label for="sendMailTemplate"><?= $this->translateAdmin('mail template') ?></label><br>
                <?= $this->href('sendMailTemplate', ['width' => '300', 'class' => 'form-control', 'id' => 'sendMailTemplate']) ?>
            </div>


        </div>


    </div>

</div>