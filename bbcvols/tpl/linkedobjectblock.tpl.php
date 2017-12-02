<?php

global $user, $db;
global $noMoreLinkedObjectBlockAfter;

$langs = $GLOBALS['langs'];
$linkedObjectBlock = $GLOBALS['linkedObjectBlock'];

$langs->load("bills");

$total = 0;
$ilink = 0;
$var = true;


/**
 * @var int     $key
 * @var Bbcvols $flight
 */
foreach ($linkedObjectBlock as $key => $flight) {
    $ilink++;
    $var = !$var;
    $trclass = ($var ? 'pair' : 'impair');
    if ($ilink == count($linkedObjectBlock) && empty($noMoreLinkedObjectBlockAfter) && count($linkedObjectBlock) <= 1) {
        $trclass .= ' liste_sub_total';
    }
    ?>
    <tr <?php echo $GLOBALS['bc'][$var]; ?> >
        <td><?php echo $langs->trans("Vol"); ?></td>
        <td><?= $flight->getNomUrl()?></td>
        <td align="center"><?= sprintf("%s à %s ", $flight->lieuD, $flight->lieuA);?></td>
        <td align="center"><?= dol_print_date($flight->date, '%d-%m-%Y') ?></td>
        <td align="right"><?= $flight->cost?:'N/A' ?></td>
        <td align="right"><?= $flight->getLibStatut(3)?></td>
        <td align="right"><a
                    href="<?php echo $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=dellink&dellinkid=' . $key; ?>"><?php echo img_delete($langs->transnoentitiesnoconv("RemoveLink")); ?></a>
        </td>
    </tr>
    <?php
}
?>

<!-- END PHP TEMPLATE -->