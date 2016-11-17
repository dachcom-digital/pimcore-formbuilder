<?php /** @var $this->file \Pimcore\Model\Asset */ ?>
<?php if( !is_null( $this->file ) ) { ?>
    <a class="form-builder-download-link icon-download-<?= $this->meta['fileExtension']; ?>" href="<?= $this->file->getRealFullPath(); ?>" target="_blank"><?= $this->meta['fileName']; ?> <span class="file-info"><span class="file-type"><?= $this->meta['fileExtension']; ?></span>, <?= $this->meta['fileSize']; ?></span></a>
<?php } ?>