<?php

use FlightLog\Application\Damage\ViewModel\Damage;

/**
 * @var Damage $damage
 * @var Bbcvols $flight
 */
global $damage, $langs, $form, $flight;

function prepareTabsDamage(Damage $damage)
{
    global $langs, $conf;

    dol_include_once('/flightlog/flightlog.inc.php');

    $tabUrls = [
        DOL_URL_ROOT . '/flightlog/index.php?r=get_one_damage&id=' . $damage->getId(),
    ];

    $tabNames = [
        'general',
    ];

    $tabTitle = [
        'Degat',
    ];

    $tabCollection = new TabCollection();

    $countUrls = count($tabUrls);
    for ($i = 0; $i < $countUrls; $i++) {
        if (empty($tabUrls[$i])) {
            continue;
        }

        $tabCollection = $tabCollection->addTab(new Tab($tabTitle[$i], $tabNames[$i], $tabUrls[$i]));
    }

    $head = $tabCollection->toArray();
    $tabCount = count($tabCollection);

    complete_head_from_modules($conf, $langs, $damage, $head, $tabCount, 'bbcDamage');

    return $head;
}

$head = prepareTabsDamage($damage);
dol_fiche_head([], 'general', $langs->trans("Dégats"));

$linkback = '<a href="' . DOL_URL_ROOT . '/flightlog/index.php?r=get_list_damages">' . $langs->trans("BackToList") . '</a>';
//print $form->showrefnav($object, "idBBC_vols", $linkback, true, "idBBC_vols");
?>

<table class="border centpercent">
    <tr><td class="fieldrequired">Auteur</td><td><?php echo $damage->getAuthorName();?></td></tr>
    <tr><td class="fieldrequired">Montant</td><td><?php echo $damage->getAmount();?>€</td></tr>
    <tr><td class="fieldrequired">Facturé</td><td><?php echo $damage->isInvoiced()? 'Oui' : 'Non';?></td></tr>
    <tr>
        <td class="fieldrequired">Vol</td>
        <td>
            <?php print $flight->getNomUrl(); ?>
        </td>
    </tr>
</table>

<div class="fichecenter">
    <div class="fichehalfleft">
        <?php $form->showLinkedObjectBlock($damage); ?>
    </div>
</div>

<?php
    dol_fiche_end();
?>

