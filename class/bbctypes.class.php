<?php

/**
 * \file    flightlog/bbctypes.class.php
 * \ingroup flightlog
 * \brief   This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *          Put some comments here
 */

require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class Bbctypes
 *
 * Put here description of your class
 *
 * @see CommonObject
 */
class Bbctypes extends CommonObject
{
    /**
     * @var string Id to identify managed objects
     */
    public $element = 'bbctypes';
    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'bbc_types';

    /**
     * @var BbctypesLine[] Lines
     */
    public $lines = array();

    /**
     * @var int
     */
    public $idType;

    /**
     * @var int
     */
    public $numero;

    /**
     * @var string
     */
    public $nom;

    /**
     * @var boolean
     */
    public $active;

    /**
     * @var int
     */
    public $fkService;

    /**
     * @var Product
     */
    public $service;

    /**
     * Constructor
     *
     * @param DoliDb $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        $this->db = $db;
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

        if (isset($this->idType)) {
            $this->idType = trim($this->idType);
        }
        if (isset($this->numero)) {
            $this->numero = trim($this->numero);
        }
        if (isset($this->nom)) {
            $this->nom = trim($this->nom);
        }
        if (isset($this->active)) {
            $this->active = trim($this->active);
        }
        if (isset($this->fkService)) {
            $this->fkService = trim($this->fkService);
        }

        $sql = 'INSERT INTO ' . MAIN_DB_PREFIX . $this->table_element . '(';

        $sql .= 'numero,';
        $sql .= 'nom,';
        $sql .= 'fkService,';
        $sql .= 'active';


        $sql .= ') VALUES (';

        $sql .= ' ' . (!isset($this->numero) ? 'NULL' : $this->numero) . ',';
        $sql .= ' ' . (!isset($this->nom) ? 'NULL' : "'" . $this->db->escape($this->nom) . "'") . ',';
        $sql .= ' ' . (!isset($this->fkService) ? 'NULL' : "'" . $this->db->escape($this->fkService) . "'") . ',';
        $sql .= ' ' . (!isset($this->active) ? 'NULL' : $this->active);


        $sql .= ')';

        $this->db->begin();

        $resql = $this->db->query($sql);
        if (!$resql) {
            $error++;
            $this->errors[] = 'Error ' . $this->db->lasterror();
            dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
        }

        if (!$notrigger) {
            $this->call_trigger('BBC_FLIGHT_TYPE_CREATE', $user);
        }

        if (!$error) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);
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
        $sql .= ' t.idType,';

        $sql .= " t.numero,";
        $sql .= " t.nom,";
        $sql .= " t.fkService,";
        $sql .= " t.active";


        $sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
        if (null !== $ref) {
            $sql .= ' WHERE t.ref = ' . '\'' . $ref . '\'';
        } else {
            $sql .= ' WHERE t.idType = ' . $id;
        }

        $resql = $this->db->query($sql);
        if ($resql) {
            $numrows = $this->db->num_rows($resql);
            if ($numrows) {
                $obj = $this->db->fetch_object($resql);

                $this->id = $obj->idType;

                $this->idType = $obj->idType;
                $this->numero = $obj->numero;
                $this->nom = $obj->nom;
                $this->fkService = $obj->fkService;
                $this->active = $obj->active;

                if ($this->fkService) {
                    $this->service = new Product($this->db);
                    $this->service->fetch($this->fkService);
                }
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
        $sql .= " t.idType,";
        $sql .= " t.numero,";
        $sql .= " t.nom,";
        $sql .= " t.fkService,";
        $sql .= " t.active";


        $sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';

        // Manage filter
        $sqlwhere = array();

        foreach ($filter as $key => $value) {
            if (is_string($value)) {
                $sqlwhere [] = $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
                continue;
            }

            if (is_int($value)) {
                $sqlwhere [] = $key . ' = ' . (int) $value;
                continue;
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
                $line = new BbctypesLine();

                $line->id = $obj->idType;
                $line->idType = $obj->idType;
                $line->numero = $obj->numero;
                $line->nom = $obj->nom;
                $line->fkService = $obj->fkService;
                $line->active = $obj->active;

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

        if (isset($this->idType)) {
            $this->idType = trim($this->idType);
        }
        if (isset($this->numero)) {
            $this->numero = trim($this->numero);
        }
        if (isset($this->nom)) {
            $this->nom = trim($this->nom);
        }
        if (isset($this->fkService)) {
            $this->fkService = trim($this->fkService);
        }
        if (isset($this->active)) {
            $this->active = trim($this->active);
        }


        // Check parameters
        // Put here code to add a control on parameters values

        // Update request
        $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element . ' SET';

        $sql .= ' numero = ' . (isset($this->numero) ? $this->numero : "null") . ',';
        $sql .= ' nom = ' . (isset($this->nom) ? "'" . $this->db->escape($this->nom) . "'" : "null") . ',';
        $sql .= ' fkService = ' . (isset($this->fkService) ? "'" . $this->db->escape($this->fkService) . "'" : "null") . ',';
        $sql .= ' active = ' . (isset($this->active) ? $this->active : "null");


        $sql .= ' WHERE idType=' . $this->id;

        $this->db->begin();

        $resql = $this->db->query($sql);
        if (!$resql) {
            $error++;
            $this->errors[] = 'Error ' . $this->db->lasterror();
            dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
        }

        if (!$error && !$notrigger) {
            $this->call_trigger('BBC_FLIGHT_TYPE_MODIFY', $user);
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
                $this->call_trigger('BBC_FLIGHT_TYPE_DELETE', $user);
            }
        }

        if (!$error) {
            $sql = 'DELETE FROM ' . MAIN_DB_PREFIX . $this->table_element;
            $sql .= ' WHERE idType=' . $this->id;

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
        $object = new Bbctypes($this->db);

        $this->db->begin();

        $object->fetch($fromid);
        $object->id = 0;

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
    public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $maxlen = 24, $morecss = '')
    {
        global $langs;


        $result = '';

        $label = '<u>' . $langs->trans("MyModule") . '</u>';
        $label .= '<div width="100%">';
        $label .= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;

        $link = '<a href="' . DOL_URL_ROOT . '/flightlog/card.php?id=' . $this->id . '"';
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
        $result .= $link . $this->ref . $linkend;
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
        return $this->LibStatut($this->active, $mode);
    }

    /**
     *  Renvoi le libelle d'un status donne
     *
     * @param    int $status Id status
     * @param  int   $mode   0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
     *
     * @return string                    Label of status
     */
    public function LibStatut($status, $mode = 0)
    {
        global $langs;

        if ($mode == 0) {
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

        $this->idType = '';
        $this->numero = '';
        $this->nom = '';
        $this->fkService = null;
        $this->active = '';
    }

    /**
     * @return boolean
     */
    public function isPaxRequired()
    {
        switch ((int) $this->idType) {
            case 1:
            case 2:
                return true;
            default:
                return false;
        }
    }

    /**
     * Return true if this type of flight requires money.
     *
     * @return boolean
     */
    public function isBillingRequired()
    {
        return (int) $this->idType === 2;
    }

    /**
     * @return Product
     */
    public function getService()
    {
        if (!$this->service) {
            if (empty($this->fkService)) {
                throw new \InvalidArgumentException('FK service is missing');
            }

            $this->service = new Product($this->db);
            $this->service->fetch($this->fkService);
        }

        return $this->service;
    }

    /**
     * Is an instruction type
     */
    public function isInstruction()
    {
        $type = (int) $this->idType;
        return $type === 6;
    }

    /**
     * @return array|BbctypesLine[]
     */
    public function getLines()
    {
        return $this->lines;
    }
}

/**
 * Class BbctypesLine
 */
class BbctypesLine
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $idType;

    /**
     * @var int
     */
    public $numero;

    /**
     * @var string
     */
    public $nom;

    /**
     * @var int
     */
    public $fkService;

    /**
     * @var boolean
     */
    public $active;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getIdType()
    {
        return $this->idType;
    }

    /**
     * @return int
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @return int
     */
    public function getFkService()
    {
        return $this->fkService;
    }

    /**
     * @return boolean
     */
    public function getActive()
    {
        return (boolean)$this->active;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return "T" . $this->numero . '-' . $this->nom;
    }

}
