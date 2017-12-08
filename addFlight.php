<?php

// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
    require '../../documents/custom/main.inc.php'; // From "custom" directory
}

global $db, $langs, $user, $conf;

dol_include_once('/flightlog/class/bbcvols.class.php');
dol_include_once('/flightlog/class/bbctypes.class.php');
dol_include_once("/flightlog/lib/flightLog.lib.php");
dol_include_once("/flightlog/validators/FlightValidator.php");

// Load translation files required by the page
$langs->load("mymodule@flightlog");

$validator = new FlightValidator($langs, $db, $conf->global->BBC_FLIGHT_TYPE_CUSTOMER);

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

        $vol = new Bbcvols($db);

        $vol->date = $dated;
        $vol->lieuD = $_POST['lieuD'];
        $vol->lieuA = $_POST['lieuA'];
        $vol->heureD = $_POST['heureD'];
        $vol->heureA = $_POST['heureA'];
        $vol->BBC_ballons_idBBC_ballons = $_POST['ballon'];
        $vol->nbrPax = $_POST['nbrPax'];
        $vol->remarque = $_POST['comm'];
        $vol->incidents = $_POST['inci'];
        $vol->fk_type = $_POST['type'];
        $vol->fk_pilot = $_POST['pilot'];
        $vol->fk_organisateur = $_POST['orga'];
        $vol->kilometers = $_POST['kilometers'];
        $vol->cost = $_POST['cost'];
        $vol->fk_receiver = $_POST['fk_receiver'];
        $vol->justif_kilometers = $_POST['justif_kilometers'];
        $isGroupedFlight = (int) GETPOST('grouped_flight', 'int', 2) === 1;

        if ($validator->isValid($vol, $_REQUEST)) {
            $result = $vol->create($user);
            if ($result > 0) {
                //creation OK

                include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
                $interface = new Interfaces($db);
                $triggerResult = $interface->run_triggers('BBC_FLIGHT_LOG_ADD_FLIGHT', $vol, $user, $langs, $conf);

                $msg = '<div class="ok">L\'ajout du vol du : ' . $_POST["reday"] . '/' . $_POST["remonth"] . '/' . $_POST["reyear"] . ' s\'est correctement effectue ! </div>';
                Header("Location: card.php?id=" . $result);
            } else {
                // Creation KO
                $msg = '<div class="error">Erreur lors de l\'ajout du vol : ' . $vol->error . '! </div>';
            }
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
                    <textarea rows="2" cols="60" class="flat <?php echo($validator->hasError('justif_kilometers') ? 'error' : '') ?>" name="justif_kilometers">
                        <?php echo $_POST['justif_kilometers'] ?>
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
                           value="<?php echo $_POST['nbrPax'] ?>"/>
                </td>
            </tr>

            <!-- Flight cost -->
            <tr>
                <td class="fieldrequired">Montant perçu</td>
                <td>
                    <input type="text" name="cost" class="flat  <?php echo $validator->hasError('cost') ? 'error' : '' ?>" value="<?php echo $_POST['cost'] ?> "/>
                    &euro;
                </td>
            </tr>

            <?php
            //Money receiver
            print "<tr>";
            print '<td class="fieldrequired">Qui a perçu l\'argent</td><td>';
            print $html->select_dolusers($_POST["fk_receiver"] ? $_POST["fk_receiver"] : $_GET["fk_receiver"],
                'fk_receiver', 1);
            print '</td></tr>';

            //commentaires
            print "<tr>";
            print '<td class="fieldrequired"> Commentaire </td><td>';
            print '<textarea rows="2" cols="60" class="flat" name="comm" placeholder="RAS">' . $_POST['comm'] . '</textarea> ';
            print '</td></tr>';

            //incidents
            print "<tr>";
            print '<td class="fieldrequired"> incidents </td><td>';
            print '<textarea rows="2" cols="60" class="flat" name="inci" placeholder="RAS">' . $_POST['inci'] . '</textarea> ';
            print '</td></tr>';
            ?>
        </table>
    </section>
<?php

print '<br><input class="button" type="submit" value="' . $langs->trans("Save") . '"> &nbsp; &nbsp; ';
print '<input class="button" type="submit" name="cancel" value="' . $langs->trans("Cancel") . '">';

print '</form>';

$db->close();
