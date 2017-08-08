<table>
    <?php foreach( $this->data as $label => $fieldData ) { ?>

        <tr>
            <td width="50%" valign="top"><strong><?= $fieldData['label'] ?>:</strong></td>
            <td width="50%" valign="top"><?= $fieldData['value']; ?></td>
        </tr>

    <?php } ?>
</table>