<?php

function sqlToArray(DoliDb $db, $sql, $total = true, $year = '')
{
    $resql = $db->query($sql);
    $array = array();
    if ($resql) {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
            while ($i < $num) {
                $obj = $db->fetch_object($resql); //vol
                if ($obj) {
                    $array[$obj->pilot][$obj->type]['time'] = $obj->time;
                    $array[$obj->pilot][$obj->type]['count'] = $obj->nbr;
                    $array[$obj->pilot]['name'] = $obj->prenom . ' ' . $obj->nom;
                    $array[$obj->pilot]['id'] = $obj->pilot;
                }
                $i++;
            }
        }
    }

    //total time for pilot
    $sql = 'SELECT llx_user.name, llx_user.firstname,llx_user.rowid, count(idBBC_vols) as total FROM llx_bbc_vols LEFT JOIN llx_user ON rowid = fk_organisateur WHERE YEAR(date) = \'' . $year . '\' AND fk_type IN (1,2) GROUP BY fk_organisateur';
    $resql = $db->query($sql);
    if ($resql && $total) {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
            while ($i < $num) {
                $obj = $db->fetch_object($resql); //vol

                if ($obj) {
                    $array[$obj->rowid]['name'] = $obj->firstname . ' ' . $obj->name;
                    $array[$obj->rowid]['orga']['count'] = $obj->total;
                }
                $i++;
            }
        }
    }

    return $array;
}

/**
 * Return list of flight type
 *
 * @param   mixed $selected  Preselected type
 * @param   mixed $htmlname  Name of field in form
 * @param   mixed $showempty Add an empty field
 */
function select_flight_type($selected = '1', $htmlname = 'type', $showempty = 0)
{

    global $db, $langs, $user;
    $langs->load("trips");

    print '<select class="flat" name="' . $htmlname . '">';

    $resql = $db->query("SELECT B.idType,B.numero,B.nom FROM llx_bbc_types as B WHERE selectable=1");
    if ($resql) {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
            while ($i < $num) {
                $obj = $db->fetch_object($resql);
                if ($obj) {
                    print '<option value="' . $obj->idType . '"';
                    if ($obj->numero == $selected) {
                        print ' selected="selected"';
                    }
                    print '>';
                    echo $obj->numero . '-' . $obj->nom;
                    print "</option>";
                }
                $i++;
            }
        }
    }

    print '</select>';
}

/**
 *        Return list of Balloons
 *
 * @param   mixed  $selected  Preselected Balloon
 * @param   mixed  $htmlname  Name of field in form
 * @param    mixed $showempty Add an empty field
 */
function select_balloons($selected = '', $htmlname = 'ballon', $showempty = 0, $showimmat = 0, $showDeclasse = 1)
{

    global $db, $langs, $user;
    $langs->load("trips");
    print '<!-- select_balloons in form class -->';
    print '<select class="flat" name="' . $htmlname . '">';

    print '<option value="-1"';
    if ($selected == -1) {
        print ' selected="selected"';
    }
    print '>&nbsp;</option>';

    if (!$showDeclasse) {
        $resql = $db->query("SELECT B.immat,B.rowid FROM llx_bbc_ballons as B WHERE is_disable = false ");
    } else {
        $resql = $db->query("SELECT B.immat,B.rowid FROM llx_bbc_ballons as B");
    }

    if ($resql) {
        $num = $db->num_rows($resql);
        $i = 0;
        if ($num) {
            while ($i < $num) {
                $obj = $db->fetch_object($resql);
                if ($obj) {
                    if ($showimmat) {
                        print '<option value="' . $obj->immat . '"';
                    } else {
                        print '<option value="' . $obj->rowid . '"';
                    }
                    if ($obj->rowid == $selected) {
                        print ' selected="selected"';
                    }
                    print '>';
                    echo strtoupper($obj->immat);
                    print "</option>";
                }
                $i++;
            }
        }
    }

    print '</select>';
}

?>
