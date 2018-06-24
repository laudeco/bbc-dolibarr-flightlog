<?php

// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
    require '../../documents/custom/main.inc.php'; // From "custom" directory
}

global $db, $langs, $user, $conf;

dol_include_once('/commande/class/commande.class.php');
dol_include_once('/flightlog/class/bbcvols.class.php');
dol_include_once('/flightlog/class/bbctypes.class.php');
dol_include_once("/flightlog/lib/flightLog.lib.php");
dol_include_once("/flightlog/validators/FlightValidator.php");
dol_include_once("/flightlog/command/CommandInterface.php");
dol_include_once("/flightlog/command/CommandHandlerInterface.php");
dol_include_once("/flightlog/command/CreateFlightCommand.php");
dol_include_once("/flightlog/command/CreateFlightCommandHandler.php");

// Load translation files required by the page
$langs->load("mymodule@flightlog");

$validator = new FlightValidator($langs, $db, $conf->global->BBC_FLIGHT_TYPE_CUSTOMER);
$createFlightHandler = new CreateFlightCommandHandler($db, $conf, $user, $langs, $validator);

if (!$user->rights->flightlog->vol->add) {
    accessforbidden();
}

/* * *****************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 * ****************************************************************** */
$msg = '';
if (GETPOST("action") == 'add') {
    if (!$_POST["cancel"]) {
        $dated = dol_mktime(12, 0, 0, $_POST["remonth"], $_POST["reday"], $_POST["reyear"]);
        $isGroupedFlight = (int) GETPOST('grouped_flight', 'int', 2) === 1;
        $orderIds = GETPOST('order_id', 'array', 2);

        $volCommand = new CreateFlightCommand();
        $volCommand->setDate($dated)
            ->setLieuD($_POST['lieuD'])
            ->setLieuA($_POST['lieuA'])
            ->setHeureD($_POST['heureD'])
            ->setHeureA($_POST['heureA'])
            ->setBBCBallonsIdBBCBallons($_POST['ballon'])
            ->setNbrPax($_POST['nbrPax'])
            ->setRemarque($_POST['comm'])
            ->setIncidents($_POST['inci'])
            ->setFkType($_POST['type'])
            ->setFkPilot($_POST['pilot'])
            ->setFkOrganisateur($_POST['orga'])
            ->setKilometers($_POST['kilometers'])
            ->setCost($_POST['cost'])
            ->setFkReceiver($_POST['fk_receiver'])
            ->setJustifKilometers($_POST['justif_kilometers'])
            ->setPassengerNames($_POST['passenger_names'])
            ->setGroupedFlight($isGroupedFlight)
            ->setOrderIds($orderIds);

        try{
            $vol = $createFlightHandler->handle($volCommand);

            include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
            $interface = new Interfaces($db);
            $triggerResult = $interface->run_triggers('BBC_FLIGHT_LOG_ADD_FLIGHT', $vol, $user, $langs, $conf);

            $msg = '<div class="ok">L\'ajout du vol du : ' . $_POST["reday"] . '/' . $_POST["remonth"] . '/' . $_POST["reyear"] . ' s\'est correctement effectue ! </div>';
            Header("Location: card.php?id=" . $vol->id);
        }catch (\Exception $e){
            $msg = '<div class="error">Erreur lors de l\'ajout du vol : ' . $vol->error . '! </div>';
        }

    }
}


/* * *************************************************
 * PAGE
 *
 * Put here all code to build page
 * ************************************************** */

llxHeader('', 'Carnet de vol', '');

$html = new Form($db);
$commande = new Commande($db);
$datec = dol_mktime(12, 0, 0, $_POST["remonth"], $_POST["reday"], $_POST["reyear"]);
if ($msg) {
    print $msg;
}

?>

    <div class="errors error-messages">
        <?php
        foreach ($validator->getErrors() as $errorMessage) {
            print sprintf('<div class="error"><span>%s</span></div>', $errorMessage);
        }
        ?>
    </div>
    <form class="flight-form" name='add' action="addFlight.php" method="post">
    <input type="hidden" name="action" value="add"/>

    <!-- Date et heures -->
    <section class="form-section">
        <h1 class="form-section-title"><?php echo $langs->trans('Date & heures'); ?></h1>
        <table class="border" width="100%">
            <?php
            //type du vol
            print "<tr>";
            print '<td class="fieldrequired"> Type du vol</td><td colspan="3">';
            select_flight_type($_POST['type']);
            print '</td></tr>';

            //date du vol
            print "<tr>";
            print '<td class="fieldrequired"> Date du vol</td><td>';
            print $html->select_date($datec ? $datec : -1, '', '', '', '', 'add', 1, 1);
            print '</td></tr>';

            //Hour start
            print '<tr><td class="fieldrequired">Heure de d&#233;part (format autorise XXXX)</td><td width="25%" >'; ?>
            <input type="text"
                   name="heureD"
                   class="flat <?php echo($validator->hasError('heureD') ? 'error' : '') ?>"
                   value="<?php echo $_POST['heureD'] ?>"/>
            </td>

            <?php
            //Hour end
            print '<td class="fieldrequired">Heure d\'arriv&#233;e (format autorise XXXX)</td><td>'; ?>
            <input type="text"
                   name="heureA"
                   class="flat <?php echo($validator->hasError('heureA') ? 'error' : '') ?>"
                   value="<?php echo $_POST['heureA'] ?>"/>
            </td>
            </tr>

        </table>
    </section>

    <section class="form-section">
        <h1 class="form-section-title"><?php echo $langs->trans('Pilote & ballon') ?></h1>
        <table class="border" width="50%">
            <?php
            //Pilote
            print "<tr>";
            print '<td class="fieldrequired"> Pilote </td><td >';
            print $html->select_dolusers($_POST["pilot"] ? $_POST["pilot"] : $_GET["pilot"], 'pilot', $user->id);
            print '</td></tr>';

            //Ballon
            print "<tr>";
            print '<td width="25%" class="fieldrequired">Ballon</td><td>';
            select_balloons($_POST['ballon'], 'ballon', 0, 0);
            print '</td></tr>';
            ?>

            <tr>
                <td>Il y'avait-il plusieurs ballons ?</td>
                <td colspan="3"><input type="checkbox" value="1" name="grouped_flight"/> - Oui</td>
            </tr>
        </table>
    </section>

    <section class="form-section">
        <h1 class="form-section-title"><?php echo $langs->trans('Lieux') ?></h1>
        <table class="border" width="100%">
            <?php

            //place start
            print "<tr>";
            print '<td class="fieldrequired">Lieu de d&#233;part </td><td width="25%" >';
            print '<input type="text" name="lieuD" class="flat" value="' . $_POST['lieuD'] . '"/>';
            print '</td>';

            //place end
            print '<td class="fieldrequired">Lieu d\'arriv&#233;e </td><td>';
            print '<input type="text" name="lieuA" class="flat" value="' . $_POST['lieuA'] . '"/>';
            print '</td></tr>';

            ?>

        </table>
    </section>

    <section class="form-section">
        <h1 class="form-section-title"><?php echo $langs->trans('Fieldfk_organisateur') ?></h1>
        <table class="border" width="50%">

            <?php


            //organisateur
            print "<tr>";
            print '<td class="fieldrequired">' . $langs->trans('Fieldfk_organisateur') . ' </td><td>';
            print $html->select_dolusers($_POST["orga"] ? $_POST["orga"] : $_GET["orga"], 'orga', 1);
            print '</td></tr>';
            ?>

        </table>
    </section>


    <section class="form-section">
        <h1 class="form-section-title"><?php echo $langs->trans('Déplacements') ?></h1>
        <table class="border" width="50%">
            <!-- number of kilometers done for the flight -->
            <tr>
                <td class="fieldrequired">Nombre de kilometres effectués pour le vol</td>
                <td>
                    <input type="number" name="kilometers" class="flat <?php echo($validator->hasError('kilometers') ? 'error' : '') ?>" value="<?php echo $_POST['kilometers'] ?>"/>
                </td>
            </tr>

            <!-- Justif Kilometers -->
            <tr>

                <td width="25%" class="fieldrequired">Justificatif des KM </td>
                <td>
                    <textarea rows="2" cols="60" class="flat <?php echo($validator->hasError('justif_kilometers') ? 'error' : '') ?>" name="justif_kilometers"><?php echo $_POST['justif_kilometers'] ?>
                    </textarea>
                </td>
            </tr>
        </table>
    </section>

    <!-- Passagers -->
    <section class="form-section">
        <h1 class="form-section-title"><?php echo $langs->trans('Passager') ?></h1>
        <table class="border" width="50%">
            <tr>
                <td class="fieldrequired"><?php echo $langs->trans('Nombre de passagers'); ?></td>
                <td>
                    <input type="number"
                           name="nbrPax"
                           class="flat <?php echo $validator->hasError('nbrPax') ? 'error' : '' ?>"
                           value="<?php echo $_POST['nbrPax']?: 0 ?>"/>
                </td>
            </tr>

            <!-- passenger names -->
            <tr>
                <td width="25%" class="fieldrequired"><?php echo $langs->trans('Noms des passagers'); ?><br/>(Séparé par des ; )</td>
                <td>
                    <textarea name="passenger_names" cols="60" rows="2" class="flat <?php echo $validator->hasError('passenger_names') ? 'error' : '' ?>"><?php echo $_POST['passenger_names'] ?></textarea>
                </td>
            </tr>
        </table>
    </section>

    <!-- billing information -->
    <section class="form-section">
        <h1 class="form-section-title"><?php echo $langs->trans('Facturation') ?></h1>
        <table class="border" width="50%">

            <!-- Commande -->
            <tr>
                <td class="fieldrequired"><?php echo $langs->trans('Commande du vol')?></td>
                <td class="js-order">
                    <?php
                     echo $html->selectarray('order_id',$commande->liste_array(2),$_POST['order_id'], 1,0,0,'multiple style="width:100%"',0,0,0,'','',1);
                    ?>
                </td>
            </tr>

            <!-- Money receiver -->
            <tr class="js-hide-order">
                <td class="fieldrequired"><?php echo $langs->trans('Qui a perçu l\'argent')?></td><td>
                    <?php print $html->select_dolusers($_POST["fk_receiver"] ? $_POST["fk_receiver"] : $_GET["fk_receiver"],
                    'fk_receiver', 1); ?>
                </td>
            </tr>

            <!-- Flight cost -->
            <tr class="js-hide-order">
                <td class="fieldrequired">Montant perçu</td>
                <td>
                    <input type="text" name="cost" class="flat  <?php echo $validator->hasError('cost') ? 'error' : '' ?>" value="<?php echo $_POST['cost'] ?> "/>
                    &euro;
                </td>
            </tr>

            <!-- commentaires -->
            <tr class="">
                <td class="fieldrequired"> Commentaire </td><td>
                    <textarea rows="2" cols="60" class="flat" name="comm" placeholder="RAS"><?php print $_POST['comm']; ?></textarea>
                </td>
            </tr>

            <!-- incidents -->
            <tr class="">
                <td class="fieldrequired"> incidents </td><td>
                    <textarea rows="2" cols="60" class="flat" name="inci" placeholder="RAS"><?php print $_POST['inci']; ?></textarea>
                </td>
            </tr>
        </table>
    </section>
<?php

print '<br><input class="button" type="submit" value="' . $langs->trans("Save") . '"> &nbsp; &nbsp; ';
print '<input class="button" type="submit" name="cancel" value="' . $langs->trans("Cancel") . '">';

print '</form>';

$db->close();
?>

<script type="application/javascript">

    function hideOrderInformation (){
        var $this = $(this);

        if($this.val() > -1){
            $('input, select', '.js-hide-order').attr('disabled', 'disabled');
        }else{
            $('input, select', '.js-hide-order').removeAttr('disabled');
        }
    }

    $(function(){

        $('.js-order select').on('change', hideOrderInformation);
        $('.js-order select').each(hideOrderInformation);

        $('.js-order select').each(function($element){
            if(!$(this).prop('multiple')){
                return;
            }

            $(this).attr('name', $(this).attr('id')+'[]')
        });
    });


</script>
