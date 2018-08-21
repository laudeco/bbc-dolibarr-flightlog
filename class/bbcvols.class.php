<?php
/* Copyright (C) 2007-2012  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2014       Juanjo Menent       <jmenent@2byte.es>
 * Copyright (C) 2015       Florian Henry       <florian.henry@open-concept.pro>
 * Copyright (C) 2015       Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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
 * \file    flightlog/bbcvols.class.php
 * \ingroup flightlog
 * \brief   This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *          Put some comments here
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/flightballoon/class/bbc_ballons.class.php';
require_once DOL_DOCUMENT_ROOT . '/flightlog/class/bbctypes.class.php';
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';

/**
 * Class Bbcvols
 *
 * Put here description of your class
 *
 * @see CommonObject
 */
class Bbcvols extends CommonObject
{
    /**
     * @var string Id to identify managed objects
     */
    public $element = 'flightlog_bbcvols';
    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'bbc_vols';

    public $idBBC_vols;
    public $date = '';
    public $lieuD;
    public $lieuA;
    public $heureD;
    public $heureA;
    public $BBC_ballons_idBBC_ballons;
    public $nbrPax;
    public $remarque;
    public $incidents;
    public $fk_type;
    public $fk_pilot;
    public $fk_organisateur;
    public $is_facture;
    public $kilometers;
    public $cost;
    public $fk_receiver;
    public $justif_kilometers;
    public $date_creation;
    public $date_update;

    /**
     * @var Bbc_ballons
     */
    private $balloon;

    /**
     * @var User
     */
    private $pilot;

    /**
     * @var string
     */
    private $passengerNames;

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var Commande
     */
    private $order;

    /**
     * @return int
     */
    public function getIdBBCVols()
    {
        return (int) $this->idBBC_vols;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->getIdBBCVols();
    }

    /**
     * @param string|int $ref
     *
     * @return $this
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
        return $this;
    }

    /**
     * Constructor
     *
     * @param DoliDb $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        $this->db = $db;
        $this->cost = 0;

        $this->passengerNames = '';
    }

    /**
     * Create a flight
     *
     * @param  User $user      User that creates
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     *
     * @return int <0 if KO, Id of created object if OK
     * @throws Exception
     */
    public function create(User $user, $notrigger = false)
    {
        dol_syslog(__METHOD__, LOG_DEBUG);

        $error = 0;

        // Clean parameters

        if (isset($this->idBBC_vols)) {
            $this->idBBC_vols = trim($this->idBBC_vols);
        }
        if (isset($this->lieuD)) {
            $this->lieuD = trim($this->lieuD);
        }
        if (isset($this->lieuA)) {
            $this->lieuA = trim($this->lieuA);
        }
        if (isset($this->heureD)) {
            $this->heureD = trim($this->heureD) . '00';
        }
        if (isset($this->heureA)) {
            $this->heureA = trim($this->heureA) . '00';
        }
        if (isset($this->BBC_ballons_idBBC_ballons)) {
            $this->BBC_ballons_idBBC_ballons = trim($this->BBC_ballons_idBBC_ballons);
        }
        if (isset($this->nbrPax)) {
            $this->nbrPax = trim($this->nbrPax);
        }
        if (isset($this->remarque)) {
            $this->remarque = trim($this->remarque);
        }
        if (isset($this->incidents)) {
            $this->incidents = trim($this->incidents);
        }
        if (isset($this->fk_type)) {
            $this->fk_type = trim($this->fk_type);
        }
        if (isset($this->fk_pilot)) {
            $this->fk_pilot = trim($this->fk_pilot);
        }
        if (isset($this->fk_organisateur)) {
            $this->fk_organisateur = trim($this->fk_organisateur);
        }
        if (isset($this->is_facture)) {
            $this->is_facture = trim($this->is_facture);
        }
        if (isset($this->kilometers)) {
            $this->kilometers = trim($this->kilometers);
        }
        if (isset($this->cost)) {
            $this->cost = trim($this->cost);
        }
        if (isset($this->fk_receiver)) {
            $this->fk_receiver = trim($this->fk_receiver);
        }
        if (isset($this->justif_kilometers)) {
            $this->justif_kilometers = trim($this->justif_kilometers);
        }
        if (isset($this->passengerNames)) {
            $this->passengerNames = trim($this->passengerNames);
        }
        if (isset($this->orderId)) {
            $this->orderId = trim($this->orderId);
        }

        // Insert request
        $sql = 'INSERT INTO ' . MAIN_DB_PREFIX . $this->table_element . '(';

        $sql .= 'date,';
        $sql .= 'lieuD,';
        $sql .= 'lieuA,';
        $sql .= 'heureD,';
        $sql .= 'heureA,';
        $sql .= 'BBC_ballons_idBBC_ballons,';
        $sql .= 'nbrPax,';
        $sql .= 'remarque,';
        $sql .= 'incidents,';
        $sql .= 'fk_type,';
        $sql .= 'fk_pilot,';
        $sql .= 'fk_organisateur,';
        $sql .= 'is_facture,';
        $sql .= 'kilometers,';
        $sql .= 'cost,';
        $sql .= 'fk_receiver,';
        $sql .= 'justif_kilometers,';
        $sql .= 'date_creation,';
        $sql .= 'date_update,';
        $sql .= 'passenger_names,';
        $sql .= 'order_id';

        $sql .= ') VALUES (';

        $sql .= ' ' . (!isset($this->date) || dol_strlen($this->date) == 0 ? 'NULL' : "'" . $this->db->idate($this->date) . "'") . ',';
        $sql .= ' ' . (!isset($this->lieuD) ? 'NULL' : "'" . $this->db->escape($this->lieuD) . "'") . ',';
        $sql .= ' ' . (!isset($this->lieuA) ? 'NULL' : "'" . $this->db->escape($this->lieuA) . "'") . ',';
        $sql .= ' ' . (!isset($this->heureD) ? 'NULL' : "'" . $this->heureD . "'") . ',';
        $sql .= ' ' . (!isset($this->heureA) ? 'NULL' : "'" . $this->heureA . "'") . ',';
        $sql .= ' ' . (!isset($this->BBC_ballons_idBBC_ballons) ? 'NULL' : $this->BBC_ballons_idBBC_ballons) . ',';
        $sql .= ' ' . (!isset($this->nbrPax) ? 'NULL' : "'" . $this->db->escape($this->nbrPax) . "'") . ',';
        $sql .= ' ' . (!isset($this->remarque) ? 'NULL' : "'" . $this->db->escape($this->remarque) . "'") . ',';
        $sql .= ' ' . (!isset($this->incidents) ? 'NULL' : "'" . $this->db->escape($this->incidents) . "'") . ',';
        $sql .= ' ' . (!isset($this->fk_type) || (int)$this->fk_type === -1 ? 'NULL' : $this->fk_type) . ',';
        $sql .= ' ' . (!isset($this->fk_pilot) || (int)$this->fk_pilot === -1 ? 'NULL' : $this->fk_pilot) . ',';
        $sql .= ' ' . (!isset($this->fk_organisateur) || (int)$this->fk_organisateur === -1 ? 'NULL' : $this->fk_organisateur) . ',';
        $sql .= ' ' . (!isset($this->is_facture) ? '0' : $this->is_facture) . ',';
        $sql .= ' ' . (!isset($this->kilometers) || empty($this->kilometers) ? '0' : $this->kilometers) . ',';
        $sql .= ' ' . (!isset($this->cost) ? 'NULL' : "'" . $this->db->escape($this->cost) . "'") . ',';
        $sql .= ' ' . (!isset($this->fk_receiver) || (int)$this->fk_receiver === -1 ? 'NULL' : $this->fk_receiver) . ',';
        $sql .= ' ' . (!isset($this->justif_kilometers) ? 'NULL' : "'" . $this->db->escape($this->justif_kilometers) . "'") . ',';
        $sql .= ' ' . "'" . date('Y-m-d H:i:s') . "'" . ',';
        $sql .= ' ' . "'" . date('Y-m-d H:i:s') . "'" . ',';
        $sql .= ' ' . "'" . $this->passengerNames . "'" . ',';
        $sql .= ' ' . (!isset($this->orderId) || (int)$this->orderId === -1 ? 'NULL' : $this->orderId) . '';

        $sql .= ')';

        $this->db->begin();

        $resql = $this->db->query($sql);
        if (!$resql) {
            $error++;
            $this->errors[] = 'Error ' . $this->db->lasterror();
            dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
        }

        if (!$error) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);

            if (!$notrigger) {
                $result = $this->call_trigger('BBC_FLIGHT_CREATED', $user);
                if ($result < 0) {
                    $error++;
                }
            }
        }

        // Commit or rollback
        if ($error) {
            $this->db->rollback();

            return -1 * $error;
        }

        $this->db->commit();
        return $this->id;
    }

    /**
     * Load object in memory from the database
     *
     * @param int    $id  Id object
     * @param string $ref Ref
     *
     * @return int <0 if KO, 0 if not found, >0 if OK
     * @throws Exception
     */
    public function fetch($id, $ref = null)
    {
        dol_syslog(__METHOD__, LOG_DEBUG);

        $sql = 'SELECT';
        $sql .= " t.idBBC_vols,";
        $sql .= " t.date,";
        $sql .= " t.lieuD,";
        $sql .= " t.lieuA,";
        $sql .= " t.heureD,";
        $sql .= " t.heureA,";
        $sql .= " t.BBC_ballons_idBBC_ballons,";
        $sql .= " t.nbrPax,";
        $sql .= " t.remarque,";
        $sql .= " t.incidents,";
        $sql .= " t.fk_type,";
        $sql .= " t.fk_pilot,";
        $sql .= " t.fk_organisateur,";
        $sql .= " t.is_facture,";
        $sql .= " t.kilometers,";
        $sql .= " t.cost,";
        $sql .= " t.fk_receiver,";
        $sql .= " t.justif_kilometers,";
        $sql .= " t.date_creation,";
        $sql .= " t.date_update,";
        $sql .= " t.passenger_names,";
        $sql .= " t.order_id";


        $sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
        if (null !== $ref) {
            $sql .= ' WHERE t.ref = ' . '\'' . $ref . '\'';
        } else {
            $sql .= ' WHERE t.idBBC_vols = ' . $id;
        }

        $resql = $this->db->query($sql);
        if ($resql) {
            $numrows = $this->db->num_rows($resql);
            if ($numrows) {
                $obj = $this->db->fetch_object($resql);

                $this->id = $obj->idBBC_vols;

                $this->idBBC_vols = $obj->idBBC_vols;
                $this->date = $this->db->jdate($obj->date);
                $this->lieuD = $obj->lieuD;
                $this->lieuA = $obj->lieuA;
                $this->heureD = $obj->heureD;
                $this->heureA = $obj->heureA;
                $this->BBC_ballons_idBBC_ballons = $obj->BBC_ballons_idBBC_ballons;
                $this->nbrPax = $obj->nbrPax;
                $this->remarque = $obj->remarque;
                $this->incidents = $obj->incidents;
                $this->fk_type = $obj->fk_type;
                $this->fk_pilot = $obj->fk_pilot;
                $this->fk_organisateur = $obj->fk_organisateur;
                $this->is_facture = $obj->is_facture;
                $this->kilometers = $obj->kilometers;
                $this->cost = $obj->cost;
                $this->fk_receiver = $obj->fk_receiver;
                $this->justif_kilometers = $obj->justif_kilometers;
                $this->date_creation = $obj->date_creation;
                $this->date_update = $obj->date_update;
                $this->passengerNames = $obj->passenger_names;
                $this->orderId = $obj->order_id;

                $this->balloon = $this->fetchBalloon();
                $this->pilot = $this->fetchUser($this->fk_pilot);
                $this->fetchOrder();
            }
            $this->db->free($resql);

            if ($numrows) {
                return 1;
            } else {
                return 0;
            }
        }

        $this->errors[] = 'Error ' . $this->db->lasterror();
        dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
        return -1;
    }

    /**
     * @param  User $user      User that modifies
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     *
     * @return int <0 if KO, >0 if OK
     * @throws Exception
     */
    public function update(User $user, $notrigger = false)
    {
        $error = 0;

        dol_syslog(__METHOD__, LOG_DEBUG);

        // Clean parameters

        if (isset($this->idBBC_vols)) {
            $this->idBBC_vols = trim($this->idBBC_vols);
        }
        if (isset($this->lieuD)) {
            $this->lieuD = trim($this->lieuD);
        }
        if (isset($this->lieuA)) {
            $this->lieuA = trim($this->lieuA);
        }
        if (isset($this->heureD)) {
            $this->heureD = trim($this->heureD);
        }
        if (isset($this->heureA)) {
            $this->heureA = trim($this->heureA);
        }
        if (isset($this->BBC_ballons_idBBC_ballons)) {
            $this->BBC_ballons_idBBC_ballons = trim($this->BBC_ballons_idBBC_ballons);
        }
        if (isset($this->nbrPax)) {
            $this->nbrPax = trim($this->nbrPax);
        }
        if (isset($this->remarque)) {
            $this->remarque = trim($this->remarque);
        }
        if (isset($this->incidents)) {
            $this->incidents = trim($this->incidents);
        }
        if (isset($this->fk_type)) {
            $this->fk_type = trim($this->fk_type);
        }
        if (isset($this->fk_pilot)) {
            $this->fk_pilot = trim($this->fk_pilot);
        }
        if (isset($this->fk_organisateur)) {
            $this->fk_organisateur = trim($this->fk_organisateur);
        }
        if (isset($this->is_facture)) {
            $this->is_facture = trim($this->is_facture);
        }
        if (isset($this->kilometers)) {
            $this->kilometers = trim($this->kilometers);
        }
        if (isset($this->cost)) {
            $this->cost = trim($this->cost);
        }
        if (isset($this->fk_receiver)) {
            $this->fk_receiver = trim($this->fk_receiver);
        }
        if (isset($this->justif_kilometers)) {
            $this->justif_kilometers = trim($this->justif_kilometers);
        }
        if (isset($this->passengerNames)) {
            $this->passengerNames = trim($this->passengerNames);
        }
        if (isset($this->orderId)) {
            $this->orderId = trim($this->orderId);
        }


        // Check parameters
        // Put here code to add a control on parameters values

        // Update request
        $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element . ' SET';

        $sql .= ' date = ' . (!isset($this->date) || dol_strlen($this->date) != 0 ? "'" . $this->db->idate($this->date) . "'" : 'null') . ',';
        $sql .= ' lieuD = ' . (isset($this->lieuD) ? "'" . $this->db->escape($this->lieuD) . "'" : "null") . ',';
        $sql .= ' lieuA = ' . (isset($this->lieuA) ? "'" . $this->db->escape($this->lieuA) . "'" : "null") . ',';
        $sql .= ' heureD = ' . (isset($this->heureD) ? "'" . $this->heureD . "'" : "null") . ',';
        $sql .= ' heureA = ' . (isset($this->heureA) ? "'" . $this->heureA . "'" : "null") . ',';
        $sql .= ' BBC_ballons_idBBC_ballons = ' . (isset($this->BBC_ballons_idBBC_ballons) ? $this->BBC_ballons_idBBC_ballons : "null") . ',';
        $sql .= ' nbrPax = ' . (isset($this->nbrPax) ? "'" . $this->db->escape($this->nbrPax) . "'" : "null") . ',';
        $sql .= ' remarque = ' . (isset($this->remarque) ? "'" . $this->db->escape($this->remarque) . "'" : "null") . ',';
        $sql .= ' incidents = ' . (isset($this->incidents) ? "'" . $this->db->escape($this->incidents) . "'" : "null") . ',';
        $sql .= ' fk_type = ' . (isset($this->fk_type) && (int)$this->fk_type > 0 ? $this->fk_type : "null") . ',';
        $sql .= ' fk_pilot = ' . (isset($this->fk_pilot) && (int)$this->fk_pilot > 0 ? $this->fk_pilot : "null") . ',';
        $sql .= ' fk_organisateur = ' . (isset($this->fk_organisateur) && (int)$this->fk_organisateur > 0 ? $this->fk_organisateur : "null") . ',';
        $sql .= ' is_facture = ' . (isset($this->is_facture) ? $this->is_facture : "0") . ',';
        $sql .= ' kilometers = ' . (!empty($this->kilometers) ? $this->kilometers : "0") . ',';
        $sql .= ' cost = ' . (isset($this->cost) ? "'" . $this->db->escape($this->cost) . "'" : "''") . ',';
        $sql .= ' fk_receiver = ' . (isset($this->fk_receiver) && (int)$this->fk_receiver > 0 ? $this->fk_receiver : "null") . ',';
        $sql .= ' justif_kilometers = ' . (isset($this->justif_kilometers) ? "'" . $this->db->escape($this->justif_kilometers) . "'," : "'',");
        $sql .= ' date_update = ' . "'" . date('Y-m-d H:i:s') . "',";
        $sql .= ' passenger_names = ' . "'" . trim($this->passengerNames) . "',";
        $sql .= ' order_id = ' .  (!isset($this->orderId) || (int)$this->orderId === -1 ? 'null' : $this->orderId);

        $sql .= ' WHERE idBBC_vols=' . $this->idBBC_vols;

        $this->db->begin();

        $resql = $this->db->query($sql);
        if (!$resql) {
            $error++;
            $this->errors[] = 'Error ' . $this->db->lasterror();
            dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
        }

        if (!$error && !$notrigger) {
            $result = $this->call_trigger('BBC_FLIGHT_UPDATED', $user);
            if ($result < 0) {
                $error++;
            }
        }

        // Commit or rollback
        if ($error) {
            $this->db->rollback();

            return -1 * $error;
        }

        $this->db->commit();
        return 1;
    }

    /**
     * Delete object in database
     *
     * @param User $user      User that deletes
     * @param bool $notrigger false=launch triggers after, true=disable triggers
     *
     * @return int <0 if KO, >0 if OK
     * @throws Exception
     */
    public function delete(User $user, $notrigger = false)
    {
        dol_syslog(__METHOD__, LOG_DEBUG);

        $error = 0;

        $this->db->begin();

        if (!$error) {
            if (!$notrigger) {
                $result = $this->call_trigger('BBC_FLIGHT_DELETED', $user);
                if ($result < 0) {
                    $error++;
                }
            }
        }

        if (!$error) {
            $sql = 'DELETE FROM ' . MAIN_DB_PREFIX . $this->table_element;
            $sql .= ' WHERE idBBC_vols=' . $this->idBBC_vols;

            $resql = $this->db->query($sql);
            if (!$resql) {
                $error++;
                $this->errors[] = 'Error ' . $this->db->lasterror();
                dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
            }
        }

        // Commit or rollback
        if ($error) {
            $this->db->rollback();
            return -1 * $error;
        }

        $this->db->commit();
        return 1;
    }

    /**
     *  Return a link to the user card (with optionaly the picto)
     *    Use this->id,this->lastname, this->firstname
     *
     * @param    int     $withpicto Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
     * @param    string  $option    On what the link point to
     * @param    integer $notooltip 1=Disable tooltip
     * @param    int     $maxlen    Max length of visible user name
     * @param  string    $morecss   Add more css on link
     *
     * @return    string                        String with URL
     */
    public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $maxlen = 24, $morecss = '')
    {
        global $langs;

        $result = '';

        $label = '<u>' . $langs->trans("MyModule") . '</u>';
        $label .= '<div width="100%">';
        $label .= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->idBBC_vols . '<br>';
        $label .= '<b>' . $langs->trans('Date') . ':</b> ' . dol_print_date($this->date, '%d-%m-%Y') . '<br/>';
        $label .= '<b>' . $langs->trans('From') . ':</b> ' . $this->lieuD . '<br/>';
        $label .= '<b>' . $langs->trans('To') . ':</b> ' . $this->lieuA . '<br/>';
        $label .= '</div>';

        $link = '<a href="' . DOL_URL_ROOT . '/flightlog/card.php?id=' . $this->idBBC_vols . '"';
        $link .= ($notooltip ? '' : ' title="' . dol_escape_htmltag($label,
                1) . '" class="classfortooltip' . ($morecss ? ' ' . $morecss : '') . '"');
        $link .= '>';
        $linkend = '</a>';

        if ($withpicto) {
            $result .= ($link . img_object(($notooltip ? '' : $label), 'label',
                    ($notooltip ? '' : 'class="classfortooltip"')) . $linkend);
            if ($withpicto != 2) {
                $result .= ' ';
            }
        }
        $result .= $link . $this->idBBC_vols . $linkend;
        return $result;
    }

    /**
     *  Retourne le libelle du status d'un user (actif, inactif)
     *
     * @param    int $mode 0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
     *
     * @return    string                   Label of status
     */
    public function getLibStatut($mode = 0)
    {
        return $this->LibStatut($this->is_facture, $mode);
    }

    /**
     * Renvoi le libelle d'un status donne
     *
     * @param int $status Id status
     * @param int $mode   0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
     *
     * @return string                    Label of status
     */
    private function LibStatut($status, $mode = 0)
    {
        global $langs;

        $billDone = $langs->trans('Facturé');
        $billNotDone = $langs->trans('Ouvert');

        if ($mode == 0) {
            if ($status == 1) {
                return $billDone;
            }
            if ($status == 0) {
                return $billNotDone;
            }
        }
        if ($mode == 1) {
            if ($status == 1) {
                return $billDone;
            }
            if ($status == 0) {
                return $billNotDone;
            }
        }
        if ($mode == 2) {
            if ($status == 1) {
                return img_picto($billDone, 'statut4') . ' ' . $billDone;
            }
            if ($status == 0) {
                return img_picto($billNotDone, 'statut5') . ' ' . $billNotDone;
            }
        }
        if ($mode == 3) {
            if ($status == 1) {
                return img_picto($billDone, 'statut4');
            }
            if ($status == 0) {
                return img_picto($billNotDone, 'statut5');
            }
        }
        if ($mode == 4) {
            if ($status == 1) {
                return img_picto($billDone, 'statut4') . ' ' . $billDone;
            }
            if ($status == 0) {
                return img_picto($billNotDone, 'statut5') . ' ' . $billNotDone;
            }
        }
        if ($mode == 5) {
            if ($status == 1) {
                return $billDone . ' ' . img_picto($billDone, 'statut4');
            }
            if ($status == 0) {
                return $billNotDone . ' ' . img_picto($billNotDone, 'statut5');
            }
        }

        return "";
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->idBBC_vols . " " . $this->getPlaces();
    }

    /**
     * @return string
     */
    public function toString()
    {
        return "" . $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->statut;
    }

    /**
     * @return boolean
     */
    public function hasFacture()
    {
        return count($this->linkedObjectsIds) > 0;
    }

    /**
     * @param int $userId
     *
     * @return User
     */
    private function fetchUser($userId)
    {
        $user = new User($this->db);
        $user->fetch($userId);

        return $user;
    }

    /**
     * @return Bbc_ballons
     */
    private function fetchBalloon()
    {
        $balloon = new Bbc_ballons($this->db);
        $balloon->fetch($this->BBC_ballons_idBBC_ballons);

        return $balloon;
    }

    /**
     * @return Bbc_ballons
     */
    public function getBalloon()
    {
        if (!$this->balloon) {
            $this->balloon = $this->fetchBalloon();
        }

        return $this->balloon;
    }

    /**
     * @return User
     */
    public function getPilot()
    {
        if (!$this->pilot) {
            $this->pilot = $this->fetchUser($this->fk_pilot);
        }

        return $this->pilot;
    }

    /**
     * @return int
     */
    public function getPilotId()
    {
        return (int) $this->fk_pilot;
    }

    /**
     * @return int
     */
    public function getOrganisatorId()
    {
        return (int) $this->fk_organisateur;
    }

    /**
     * @return Bbctypes
     */
    public function getFlightType()
    {
        $flightType = new Bbctypes($this->db);
        $flightType->fetch($this->fk_type);

        return $flightType;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->remarque;
    }

    /**
     * @return string
     */
    public function getIncident()
    {
        return $this->incidents;
    }

    /**
     * Return true if the number of pax is greater than 0
     *
     * @return boolean
     */
    public function hasPax()
    {
        return (int) $this->nbrPax > 0;
    }

    /**
     * Regarding the type of the flight give an indication if the flight must have pax to be valid.
     *
     * @return boolean
     */
    public function mustHavePax()
    {
        return $this->getFlightType()->isPaxRequired();
    }

    /**
     * Returns true if the amount requested by the flight is 0.
     *
     * @return boolean
     */
    public function isFree()
    {
        return empty($this->cost);
    }

    /**
     * @return int
     */
    public function getAmountReceived()
    {
        return $this->cost;
    }

    /**
     * @return int
     */
    public function getAmountPerPassenger()
    {
        return $this->cost / $this->nbrPax;
    }

    /**
     * @return boolean
     */
    public function hasReceiver()
    {
        return !empty($this->fk_receiver);
    }

    /**
     * @return boolean
     */
    public function hasKilometers()
    {
        return !empty($this->kilometers);
    }

    /**
     * @return boolean
     */
    public function hasKilometersDescription()
    {
        return !empty(trim($this->justif_kilometers));
    }

    /**
     * @return int
     */
    public function getKilometers()
    {
        return (int) $this->kilometers;
    }

    /**
     * @return string
     */
    public function getPlaces()
    {
        return $this->lieuD . ' -> ' . $this->lieuA;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->__toString() . ' - ' . $this->passengerNames;
    }

    /**
     * @return string
     */
    public function getPassengerNames()
    {
        return $this->passengerNames;
    }

    /**
     * @param string $passengerNames
     *
     * @return Bbcvols
     */
    public function setPassengerNames($passengerNames)
    {
        $this->passengerNames = $passengerNames;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfPassengers()
    {
        return (int) $this->nbrPax;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     *
     * @return Bbcvols
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * Is an instruction flight (T6/T7)
     */
    public function isInstruction()
    {
        return $this->getFlightType()->isInstruction();
    }

    /**
     * @return bool
     */
    public function isLinkedToOrder()
    {
        return isset($this->orderId) && $this->orderId > 0;
    }

    /**
     * Fetch the order based on the order id.
     */
    public function fetchOrder()
    {
        if (!$this->isLinkedToOrder()) {
            return $this;
        }

        $this->order = new Commande($this->db);
        $this->order->fetch($this->orderId);

        return $this;
    }

    /**
     * @return Commande
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Flag the flight as billed
     *
     * @return $this
     */
    public function bill()
    {
        $this->is_facture = true;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isBilled()
    {
        return !empty($this->is_facture);
    }

}
