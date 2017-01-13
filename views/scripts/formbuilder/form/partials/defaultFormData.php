<table>
    <?php foreach( $this->data as $label => $fieldData ) { ?>

        <tr>
            <td width="50%"><strong><?= $fieldData['label'] ?>:</strong></td>
            <td width="50%"><?= $fieldData['value']; ?></td>
        </tr>

    <?php } ?>
</table>