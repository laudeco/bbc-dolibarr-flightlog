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
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class Bbcvols
 *
 * Put here description of your class
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

    /**
     * @var BbcvolsLine[] Lines
     */
    public $lines = array();

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
     * @return int
     */
    public function getIdBBCVols()
    {
        return $this->idBBC_vols;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getIdBBCVols();
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
    }

    /**
     * Create object into database
     *
     * @param  User $user      User that creates
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     *
     * @return int <0 if KO, Id of created object if OK
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


        // Check parameters
        // Put here code to add control on parameters values

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
        $sql .= 'date_update';


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
        $sql .= ' ' . (!isset($this->fk_type) ? 'NULL' : $this->fk_type) . ',';
        $sql .= ' ' . (!isset($this->fk_pilot) ? 'NULL' : $this->fk_pilot) . ',';
        $sql .= ' ' . (!isset($this->fk_organisateur) ? 'NULL' : $this->fk_organisateur) . ',';
        $sql .= ' ' . (!isset($this->is_facture) ? '0' : $this->is_facture) . ',';
        $sql .= ' ' . (!isset($this->kilometers) || empty($this->kilometers) ? '0' : $this->kilometers) . ',';
        $sql .= ' ' . (!isset($this->cost) ? 'NULL' : "'" . $this->db->escape($this->cost) . "'") . ',';
        $sql .= ' ' . (!isset($this->fk_receiver) ? 'NULL' : $this->fk_receiver) . ',';
        $sql .= ' ' . (!isset($this->justif_kilometers) ? 'NULL' : "'" . $this->db->escape($this->justif_kilometers) . "'") . ',';
        $sql .= ' ' . "'" . date('Y-m-d H:i:s') . "'" . ',';
        $sql .= ' ' . "'" . date('Y-m-d H:i:s') . "'" . '';


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
                // Uncomment this and change MYOBJECT to your own tag if you
                // want this action to call a trigger.

                //// Call triggers
                //$result=$this->call_trigger('MYOBJECT_CREATE',$user);
                //if ($result < 0) $error++;
                //// End call triggers
            }
        }

        // Commit or rollback
        if ($error) {
            $this->db->rollback();

            return -1 * $error;
        } else {
            $this->db->commit();

            return $this->id;
        }
    }

    /**
     * Load object in memory from the database
     *
     * @param int    $id  Id object
     * @param string $ref Ref
     *
     * @return int <0 if KO, 0 if not found, >0 if OK
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
        $sql .= " t.date_update";


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
                
                $this->balloon = $this->fetchBalloon();
                $this->pilot = $this->fetchUser($this->fk_pilot);
            }
            $this->db->free($resql);

            if ($numrows) {
                return 1;
            } else {
                return 0;
            }
        } else {
            $this->errors[] = 'Error ' . $this->db->lasterror();
            dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

            return -1;
        }
    }

    /**
     * Load object in memory from the database
     *
     * @param string $sortorder  Sort Order
     * @param string $sortfield  Sort field
     * @param int    $limit      offset limit
     * @param int    $offset     offset limit
     * @param array  $filter     filter array
     * @param string $filtermode filter mode (AND or OR)
     *
     * @return int <0 if KO, >0 if OK
     */
    public function fetchAll(
        $sortorder = '',
        $sortfield = '',
        $limit = 0,
        $offset = 0,
        array $filter = array(),
        $filtermode = 'AND'
    ) {
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
        $sql .= " t.date_update";


        $sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';

        // Manage filter
        $sqlwhere = array();
        if (count($filter) > 0) {
            foreach ($filter as $key => $value) {
                $sqlwhere [] = $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
            }
        }
        if (count($sqlwhere) > 0) {
            $sql .= ' WHERE ' . implode(' ' . $filtermode . ' ', $sqlwhere);
        }

        if (!empty($sortfield)) {
            $sql .= $this->db->order($sortfield, $sortorder);
        }
        if (!empty($limit)) {
            $sql .= ' ' . $this->db->plimit($limit + 1, $offset);
        }
        $this->lines = array();

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);

            while ($obj = $this->db->fetch_object($resql)) {
                $line = new BbcvolsLine();

                $line->id = $obj->idBBC_vols;

                $line->idBBC_vols = $obj->idBBC_vols;
                $line->date = $this->db->jdate($obj->date);
                $line->lieuD = $obj->lieuD;
                $line->lieuA = $obj->lieuA;
                $line->heureD = $obj->heureD;
                $line->heureA = $obj->heureA;
                $line->BBC_ballons_idBBC_ballons = $obj->BBC_ballons_idBBC_ballons;
                $line->nbrPax = $obj->nbrPax;
                $line->remarque = $obj->remarque;
                $line->incidents = $obj->incidents;
                $line->fk_type = $obj->fk_type;
                $line->fk_pilot = $obj->fk_pilot;
                $line->fk_organisateur = $obj->fk_organisateur;
                $line->is_facture = $obj->is_facture;
                $line->kilometers = $obj->kilometers;
                $line->cost = $obj->cost;
                $line->fk_receiver = $obj->fk_receiver;
                $line->justif_kilometers = $obj->justif_kilometers;
                $line->date_creation = $obj->date_creation;
                $line->date_update = $obj->date_update;


                $this->lines[$line->id] = $line;
            }
            $this->db->free($resql);

            return $num;
        } else {
            $this->errors[] = 'Error ' . $this->db->lasterror();
            dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

            return -1;
        }
    }

    /**
     * Update object into database
     *
     * @param  User $user      User that modifies
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     *
     * @return int <0 if KO, >0 if OK
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
        $sql .= ' fk_type = ' . (isset($this->fk_type) ? $this->fk_type : "null") . ',';
        $sql .= ' fk_pilot = ' . (isset($this->fk_pilot) ? $this->fk_pilot : "null") . ',';
        $sql .= ' fk_organisateur = ' . (isset($this->fk_organisateur) ? $this->fk_organisateur : "null") . ',';
        $sql .= ' is_facture = ' . (isset($this->is_facture) ? $this->is_facture : "0") . ',';
        $sql .= ' kilometers = ' . (!empty($this->kilometers) ? $this->kilometers : "0") . ',';
        $sql .= ' cost = ' . (isset($this->cost) ? "'" . $this->db->escape($this->cost) . "'" : "''") . ',';
        $sql .= ' fk_receiver = ' . (isset($this->fk_receiver) ? $this->fk_receiver : "null") . ',';
        $sql .= ' justif_kilometers = ' . (isset($this->justif_kilometers) ? "'" . $this->db->escape($this->justif_kilometers) . "'," : "'',");
        $sql .= ' date_update = ' . "'" . date('Y-m-d H:i:s') . "'";

        $sql .= ' WHERE idBBC_vols=' . $this->idBBC_vols;

        $this->db->begin();

        $resql = $this->db->query($sql);
        if (!$resql) {
            $error++;
            $this->errors[] = 'Error ' . $this->db->lasterror();
            dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
        }

        if (!$error && !$notrigger) {
            // Uncomment this and change MYOBJECT to your own tag if you
            // want this action calls a trigger.

            //// Call triggers
            //$result=$this->call_trigger('MYOBJECT_MODIFY',$user);
            //if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
            //// End call triggers
        }

        // Commit or rollback
        if ($error) {
            $this->db->rollback();

            return -1 * $error;
        } else {
            $this->db->commit();

            return 1;
        }
    }

    /**
     * Delete object in database
     *
     * @param User $user      User that deletes
     * @param bool $notrigger false=launch triggers after, true=disable triggers
     *
     * @return int <0 if KO, >0 if OK
     */
    public function delete(User $user, $notrigger = false)
    {
        dol_syslog(__METHOD__, LOG_DEBUG);

        $error = 0;

        $this->db->begin();

        if (!$error) {
            if (!$notrigger) {
                // Uncomment this and change MYOBJECT to your own tag if you
                // want this action calls a trigger.

                //// Call triggers
                //$result=$this->call_trigger('MYOBJECT_DELETE',$user);
                //if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
                //// End call triggers
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
        } else {
            $this->db->commit();

            return 1;
        }
    }

    /**
     * Load an object from its id and create a new one in database
     *
     * @param int $fromid Id of object to clone
     *
     * @return int New id of clone
     */
    public function createFromClone($fromid)
    {
        dol_syslog(__METHOD__, LOG_DEBUG);

        global $user;
        $error = 0;
        $object = new Bbcvols($this->db);

        $this->db->begin();

        // Load source object
        $object->fetch($fromid);
        // Reset object
        $object->id = 0;

        // Clear fields
        // ...

        // Create clone
        $result = $object->create($user);

        // Other options
        if ($result < 0) {
            $error++;
            $this->errors = $object->errors;
            dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
        }

        // End
        if (!$error) {
            $this->db->commit();

            return $object->id;
        } else {
            $this->db->rollback();

            return -1;
        }
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
    function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $maxlen = 24, $morecss = '')
    {
        global $langs, $conf, $db;
        global $dolibarr_main_authentication, $dolibarr_main_demo;
        global $menumanager;

        $result = '';
        $companylink = '';

        $label = '<u>' . $langs->trans("MyModule") . '</u>';
        $label .= '<div width="100%">';
        $label .= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->idBBC_vols . '<br>';
        $label .= '<b>' . $langs->trans('Date') . ':</b> ' . dol_print_date($this->date, '%d-%m-%Y');
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
    function getLibStatut($mode = 0)
    {
        return $this->LibStatut($this->is_facture, $mode);
    }

    /**
     *  Renvoi le libelle d'un status donne
     *
     * @param    int $status Id status
     * @param  int   $mode   0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
     *
     * @return string                    Label of status
     */
    private function LibStatut($status, $mode = 0)
    {
        global $langs;

        if ($mode == 0) {
            $prefix = '';
            if ($status == 1) {
                return $langs->trans('Enabled');
            }
            if ($status == 0) {
                return $langs->trans('Disabled');
            }
        }
        if ($mode == 1) {
            if ($status == 1) {
                return $langs->trans('Enabled');
            }
            if ($status == 0) {
                return $langs->trans('Disabled');
            }
        }
        if ($mode == 2) {
            if ($status == 1) {
                return img_picto($langs->trans('Enabled'), 'statut4') . ' ' . $langs->trans('Enabled');
            }
            if ($status == 0) {
                return img_picto($langs->trans('Disabled'), 'statut5') . ' ' . $langs->trans('Disabled');
            }
        }
        if ($mode == 3) {
            if ($status == 1) {
                return img_picto($langs->trans('Enabled'), 'statut4');
            }
            if ($status == 0) {
                return img_picto($langs->trans('Disabled'), 'statut5');
            }
        }
        if ($mode == 4) {
            if ($status == 1) {
                return img_picto($langs->trans('Enabled'), 'statut4') . ' ' . $langs->trans('Enabled');
            }
            if ($status == 0) {
                return img_picto($langs->trans('Disabled'), 'statut5') . ' ' . $langs->trans('Disabled');
            }
        }
        if ($mode == 5) {
            if ($status == 1) {
                return $langs->trans('Enabled') . ' ' . img_picto($langs->trans('Enabled'), 'statut4');
            }
            if ($status == 0) {
                return $langs->trans('Disabled') . ' ' . img_picto($langs->trans('Disabled'), 'statut5');
            }
        }

        return "";
    }


    /**
     * Initialise object with example values
     * Id must be 0 if object instance is a specimen
     *
     * @return void
     */
    public function initAsSpecimen()
    {
        $this->id = 0;

        $this->idBBC_vols = '';
        $this->date = '';
        $this->lieuD = '';
        $this->lieuA = '';
        $this->heureD = '';
        $this->heureA = '';
        $this->BBC_ballons_idBBC_ballons = '';
        $this->nbrPax = '';
        $this->remarque = '';
        $this->incidents = '';
        $this->fk_type = '';
        $this->fk_pilot = '';
        $this->fk_organisateur = '';
        $this->is_facture = '';
        $this->kilometers = '';
        $this->cost = '';
        $this->fk_receiver = '';
        $this->justif_kilometers = '';


    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->idBBC_vols . " " . $this->lieuD . " " . $this->lieuA;
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
    public function hasFacture(){
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
        if(!$this->balloon){
            $this->balloon = $this->fetchBalloon();
        }

        return $this->balloon;
    }

    /**
     * @return User
     */
    public function getPilot()
    {
        if(!$this->pilot){
            $this->pilot = $this->fetchUser($this->fk_pilot);
        }

        return $this->pilot;
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


}

/**
 * Class BbcvolsLine
 */
class BbcvolsLine
{
    /**
     * @var int ID
     */
    public $id;
    /**
     * @var mixed Sample line property 1
     */

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
     * @var mixed Sample line property 2
     */

}
