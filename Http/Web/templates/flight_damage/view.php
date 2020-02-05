<?php

/**
 * @var array|\FlightLog\Application\Damage\ViewModel\Damage[] $damages
 */

global $langs, $user, $damages, $receiver;

?>

<table class="border centpercent">
    <tr>
        <td>Auteur</td>
        <td>Montant</td>
    </tr>
    <?php $total = 0; ?>
    <?php foreach($damages as $currentDamage):?>
        <?php $total += $currentDamage->getAmount(); ?>
        <tr>
            <td><?php echo $currentDamage->getAuthorName();?></td>
            <td><?php echo $currentDamage->getAmount();?>€</td>
        </tr>
    <?php endforeach; ?>

    <tr class="bold border-top">
        <td>Total</td>
        <td><?php echo $total; ?>€</td>
    </tr>
</table>
