<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2016 Regis Houssin        <regis.houssin@capnetworks.com>
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
 *    \defgroup   mymodule     Module MyModule
 *  \brief      Example of a module descriptor.
 *                Such a file must be copied into htdocs/mymodule/core/modules directory.
 *  \file       htdocs/mymodule/core/modules/modMyModule.class.php
 *  \ingroup    mymodule
 *  \brief      Description and activation file for module MyModule
 */
include_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module MyModule
 */
class modFlightLog extends DolibarrModules
{
    const MENU_TYPE_LEFT = 'left';
    const MENU_TYPE_TOP = 'top';

    /**
     * @var array Indexed list of export IDs
     *
     */
    public $export_code = array();

    /**
     * @var array Indexed list of export names
     *
     */
    public $export_label = array();

    /**
     * @var array Indexed list of export enabling conditions
     *
     */
    public $export_enabled = array();

    /**
     * @var array Indexed list of export required permissions
     *
     */
    public $export_permission = array();

    /**
     * @var array Indexed list of export fields
     *
     */
    public $export_fields_array = array();

    /**
     * @var array Indexed list of export entities
     *
     */
    public $export_entities_array = array();

    /**
     * @var array Indexed list of export SQL queries start
     *
     */
    public $export_sql_start = array();

    /**
     * @var array Indexed list of export SQL queries end
     *
     */
    public $export_sql_end = array();

    public $export_TypeFields_array = [];

    /**
     * @var array
     */
    public $menus;

    /**
     * Constructor. Define names, constants, directories, boxes, permissions
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $langs, $conf;

        $this->db = $db;

        // Id for module (must be unique).
        $this->numero = 500000;
        // Key text used to identify module (for permissions, menus, etc...)
        $this->rights_class = 'flightLog';

        // Family can be 'crm','financial','hr','projects','products','ecm','technic','interface','other'
        // It is used to group modules by family in module setup page
        $this->family = "Belgian Balloon Club";
        // Module position in the family
        $this->module_position = 500;
        // Gives the possibility to the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
        //$this->familyinfo = array('myownfamily' => array('position' => '001', 'label' => $langs->trans("MyOwnFamily")));

        // Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        // Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
        $this->description = "Pilots flight log";
        $this->descriptionlong = "Manage flights and flights type for the Belgian Balloon Club";
        $this->editor_name = 'De Coninck Laurent';
        $this->editor_url = 'http://www.dolibarr.org';

        // Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
        $this->version = '1.1';
        // Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
        $this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
        // Name of image file used for this module.
        // If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
        // If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
        $this->picto = 'flight@flightLog';

        $this->module_parts = array();

        // Data directories to create when module is enabled.
        $this->dirs = array();

        // Config pages. Put here list of php page, stored into mymodule/admin directory, to use to setup module.
        $this->config_page_url = array();

        // Dependencies
        $this->hidden = false;
        $this->depends = array('modFlightBalloon');
        $this->requiredby = array();
        $this->conflictwith = array();
        $this->phpmin = array(5, 5);
        $this->need_dolibarr_version = array(4, 0);
        $this->langfiles = array("mymodule@flightLog");

        // Constants
        $this->initConstants();

        // Array to add new pages in new tabs
        $this->tabs = [
        ];

        if (!isset($conf->flightLog) || !isset($conf->flightLog->enabled)) {
            $conf->flightLog = new stdClass();
            $conf->flightLog->enabled = 0;
        }

        $this->boxes = [];

        $this->initDictionnaries();
        $this->initCronJobs();
        $this->initMenu();
        $this->initHooks();
        $this->initPermissions();

        // Exports
        $r = 0;

        $this->export_code[$r] = $this->rights_class . '_' . $r;
        $this->export_label[$r] = 'Flights export';
        $this->export_enabled[$r] = '1';
        $this->export_permission[$r] = array(array("flightLog", "vol", "detail"));
        $this->export_fields_array[$r] = array(
            "flight.idBBC_vols"                => "Identifiant",
            "flight.date"                      => "Date",
            "flight.lieuD"                     => "Lieu décollage ",
            "flight.lieuA"                     => "Lieu atterissage",
            "flight.heureD"                    => "Heure décollage",
            "flight.heureA"                    => "Heure atterissage",
            "flight.BBC_ballons_idBBC_ballons" => "Identifiant ballon",
            "flight.nbrPax"                    => "# pax",
            "flight.remarque"                  => "Remarque",
            "flight.incidents"                 => "Incidents",
            "flight.fk_type"                   => "Identifiant type",
            "flight.fk_pilot"                  => "Identifiant pilote",
            "flight.fk_organisateur"           => "Identifiant organisateur",
            "flight.is_facture"                => "Facture Oui/Non",
            "flight.kilometers"                => "# Km",
            "flight.cost"                      => "Cout",
            "flight.fk_receiver"               => "Identifiant receveur d'argent",
            "flight.justif_kilometers"         => "Justificatif kilomètres",
            "balloon.immat"                    => "Immat.",
            "pilot.login"                      => "Pilote",
            "flightType.nom"                   => "Type de vol",
            "organisator.login"                => "Organisateur",
            "receiver.login"                   => "Percepteur",
        );

        $this->export_TypeFields_array[$r] = [
            "flight.date"                      => "Date",
            "flight.lieuD"                     => "Text",
            "flight.lieuA"                     => "Text",
            "flight.heureD"                    => "Text",
            "flight.heureA"                    => "Text",
            "flight.BBC_ballons_idBBC_ballons" => implode(":", ["List", "bbc_ballons", "immat", "rowid"]),
            "flight.nbrPax"                    => "Numeric",
            "flight.remarque"                  => "Text",
            "flight.incidents"                 => "Text",
            "flight.fk_type"                   => implode(":", ["List", "bbc_types", "nom", "idType"]),
            "flight.fk_pilot"                  => implode(":", ["List", "user", "login", "rowid"]),
            "flight.fk_organisateur"           => implode(":", ["List", "user", "login", "rowid"]),
            "flight.is_facture"                => "Boolean",
            "flight.kilometers"                => "Numeric",
            "flight.cost"                      => "Numeric",
            "flight.fk_receiver"               => implode(":", ["List", "user", "login", "rowid"]),
            "flight.justif_kilometers"         => "Text",
        ];

        $this->export_entities_array[$r] = array(
            "flight.idBBC_vols"                => "Flight",
            "flight.date"                      => "Flight",
            "flight.lieuD"                     => "Flight",
            "flight.lieuA"                     => "Flight",
            "flight.heureD"                    => "Flight",
            "flight.heureA"                    => "Flight",
            "flight.BBC_ballons_idBBC_ballons" => "Flight",
            "flight.nbrPax"                    => "Flight",
            "flight.remarque"                  => "Flight",
            "flight.incidents"                 => "Flight",
            "flight.fk_type"                   => "Flight",
            "flight.fk_pilot"                  => "Flight",
            "flight.fk_organisateur"           => "Flight",
            "flight.is_facture"                => "Flight",
            "flight.kilometers"                => "Flight",
            "flight.cost"                      => "Flight",
            "flight.fk_receiver"               => "Flight",
            "flight.justif_kilometers"         => "Flight",
            "balloon.immat"                    => "Balloon",
            "pilot.login"                      => "Pilot",
            "flightType.nom"                   => "FlightType",
            "organisator.login"                => "Organisator",
            "receiver.login"                   => "Percepteur",
        );
        $this->export_sql_start[$r] = 'SELECT DISTINCT ';
        $this->export_sql_end[$r] = ' FROM ' . MAIN_DB_PREFIX . 'bbc_vols as flight';
        $this->export_sql_end[$r] .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'bbc_ballons as balloon on (flight.BBC_ballons_idBBC_ballons = balloon.rowid)';
        $this->export_sql_end[$r] .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'bbc_types as flightType on (flight.fk_type = flightType.idType)';
        $this->export_sql_end[$r] .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'user as pilot on (flight.fk_pilot = pilot.rowid)';
        $this->export_sql_end[$r] .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'user as organisator on (flight.fk_organisateur = organisator.rowid)';
        $this->export_sql_end[$r] .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'user as receiver on (flight.fk_receiver = receiver.rowid)';
        $this->export_sql_end[$r] .= ' WHERE 1 = 1';
        $r++;
    }

    /**
     *        Function called when module is enabled.
     *        The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     *        It also creates data directories
     *
     * @param      string $options Options when enabling module ('', 'noboxes')
     *
     * @return     int                1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        $sql = array();

        $this->_load_tables('/flightLog/sql/');

        return $this->_init($sql, $options);
    }

    /**
     * Function called when module is disabled.
     * Remove from database constants, boxes and permissions from Dolibarr database.
     * Data directories are not deleted
     *
     * @param      string $options Options when enabling module ('', 'noboxes')
     *
     * @return     int                1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        $sql = array();

        return $this->_remove($sql, $options);
    }

    /**
     * Init menu
     */
    private function initMenu()
    {
        $this->menus = array();
        $r = 0;

        $this->menu[$r] = array(
            'fk_menu'  => 'fk_mainmenu=flightLog',
            'type'     => self::MENU_TYPE_TOP,
            'titre'    => 'Carnet de vols',
            'mainmenu' => 'flightLog',
            'leftmenu' => 'readFlight',
            'url'      => '/flightLog/readFlights.php',
            'langs'    => 'mylangfile',
            'position' => 100,
            'enabled'  => '1',
            'perms'    => '$user->rights->flightLog->vol->access',
            'target'   => '',
            'user'     => 0
        );
        $r++;

        $this->menu[$r] = array(
            'fk_menu'  => 'fk_mainmenu=flightLog',
            'type'     => self::MENU_TYPE_LEFT,
            'titre'    => 'Ajouter un vol',
            'mainmenu' => 'flightLog',
            'leftmenu' => 'addFlight',
            'url'      => '/flightLog/addFlight.php',
            'langs'    => 'mylangfile',
            'position' => 101,
            'enabled'  => '1',
            'perms'    => '$user->rights->flightLog->vol->add',
            'target'   => '',
            'user'     => 2
        );
        $r++;
        $this->menu[$r] = array(
            'fk_menu'  => 'fk_mainmenu=flightLog',
            'type'     => self::MENU_TYPE_LEFT,
            'titre'    => 'Visualisation',
            'mainmenu' => 'flightLog',
            'leftmenu' => 'showFlight',
            'url'      => '/flightLog/readFlights.php',
            'langs'    => 'mylangfile',
            'position' => 102,
            'enabled'  => '1',
            'perms'    => '1',
            'target'   => '',
            'user'     => 2
        );
        $this->menu[$r] = array(
            'fk_menu'  => 'fk_mainmenu=flightLog',
            'type'     => self::MENU_TYPE_LEFT,
            'titre'    => 'Les vols',
            'mainmenu' => 'flightLog',
            'leftmenu' => 'flightLog',
            'url'      => '/flightLog/list.php',
            'langs'    => 'mylangfile',
            'position' => 105,
            'enabled'  => '1',
            'perms'    => '1',
            'target'   => '',
            'user'     => 2
        );
        $r++;
        $this->menu[$r] = array(
            'fk_menu'  => 'fk_mainmenu=flightLog',
            'type'     => self::MENU_TYPE_LEFT,
            'titre'    => 'Gestion',
            'mainmenu' => 'flightLog',
            'leftmenu' => 'management',
            'url'      => '',
            'langs'    => 'mylangfile',
            'position' => 106,
            'enabled'  => '1',
            'perms'    => '$user->rights->flightLog->vol->status||$user->rights->flightLog->vol->detail',
            'target'   => '',
            'user'     => 2
        );
        $r++;
        $this->menu[$r] = array(
            'fk_menu'  => 'fk_mainmenu=flightLog,fk_leftmenu=management',
            'type'     => self::MENU_TYPE_LEFT,
            'titre'    => 'Payement',
            'mainmenu' => 'flightLog',
            'leftmenu' => 'flightBilling',
            'url'      => '/flightLog/listFact.php?view=1',
            'langs'    => 'mylangfile',
            'position' => 107,
            'enabled'  => '1',
            'perms'    => '$user->rights->flightLog->vol->financial',
            'target'   => '',
            'user'     => 2
        );
        $r++;
        $this->menu[$r] = array(
            'fk_menu'  => 'fk_mainmenu=flightLog,fk_leftmenu=management',
            'type'     => self::MENU_TYPE_LEFT,
            'titre'    => 'Aviabel',
            'mainmenu' => 'flightLog',
            'leftmenu' => 'flightAviabel',
            'url'      => '/flightLog/listFact.php?view=2',
            'langs'    => 'mylangfile',
            'position' => 108,
            'enabled'  => '1',
            'perms'    => '$user->rights->flightLog->vol->detail',
            'target'   => '',
            'user'     => 2
        );
    }

    /**
     * Init permissions
     */
    private function initPermissions()
    {
        $this->rights = array();        // Permission array used by this module
        $r = 0;

        $this->rights[$r][0] = 9993;
        $this->rights[$r][1] = 'Permet d\'acceder au module des vols.';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'vol';
        $this->rights[$r][5] = 'access';
        $r++;

        $this->rights[$r][0] = 9998;
        $this->rights[$r][1] = 'Enregistrer un nouveau vol.';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'vol';
        $this->rights[$r][5] = 'add';
        $r++;

        $this->rights[$r][0] = 9997;
        $this->rights[$r][1] = 'Permet de facturer un vol.';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'vol';
        $this->rights[$r][5] = 'status';
        $r++;

        $this->rights[$r][0] = 9996;
        $this->rights[$r][1] = 'Permet de supprimer un vol.';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'vol';
        $this->rights[$r][5] = 'delete';
        $r++;

        $this->rights[$r][0] = 9995;
        $this->rights[$r][1] = 'Permet de modifier tous les vols.';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'vol';
        $this->rights[$r][5] = 'edit';
        $r++;

        $this->rights[$r][0] = 9994;
        $this->rights[$r][1] = 'affiche les details de tous les ballons et de tous les pilotes.';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'vol';
        $this->rights[$r][5] = 'detail';
        $r++;

        $this->rights[$r][0] = 9999;
        $this->rights[$r][1] = 'Gérer les aspects financier des vols';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'vol';
        $this->rights[$r][5] = 'financial';
        $r++;
    }

    private function initCronJobs()
    {
        $this->cronjobs = array();            // List of cron jobs entries to add
        // Example: $this->cronjobs=array(0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'test'=>true),
        //                                1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'test'=>true)
        // );
    }

    private function initDictionnaries()
    {
        $this->initFlightTypeDictionnary();
    }

    private function initFlightTypeDictionnary()
    {
        $this->dictionaries = array(
            'langs'          => 'mylangfile@mymodule',
            'tabname'        => array(MAIN_DB_PREFIX . "bbc_types"),
            'tablib'         => array("Types de vols"),
            'tabsql'         => array('SELECT f.idType, f.numero, f.nom, f.active FROM ' . MAIN_DB_PREFIX . 'bbc_types as f',),
            'tabsqlsort'     => array("numero ASC"),
            'tabfield'       => array("idType,numero,nom"),
            'tabfieldvalue'  => array("numero,nom"),
            'tabfieldinsert' => array("numero,nom"),
            'tabrowid'       => array("idType"),
            'tabcond'        => array('$conf->flightLog->enabled'),
        );
    }

    /**
     * Init hooks
     */
    private function initHooks()
    {
        if (!isset($this->module_parts["hooks"])) {
            $this->module_parts["hooks"] = [];
        }

        $this->module_parts["hooks"][] = "searchform";

    }

    private function initConstants()
    {
        $this->const = array(
            0 => [
                'BBC_FLIGHT_LOG_TAUX_REMB_KM',
                'chaine',
                '0.25',
                'Taux remboursement des kilomètres au BBC',
                true,
                'current',
                true
            ],
            1 => [
                'BBC_FLIGHT_LOG_UNIT_PRICE_MISSION',
                'chaine',
                '35',
                'Unit price special mission',
                true,
                'current',
                true
            ],
        );
    }

}

