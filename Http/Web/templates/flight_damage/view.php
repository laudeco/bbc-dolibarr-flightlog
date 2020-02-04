<?php
global $langs, $user, $object, $receiver;
?>

<table class="border centpercent">
    <?php

    print '<tr><td class="fieldrequired">' . $langs->trans("FieldidBBC_vols") . '</td><td>' . $object->idBBC_vols . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fielddate") . '</td><td>' . dol_print_date($object->date) . '</td></tr>';

    if ($user->rights->flightlog->vol->financial) {
        print '<tr><td class="fieldrequired">' . $langs->trans("Fieldis_facture") . '</td><td>' . $object->getLibStatut(5). '</td></tr>';
    }

    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldkilometers") . '</td><td>' . $object->kilometers . ' KM</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans("Fieldjustif_kilometers") . '</td><td>' . $object->justif_kilometers . '</td></tr>';
    if(!$object->isLinkedToOrder()){
        print '<tr><td class="fieldrequired">' . $langs->trans("Fieldcost") . '</td><td>' . $object->cost . " " . $langs->getCurrencySymbol($conf->currency) . '</td></tr>';
        print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_receiver") . '</td><td>' . $receiver->getNomUrl(1) . '</td></tr>';
    }else{
        print '<tr><td class="fieldrequired">' . $langs->trans("Order") . '</td><td><ul>';
        foreach($object->getOrders() as $currentOrder){
            print '<li>'.$currentOrder->getNomUrl(1).'</li>';
        }
        print '</ul></td></tr>';
    }
    ?>
</table>
