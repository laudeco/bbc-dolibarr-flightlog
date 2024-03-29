<?php

/**
 * @var \FlightLog\Http\Web\Form\DamageCreationForm $damageCreationForm
 */

global $damageCreationForm, $langs;

$renderer = new \flightlog\form\SimpleFormRenderer();

?>

<div class="errors error-messages">
    <?php
    foreach ($damageCreationForm->getErrorMessages() as $errorMessage) {
        print sprintf('<div class="error"><span>%s</span></div>', $errorMessage);
    }
    ?>
</div>

<form class="flight-form js-form" name='<?php echo $damageCreationForm->getName(); ?>' method="<?php echo $damageCreationForm->getMethod(); ?>">
    <input type="hidden" name="action" value="handle"/>

    <?php echo $renderer->render($damageCreationForm->getElement('flight_id')); ?>
    <?php echo $renderer->render($damageCreationForm->getElement('token')); ?>

    <?php if($damageCreationForm->has('amount')): ?>
        <section class="form-section">
            <h1 class="form-section-title"><?php echo $langs->trans('Ajout de dégâts'); ?></h1>
            <table class="border" width="50%">

                <tr>
                    <td class="fieldrequired" width="50%">Montant des dégâts (TTC en €)</td>
                    <td><?php echo $renderer->render($damageCreationForm->getElement('amount')); ?></td>
                </tr>

                <tr>
                    <td class="fieldrequired" width="50%">Description</td>
                    <td><?php echo $renderer->render($damageCreationForm->getElement('label')); ?></td>
                </tr>

                <tr>
                    <td class="fieldrequired" width="25%">Autheur des dégâts</td>
                    <td>
                        <?php echo $renderer->render($damageCreationForm->getElement('author_id')); ?>
                        <br/> <span class="text-muted">Par défaut ces dégats seront attribués au pilote.</span>
                    </td>
                </tr>

                <tr>
                    <td class="" width="25%">Facture fournisseur</td>
                    <td>
                        <?php echo $renderer->render($damageCreationForm->getElement('bill_id')); ?>
                        <br/> <span class="text-muted">Pas obligatoire, mais dans un soucis de transparance il est toujours bon de lier la facture.</span>
                    </td>
                </tr>

            </table>
        </section>
    <?php endif; ?>

    <button class="button" type="submit">Ajouter</button>
    <a href="card_tab_damage.php?id=<?php echo $object->getId(); ?>" class="btn button button-a">Annuler</a>
</form>

