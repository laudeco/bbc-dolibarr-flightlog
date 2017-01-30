<?php
//TODO refactor it by using the card layout

// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
    require '../../documents/custom/main.inc.php'; // From "custom" directory
}

global $db, $langs, $user;

dol_include_once('/flightLog/class/bbcvols.class.php');
dol_include_once('/flightLog/class/bbctypes.class.php');
dol_include_once("/flightLog/inc/other.php");
dol_include_once("/flightBalloon/bbc_ballons.class.php");

// Load translation files required by the page
$langs->load("mymodule@mymodule");

// Protection if the user can't acces to the module
if (!$user->rights->flightLog->vol->access) {
    accessforbidden();
}

//TODO get all parameters here

/* * *****************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 * ****************************************************************** */
//To delete the flight
if (GETPOST('action') == 'deleteconfirm' && GETPOST('confirm') == 'yes') {
    if ($_GET['vol']) {
        $tmp = New Bbcvols($db);
        $tmp->fetch($_GET['vol']);
        if ($tmp->getId() && (($user->rights->flightLog->vol->delete && $user->id == $pilot->id ) || $user->admin)) {
            if ($tmp->delete($user) > 0) {
                dol_syslog("FLIGHT_DELETE : ".$tmp->toString().' BY : '.$user->id,LOG_WARNING);
                Header("Location: readFlights.php");
            }
        }
    }
}

//confirmation de la facturaion
if ($user->rights->flightLog->vol->status && $_POST['action'] == 'factconfirm' && $_POST['confirm'] == 'yes') {
    if ($_GET['vol']) {
        $tmp = New Bbcvols($db);
        $fetchResult = $tmp->fetch($_GET['vol']);
        if ($fetchResult > -1 && $user->rights->flightLog->vol->status) {
            $tmp->is_facture = 1;
            $tmp->update($user);
        }
    }
}

//Edit a flight
if ($_POST['action'] == 'edit') {

    $vol = New Bbcvols($db);
    $vol->fetch($_POST['vol']);
    $dated = dol_mktime(12, 0, 0, $_POST["remonth"], $_POST["reday"], $_POST["reyear"]);

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

    if ($vol->update($user) < 0) {
        dol_syslog("Error during update a flight ".$vol->error, LOG_ERR);
        $msg = '<div class="error">Erreur lors de la MAJ </div>';
        $error++;
    } else {
        Header("Location: fiche.php?vol=".$_POST['vol']);
    }
}





/* * *************************************************
 * PAGE
 *
 * Put here all code to build page
 * ************************************************** */

llxHeader('', 'Carnet de vol', '');
//delete
if ((($user->rights->flightLog->vol->delete && $user->id == $pilot->id ) || $user->admin) && $_GET["action"] == 'delete') {
    $html = New Form($db);
    print $html->formconfirm("fiche.php?vol=" . $_GET['vol'], "Delete vol", "Etes vous sure de vouloir supprimer ce vol?", 'deleteconfirm');
}
//facturation
if ($_GET["action"] == 'fact' && $user->rights->flightLog->vol->status) {
    $html = New Form($db);
    print $html->formconfirm("fiche.php?vol=" . $_GET['vol'], "Facturation du vol", "Etes vous sure de vouloir noter ce vol comme factur�?", 'factconfirm');
}
if ($msg) {
    print $msg;
}


// Put here content of your page
$vol = New Bbcvols($db);
$vol->fetch($_GET['vol']);

$pilot = New User($db);
$pilot->fetch($vol->fk_pilot);

$orga = New User($db);
$orga->fetch($vol->fk_organisateur);

$balloon = New Bbc_ballons($db);
$balloon->fetch($vol->BBC_ballons_idBBC_ballons);

$type = New Bbctypes($db);
$type->fetch($vol->fk_type);


//si l'action est edit et que l'utilisateur a le droit de modifier
if (isset($_GET['action']) && $_GET['action'] == 'edit' && ($user->rights->flightLog->vol->edit || $user->id == $pilot->id || $user->admin)) {
    $html = new Form($db);
    $datec = dol_mktime(12, 0, 0, $_POST["remonth"], $_POST["reday"], $_POST["reyear"]);


    // Put here content of your page
    print "<form name='add' action=\"fiche.php\" method=\"post\">\n";
    print '<input type="hidden" name="action" value="edit"/>';
    print '<input type="hidden" name="vol" value="' . $vol->idBbcvols . '"/>';
    print '<table class="border" width="100%">';
    print '<tr><td>identifiant</td><td>' . $vol->idBbcvols . '</td></tr>';
    //date du vol
    print "<tr>";
    print '<td class="fieldrequired"> Date du vol</td><td>';
    print $html->select_date($vol->date, '', '', '', '', 'add', 1, 1);
    print '</td></tr>';
    //type du vol
    print "<tr>";
    print '<td class="fieldrequired"> Type du vol</td><td>';
    print select_flight_type($vol->fk_type);
    print '</td></tr>';
    //Pilote
    print "<tr>";
    print '<td class="fieldrequired"> Pilote</td><td>';
    print $html->select_dolusers($vol->fk_pilot,'pilot',0);
    print '</td></tr>';
    //organisateur
    print "<tr>";
    print '<td class="fieldrequired"> Organisateur </td><td>';
    print $html->select_dolusers($vol->fk_organisateur, 'orga', 1);
    print '</td></tr>';
    //Ballon
    print "<tr>";
    print '<td width="25%" class="fieldrequired">Ballon</td><td>';
    print select_balloons($vol->BBC_ballons_idBBC_ballons, 'ballon', $showempty = 0, $showimmat = 0, $showDeclasse = 0);
    print '</td></tr>';
    //lieu d�part
    print "<tr>";
    print '<td width="25%" class="fieldrequired">Lieu de d&#233;part </td><td>';
    print '<input type="text" name="lieuD" calss="flat" value="' . $vol->lieuD . '"/>';
    print '</td></tr>';
    //lieu arriv�e
    print "<tr>";
    print '<td width="25%" class="fieldrequired">Lieu d\'arriv&#233;e </td><td>';
    print '<input type="text" name="lieuA" calss="flat" value="' . $vol->lieuA . '"/>';
    print '</td></tr>';
    //heure d�part
    print "<tr>";
    print '<td width="25%" class="fieldrequired">Heure de d&#233;part <br/>(format autorise XXXX)</td><td>';
    print '<input type="text" name="heureD" calss="flat" value="' . $vol->heureD . '"/>';
    print '</td></tr>';
    //heure arriv�e
    print "<tr>";
    print '<td width="25%" class="fieldrequired">Heure d\'arriv&#233;e <br/>(format autorise XXXX)</td><td>';
    print '<input type="text" name="heureA" calss="flat" value="' . $vol->heureA . '"/>';
    print '</td></tr>';
//Numbe rof kilometrs done for the flight
    print "<tr>";
    print '<td width="25%" class="fieldrequired">Nombre de kilometres effectués pour le vol</td><td>';
    print '<input type="number" name="kilometers" calss="flat" value="' . $vol->kilometers . '"/>';
    print '</td></tr>';


//Justif KIlometers
    print "<tr>";
    print '<td width="25%" class="fieldrequired">Justificatif des KM</td><td>';
    print '<textarea rows="2" cols="60" class="flat" name="justif_kilometers" >' . $vol->justif_kilometers. '</textarea> ';
    print '</td></tr>';
//NBR pax
    print "<tr>";
    print '<td width="25%" class="fieldrequired">Nombre de passagers</td><td>';
    print '<input type="number" name="nbrPax" calss="flat" value="' . $vol->nbrPax . '"/>';
    print '</td></tr>';
//Flight cost
    print "<tr>";
    print '<td width="25%" class="fieldrequired">Montant perçu</td><td>';
    print '<input type="text" name="cost" calss="flat" value="' .$vol->cost . '"/>';
    print "&euro;";
    print '</td></tr>';
//Money receiver
    print "<tr>";
    print '<td width="25%" class="fieldrequired">Qui a perçu l\'argent</td><td>';
    print $html->select_dolusers($vol->fk_receiver, 'fk_receiver', 1);
    print '</td></tr>';
//commentaires
    print "<tr>";
    print '<td class="fieldrequired"> Commentaire </td><td>';
    print '<textarea rows="2" cols="60" calss="flat" name="comm" placeholder="RAS">' . $vol->remarque . '</textarea> ';
    print '</td></tr>';
//incidents
    print "<tr>";
    print '<td class="fieldrequired"> incidents </td><td>';
    print '<textarea rows="2" cols="60" calss="flat" name="inci" placeholder="RAS">' . $vol->incidents . '</textarea> ';
    print '</td></tr>';

    print '</table>';

    print '<br><center><input class="button" type="submit" value="' . $langs->trans("Save") . '"> &nbsp; &nbsp; ';
    print '<input class="button" type="submit" name="cancel" value="' . $langs->trans("Cancel") . '"></center';

    print '</form>';
} else {
    print '<table class="border" width="100%">';
    print '<tr><td>identifiant</td><td>' . $vol->getIdBBCVols() . '</td></tr>';
    print '<tr><td>date </td><td>' . dol_print_date($vol->date) . '</td></tr>';
    print '<tr><td>Ballon </td><td>' . ($balloon->immat) . '</td></tr>';
    print '<tr><td>lieu de decollage</td><td>' . $vol->lieuD . '</td></tr>';
    print '<tr><td>lieu d\'aterissage</td><td>' . $vol->lieuA . '</td></tr>';
    print '<tr><td>Heure 1</td><td>' . $vol->heureD . '</td></tr>';
    print '<tr><td>Heure 2</td><td>' . $vol->heureA . '</td></tr>';
    print '<tr><td>type de vol</td><td>' . $type->idType . '-' . $type->nom . '</td></tr>';
    print '<tr><td>Pilote </td><td><a>' . $pilot->getNomUrl() . '</a></td></tr>';
    print '<tr><td>organisateur </td><td><a>' . $orga->getNomUrl() . '</a></td></tr>';
    print '<tr><td>Nombre de passagers</td><td>' . $vol->nbrPax . '</td></tr>';
    print '<tr><td>Incident </td><td>' . $vol->incidents . '</td></tr>';

    print '</table>';
}


print '<div class="tabsAction">';

if (!isset($_GET['action'])) {
    //delete - if user has right
    if (($user->rights->flightLog->vol->delete && $user->id == $pilot->id ) || $user->admin) {
        print '<a class="butActionDelete" href="fiche.php?action=delete&vol=' . $vol->getId() . '">' . $langs->trans('Delete') . '</a>';
    } else {
        print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('Delete') . '</a>';
    }

    //facturer
    if ($user->rights->flightLog->vol->status && !$vol->is_facture) {
        print '<a class="butAction" href="fiche.php?action=fact&vol=' . $vol->getId() . '">facturer</a>';
    } else {
        print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">facturer</a>';
    }

    //bouton modifier si on a le droit ou si c'est son vol
    if ($user->rights->flightLog->vol->edit || $user->id == $pilot->id || $user->admin) {
        print '<a class="butAction" href="fiche.php?action=edit&vol=' . $vol->getId() . '">' . $langs->trans('Edit') . '</a>';
    } else {
        print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('Edit') . '</a>';
    }
}

print '</div>';

llxFooter();