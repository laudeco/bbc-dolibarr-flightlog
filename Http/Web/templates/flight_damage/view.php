<?php

/**
 * @var array|\FlightLog\Application\Damage\ViewModel\Damage[] $damages
 */

global $langs, $user, $damages, $receiver, $flightId;

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
            <td>
                <?php if($currentDamage->isInvoiced()): ?>
                    <i class="fas fa-check"></i>
                <?php else: ?>
                    <i class="fas fa-times"></i>
                <?php endif; ?>
            </td>
            <td>
                <?php if(!$currentDamage->isInvoiced()): ?>
                    <a href="<?php print sprintf('%s/flightlog/card_tab_damage.php?id=%s&action=%s&damage=%s', DOL_URL_ROOT, $flightId, 'bill_damage', $currentDamage->getId());?>"><i class="fas fa-file-invoice"></i></a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>

    <tr class="bold border-top">
        <td>Total</td>
        <td><?php echo $total; ?>€</td>
        <td></td>
    </tr>
</table>
