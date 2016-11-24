<div class="row">
    <div class="col-xs-12">
        <div class="alert alert-<?= $this->valid ? 'success' : 'danger'; ?>">

            <?php foreach( $this->messages as $message ) { ?>
                <?= $message; ?><br>
            <?php } ?>

        </div>
    </div>
</div>