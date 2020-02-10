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
                <a href="<?php print sprintf('%s/flightlog/index.php?r=get_one_damage&id=%s', DOL_URL_ROOT, $currentDamage->getId());?>"><i class="fas fa-link"></i></a>
            </td>
        </tr>
    <?php endforeach; ?>

    <tr class="bold border-top">
        <td>Total</td>
        <td><?php echo $total; ?>€</td>
        <td></td>
    </tr>
</table>
