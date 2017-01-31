<?php
/**
 * \file    mypage.php
 * \ingroup mymodule
 * \brief   Example PHP page.
 *
 * read flights
 */

// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
    require '../../documents/custom/main.inc.php'; // From "custom" directory
}

global $db, $langs, $user;

dol_include_once('/flightLog/class/bbcvols.class.php');
dol_include_once("/flightBalloon/bbc_ballons.class.php");
dol_include_once('/flightLog/class/bbctypes.class.php');
dol_include_once("/flightLog/inc/other.php");

// Load translation files required by the page
$langs->load("mymodule@mymodule");

// Get parameters
$myparam = isset($_GET["myparam"])?$_GET["myparam"]:'';

// Protection if the user can't acces to the module
if (!$user->rights->flightLog->vol->access)
{
	accessforbidden();
}



/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/

//l'utilisateur a le droit de selectionner le ballon qu'il veut
if (($user->rights->flightLog->vol->detail))
{
	if(isset($_GET["ballon"])){
		$idBallon = $_GET['ballon'];
	}else{
		$idBallon =1;
	}
	
}else{
	
	//l'utilisateur n'a pas le choix du ballon
	//il est titulaire d'un ballon
	$query = 'SELECT * FROM llx_bbc_ballons';
	$query.= ' WHERE `fk_responsable` = '.$user->id;
	$resql = $db->query($query);
	if($resql){
		$num = $db->num_rows($resql);
		$i = 0;
		if($num){
			print'</tr>';
			while($i <$num){
				$obj = $db->fetch_object($resql); //vol
				if($obj){
					$idBallon = ($obj->rowid);
				}
			}
		}else{
			//il n'est pas titulaire d'un ballon
			accessforbidden("Vous n'&ecirc;tes pas titulaire du ballon");
		}
	}
}
if($idBallon != -1){
	
	//balloon with ID
	$ballon = New Bbc_ballons($db);
        
	if($ballon->fetch($idBallon)==-1){
		print "ERROR".$idBallon."<br/>";
	}
	//titulaire with ballon ID
        $titulaire = new User($db);
        $titulaire->fetch($ballon->fk_responsable);
        //flight with balloon ID
	$query = 'SELECT *, TIMEDIFF(heureA,heureD) AS time FROM llx_bbc_vols';
	$query.= ' WHERE `BBC_ballons_idBBC_ballons` = '.$ballon->id;
	$query.= ' AND `incidents` NOT LIKE \'RAS\'';
	$query.= ' AND `incidents` NOT LIKE \'\'';
	$query.= ' ORDER BY date';
	$resql = $db->query($query);
}



/***************************************************
* PAGE
*
* Put here all code to build page
****************************************************/

llxHeader('','Carnet de vol','');
if($msg && $idBallon != -1){
	print $msg;
}else{
	$form=new Form($db);
	
	print '<!-- debut cartouche rapport -->
	<div class="tabs">
	<a  class="tab" href="readFlightsBalloon.php?ballon='.$idBallon.'">Carnet de vol</a>
	<a id="active" class="tab" href="readBalloonInc.php?ballon='.$idBallon.'">Incidents</a>
	</div>';
	print '<div class="tabBar">';
	print "<form name='readBalloon' action=\"readFlightsBalloon.php\" method=\"get\">\n";
	print '<input type="hidden" name="mode" value="SELECT">';
	print '<table width="100%" class="border">';
	print '<tr><td>Ballon</td><td colspan="3">';
	if($user->rights->flightLog->vol->detail){
		select_balloons($idBallon);
	}else{
		print $ballon->immat;
	}
	
	print'</td></tr>';
        //titulaire
        print '<tr>';
        print '<td>Titulaire</td>';
        print '<td>'.$titulaire->getLoginUrl(1).'</td>';
        print '</tr>';
        //Vol initial
        print '<tr>';
        print '<td>Bapteme</.td>';
        print '<td>'.dol_print_date($ballon->date, 'dayrfc').'</td>';
        print '</tr>';
        $num = 0;
	if($resql){
		//fetch pilot
		
		$num = $db->num_rows($resql);
                //>Nombre d'incidences
                print '<tr>';
                print '<td>Nombre d\'incidences</.td>';
                print '<td>' . $num . '</.td>';
                print '</tr>';
        }
                print '<tr><td colspan="4" align="center"><input type="submit" class="button" name="submit" value="Rafraichir"></td></tr></table>';
                print '</form></div>';


                print '<table class="border" width="100%">';
		$i = 0;
		if($num){
			print '<tr class="liste_titre">';
			print '<td class="liste_titre"> identifiant </td>';
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
			if($user->rights->flightLog->vol->status){
				print '<td class="liste_titre"> Statut </td>';
			}
			print'</tr>';
			while($i <$num){
				$obj = $db->fetch_object($resql); //vol
				print '<tr>';
				if($obj){
					$pilot = New User($db); //pilot
					$pilot->fetch($obj->fk_pilot);
					print '<td><a href="fiche.php?vol='.$obj->idBBC_vols.'">'.$obj->idBBC_vols.'</a></td>';
					print '<td>'.$obj->date.'</td>';
					print '<td>'.$ballon->immat.'</td>';
					print '<td>'.$pilot->getNomUrl().'</a></td>';
					print '<td>'.$obj->lieuD.'</td>';
					print '<td>'.$obj->lieuA.'</td>';
					print '<td>'.$obj->heureD.'</td>';
					print '<td>'.$obj->heureA.'</td>';
					print '<td>'.$obj->time.'</td>';
					print '<td>'.$obj->nbrPax.'</td>';
					print '<td>'.$obj->remarque.'</td>';
					print '<td>'.$obj->incidents.'</td>';
					if($user->rights->flightLog->vol->status){
						$vol = new Bbcvols($db);
						$vol->fetch($obj->idBBC_vols);
                        print '<td>' . $vol->getStatus().'</td>';
					}
				}
				print'</tr>';
				$i++;
			}
		}	
		print'</table>';
	
}
/***************************************************
* LINKED OBJECT BLOCK
*
* Put here code to view linked object
****************************************************/
//$somethingshown=$myobject->showLinkedObjectBlock();

// End of page
$db->close();
llxFooter('$Date: 2011/07/31 22:21:57 $ - $Revision: 1.19 $');
?>
