<?php

// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
    require '../../documents/custom/main.inc.php'; // From "custom" directory
}

global $db, $langs, $user;

dol_include_once('/flightLog/class/bbcvols.class.php');
dol_include_once("/flightLog/inc/other.php");

// Load translation files required by the page
$langs->load("mymodule@mymodule");


if (!$user->rights->flightLog->vol->add) {
    accessforbidden();
}


/* * *****************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 * ****************************************************************** */
$msg = '';
if ($_GET["action"] == 'add' || $_POST["action"] == 'add') {
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

        //verification des heures
        $patern = '#[0-9]{4}#';
        $error = 0;
        if (preg_match($patern, $vol->heureD) == 0 || strlen($vol->heureD) != 4) {
            $msg = '<div class="error">L\'heure depart n\'est pas correcte</div>';
            $error++;
        } else {
            $vol->heureD = $vol->heureD . '00';
        }
        if (preg_match($patern, $vol->heureA) == 0 || strlen($vol->heureA) != 4) {
            $msg = '<div class="error">L\'heure d\'arrivee n\'est pas correcte</div>';
            $error++;
        } else {
            $vol->heureA = $vol->heureA . '00';
        }

        if ($error == 0 && ($vol->heureA - $vol->heureD) <= 0) {
            $msg = '<div class="error">L\'heure de depart est plus grande  que l\'heure d\'arrivee</div>';
            $error++;
        }

        // verification du nombre de pax
        if ($vol->nbrPax < 0) {
            $msg = '<div class="error">Erreur le nombre de passager est �gale � 0 ou est un nombre n�gatif.</div>';
            $error++;
        }
        if ($error == 0) {
            $result = $vol->create($user);
            if ($result > 0) {
                //creation OK
                $msg = '<div class="ok">L\'ajout du vol du : ' . $_POST["reday"] . '/' . $_POST["remonth"] . '/' . $_POST["reyear"] . ' s\'est correctement effectue ! </div>';
                Header("Location: fiche.php?vol=" . $result);
            } else {
                // Creation KO
                $msg = '<div class="error">Erreur lors de l\'ajout du vol : ' . $vol->error . '! </div>';
                $error++;
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

// Put here content of your page
print "<form name='add' action=\"addFlight.php\" method=\"post\">\n";
print '<input type="hidden" name="action" value="add"/>';
print '<table class="border" width="100%">';
//date du vol
print "<tr>";
print '<td class="fieldrequired"> Date du vol</td><td>';
print $html->select_date($datec ? $datec : -1, '', '', '', '', 'add', 1, 1);
print '</td></tr>';
//type du vol
print "<tr>";
print '<td class="fieldrequired"> Type du vol</td><td>';
print select_flight_type($_POST['type']);
print '</td></tr>';
//Pilote
print "<tr>";
print '<td class="fieldrequired"> Pilote </td><td>';
print $html->select_dolusers($_POST["pilot"] ? $_POST["pilot"] : $_GET["pilot"], 'pilot', $user->id);
//print '<td class="fieldrequired"> Pilote <input type="hidden" name="pilot" value="'.$user->id.'"/></td><td>';
print '</td></tr>';
//organisateur
print "<tr>";
print '<td class="fieldrequired"> Organisateur </td><td>';
print $html->select_dolusers($_POST["orga"] ? $_POST["orga"] : $_GET["orga"], 'orga', 1);
print '</td></tr>';
//Ballon
print "<tr>";
print '<td width="25%" class="fieldrequired">Ballon</td><td>';
print select_balloons($_POST['ballon'], 'ballon', $showempty = 0, $showimmat = 0, $showDeclasse = 0);
print '</td></tr>';
//lieu d�part
print "<tr>";
print '<td width="25%" class="fieldrequired">Lieu de d&#233;part </td><td>';
print '<input type="text" name="lieuD" class="flat" value="' . $_POST['lieuD'] . '"/>';
print '</td></tr>';
//lieu arriv�e
print "<tr>";
print '<td width="25%" class="fieldrequired">Lieu d\'arriv&#233;e </td><td>';
print '<input type="text" name="lieuA" class="flat" value="' . $_POST['lieuA'] . '"/>';
print '</td></tr>';
//heure d�part
print "<tr>";
print '<td width="25%" class="fieldrequired">Heure de d&#233;part <br/>(format autorise XXXX)</td><td>';
print '<input type="text" name="heureD" class="flat" value="' . $_POST['heureD'] . '"/>';
print '</td></tr>';
//heure arriv�e
print "<tr>";
print '<td width="25%" class="fieldrequired">Heure d\'arriv&#233;e <br/>(format autorise XXXX)</td><td>';
print '<input type="text" name="heureA" class="flat" value="' . $_POST['heureA'] . '"/>';
print '</td></tr>';
//Numbe rof kilometrs done for the flight
print "<tr>";
print '<td width="25%" class="fieldrequired">Nombre de kilometres effectués pour le vol</td><td>';
print '<input type="number" name="kilometers" class="flat" value="' . $_POST['kilometers'] . '"/>';
print '</td></tr>';

//Justif KIlometers
print "<tr>";
print '<td width="25%" class="fieldrequired">Justificatif des KM</td><td>';
print '<textarea rows="2" cols="60" class="flat" name="justif_kilometers" >' . $_POST['justif_kilometers'] . '</textarea> ';
print '</td></tr>';
//NBR pax
print "<tr>";
print '<td width="25%" class="fieldrequired">Nombre de passagers</td><td>';
print '<input type="number" name="nbrPax" class="flat" value="' . $_POST['nbrPax'] . '"/>';
print '</td></tr>';
//Flight cost
print "<tr>";
print '<td width="25%" class="fieldrequired">Montant perçu</td><td>';
print '<input type="text" name="cost" class="flat" value="' . $_POST['cost'] . '"/>';
print "&euro;";
print '</td></tr>';
//Money receiver
print "<tr>";
print '<td width="25%" class="fieldrequired">Qui a perçu l\'argent</td><td>';
print $html->select_dolusers($_POST["fk_receiver"] ? $_POST["fk_receiver"] : $_GET["fk_receiver"], 'fk_receiver', 1);
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

print '</table>';

print '<br><center><input class="button" type="submit" value="' . $langs->trans("Save") . '"> &nbsp; &nbsp; ';
print '<input class="button" type="submit" name="cancel" value="' . $langs->trans("Cancel") . '"></center';

print '</form>';

/* * *************************************************
 * LINKED OBJECT BLOCK
 *
 * Put here code to view linked object
 * ************************************************** */
//$somethingshown=$myobject->showLinkedObjectBlock();
// End of page
$db->close();
llxFooter('$Date: 2011/07/31 22:21:57 $ - $Revision: 1.19 $');
?>
