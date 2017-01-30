<?php

/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   	\file       dev/skeletons/skeleton_page.php
 * 		\ingroup    mymodule othermodule1 othermodule2
 * 		\brief      This file is an example of a php page
 * 		\version    $Id: skeleton_page.php,v 1.19 2011/07/31 22:21:57 eldy Exp $
 * 		\author		Put author name here
 * 		\remarks	Put here some comments
 */
//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');	// If there is no menu to show
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');	// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');		// If this page is public (can be called outside logged session)
// Change this following line to use the correct relative path (../, ../../, etc)
$res = 0;
if (!$res && file_exists("../main.inc.php"))
    $res = @include("../main.inc.php");
if (!$res && file_exists("../../main.inc.php"))
    $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php"))
    $res = @include("../../../main.inc.php");
if (!$res && file_exists("../../../dolibarr/htdocs/main.inc.php"))
    $res = @include("../../../dolibarr/htdocs/main.inc.php");     // Used on dev env only
if (!$res && file_exists("../../../../dolibarr/htdocs/main.inc.php"))
    $res = @include("../../../../dolibarr/htdocs/main.inc.php");   // Used on dev env only
if (!$res && file_exists("../../../../../dolibarr/htdocs/main.inc.php"))
    $res = @include("../../../../../dolibarr/htdocs/main.inc.php");   // Used on dev env only
if (!$res)
    die("Include of main fails");
// Change this following line to use the correct relative path from htdocs (do not remove DOL_DOCUMENT_ROOT)
require_once(DOL_DOCUMENT_ROOT . "/../htdocs/flightBalloon/bbc_ballons.class.php");
require_once(DOL_DOCUMENT_ROOT . "/../htdocs/user/class/user.class.php");
require_once(DOL_DOCUMENT_ROOT . "/flightLog/bbc_vols.class.php");
// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");

// Get parameters
$myparam = isset($_GET["myparam"]) ? $_GET["myparam"] : '';

// Protection if the user can't acces to the module
if (!$user->rights->flightLog->vol->access) {
    accessforbidden();
}



/* * *****************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 * ****************************************************************** */

//l'utilisateur a le droit de selectionner le ballon qu'il veut
if (isset($_GET["ballon"])) {
    $idBallon = $_GET['ballon'];
} else {
    //l'utilisateur n'a pas le choix du ballon
    //il est titulaire d'un ballon
    $query = 'SELECT * FROM llx_bbc_ballons';
    if (!$user->rights->flightLog->vol->detail) {
        $query.= ' WHERE `fk_responsable` = ' . $user->id;
        $query.= ' OR `fk_co_responsable` = ' . $user->id;
    }
    $resql = $db->query($query);
    if ($resql) {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
//            print'</tr>';
            while ($i < $num) {
                $obj = $db->fetch_object($resql); //vol
                if ($obj) {
                    $idBallon = ($obj->rowid);
                }
                $i++;
            }
        } else {
            //il n'est pas titulaire d'un ballon
            accessforbidden("Vous n'&ecirc;tes pas titulaire du ballon");
        }
    }
}
if ($idBallon != -1) {

    //balloon with ID
    $ballon = New Bbc_ballons($db);

    //date
    $datep = dol_mktime(-1, -1, -1, $_GET["apmonth"], $_GET["apday"], $_GET["apyear"]);
    $datef = dol_mktime(-1, -1, -1, $_GET["p2month"], $_GET["p2day"], $_GET["p2year"]);

    if ($ballon->fetch($idBallon) == -1) {
        print "ERROR" . $idBallon . "<br/>";
    }
    //titulaire with ballon ID
    $titulaire = new User($db);
    $titulaire->fetch($ballon->fk_responsable);
    //flight with balloon ID
    $query = 'SELECT *, TIMEDIFF(heureA,heureD) AS time FROM llx_bbc_vols';
    $query.= ' WHERE `BBC_ballons_idBBC_ballons` = ' . $ballon->id;
    if ($datep) {
        $query.= ' AND date >= \'' . dol_date('Y-m-d', $datep) . '\'';
    }
    if ($datef) {
        $query.= ' AND date <= \'' . dol_date('Y-m-d', $datef) . '\'';
    }

    $query.= ' ORDER BY date';

    $resql = $db->query($query);
}



/* * *************************************************
 * PAGE
 *
 * Put here all code to build page
 * ************************************************** */

llxHeader('', 'Carnet de vol', '');
if ($msg && $idBallon != -1) {
    print $msg;
} else {
    $form = new Form($db);

    print '<!-- debut cartouche rapport -->
	<div class="tabs">
	<a  id="active" class="tab" href="readFlightsBalloon.php?ballon=' . $idBallon . '">Carnet de vol</a>
	<a  class="tab" href="readBalloonInc.php?ballon=' . $idBallon . '">Incidents</a>
	</div>';
    print '<div class="tabBar">';
    print "<form name='readBalloon' action=\"readFlightsBalloon.php\" method=\"get\">\n";
    print '<input type="hidden" name="mode" value="SELECT">';
    print '<table width="100%" class="border">';
    print '<tr><td>Ballon</td><td colspan="3">';
    if ($user->rights->flightLog->vol->detail) {
        $form->select_balloons($idBallon);
    } else {
        $query2 = 'SELECT * FROM llx_bbc_ballons';
        $query2.= ' WHERE `fk_responsable` = ' . $user->id;
        $query2.= ' OR `fk_co_responsable` = ' . $user->id;
        $resql2 = $db->query($query2);
        if ($resql2) {
            $num = $db->num_rows($resql2);
            $i = 0;
            if ($num) {
                print '<select name="ballon" class="flat">';
                while ($i < $num) {
                    $obj = $db->fetch_object($resql2); //vol
                    print '<option ' . ($obj->rowid == $idBallon ? 'selected="selected"' : '') . ' value="' . $obj->rowid . '">' . $obj->immat . '</option>';
                    $i++;
                }
                print '</select>';
            } else {
                //il n'est pas titulaire d'un ballon
                accessforbidden("Vous n'&ecirc;tes pas titulaire du ballon");
            }
        }
    }

    print'</td></tr>';
    //titulaire
    print '<tr>';
    print '<td>Titulaire</.td>';
    print '<td>' . $titulaire->getLoginUrl(1) . '</.td>';
    print '</tr>';
    //Vol initial
    print '<tr>';
    print '<td>Bapteme</.td>';
    print '<td>' . dol_date('d-m-Y', $ballon->date) . '</.td>';
    print '</tr>';

    $num = 0;
    if ($resql) {
        $num = $db->num_rows($resql);
        //Nbr de vols
        print '<tr>';
        print '<td>Nombre de vol(s)</td>';
        print '<td>' . $num . '</td>';
        print '</tr>';
    }
    // Date start
    if (GETPOST('datep', 'int', 1))
        $datep = dol_stringtotime(GETPOST('datep', 'int', 1), 0);
    print '<tr><td width="30%" nowrap="nowrap"><span>Debut</span></td><td>';
    $form->select_date($datep, 'ap', 0, 0, 1, "readBalloon", 1, 1, 0, 0);
    print '</td></tr>';

    // Date end
    if (GETPOST('datef', 'int', 1))
        $datef = dol_stringtotime(GETPOST('datef', 'int', 1), 0);
    print '<tr><td>Fin</span></td><td>';
    $form->select_date($datef, 'p2', 0, 0, 1, "readBalloon", 1, 1, 0, 0);
    print '</td></tr>';
    print '<tr><td colspan="4" align="center"><input type="submit" class="button" name="submit" value="Rafraichir"></td></tr></table>';
    print '</form></div>';

    print '<table class="border" width="100%">';
    $i = 0;
    if ($num) {
        print '<tr class="liste_titre">';
        print '<td class="liste_titre"> identifiant </td>';
        print '<td class="liste_titre"> Type </td>';
        print '<td class="liste_titre"> Date </td>';
        print '<td class="liste_titre"> Ballon </td>';
        print '<td class="liste_titre"> Pilote </td>';
        print '<td class="liste_titre"> Lieu depart </td>';
        print '<td class="liste_titre"> Lieu arrivee </td>';
        print '<td class="liste_titre"> Heure depart</td>';
        print '<td class="liste_titre"> Heure Arrivee </td>';
        print '<td class="liste_titre"> Duree (min) </td>';
        print '<td class="liste_titre"> Nbr Pax </td>';
        print '<td class="liste_titre"> Rem </td>';
        print '<td class="liste_titre"> Incidents </td>';
        print '<td class="liste_titre"> KM </td>';
        print '<td class="liste_titre"> Justificatif KM </td>';
        if ($user->rights->flightLog->vol->status) {
            print '<td class="liste_titre"> Statut </td>';
        }
        print'</tr>';
        while ($i < $num) {
            $obj = $db->fetch_object($resql); //vol
            print '<tr>';
            if ($obj) {
                $pilot = New User($db); //pilot
                $pilot->fetch($obj->fk_pilot);
                print '<td><a href="fiche.php?vol=' . $obj->idBBC_vols . '">' . $obj->idBBC_vols . '</a></td>';
                print '<td>' . $obj->fk_type . '</td>';
                print '<td>' . $obj->date . '</td>';
                print '<td>' . $ballon->immat . '</td>';
                print '<td> <a href="' . DOL_URL_ROOT . '/user/fiche.php?id=' . $obj->fk_pilot . '">' . img_object($langs->trans("ShowUser"), "user") . ' ' . $pilot->getFullName($langs) . '</a></td>';
                print '<td>' . $obj->lieuD . '</td>';
                print '<td>' . $obj->lieuA . '</td>';
                print '<td>' . $obj->heureD . '</td>';
                print '<td>' . $obj->heureA . '</td>';
                print '<td>' . $obj->time . '</td>';
                print '<td>' . $obj->nbrPax . '</td>';
                print '<td>' . $obj->remarque . '</td>';
                print '<td>' . $obj->incidents . '</td>';
                print '<td>' . $obj->kilometers . '</td>';
                print '<td>' . $obj->justif_kilometers . '</td>';
                if ($user->rights->flightLog->vol->status) {
                    $vol = new Bbc_vols($db);
                    $vol->fetch($obj->idBBC_vols);
                    print '<td>' . $vol->getStatus();
                    //					print '<form action="readFlightsBalloon.php" method="post>';
                    //					print '<input type="hidden" name="vol" value="'.$obj->idBBC_vols.'"/>';
                    //					print '<input type="hidden" name="action" value="valider"/>';
                    //					print '<input class="butAction" type="submit" value="Valider"/>';
                    //					print '</form>';
                    print '</td>';
                }
            }
            print'</tr>';
            $i++;
        }
    }
    print'</table>';
}
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
