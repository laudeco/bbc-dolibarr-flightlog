<?php

global $db, $langs, $user, $conf;

if(!defined('ROOT')){
    define('ROOT',dol_buildpath('/flightlog/'));
    define('HTTP',dol_buildpath('/flightlog/',1));
}
if(!defined('COREROOT')) {
    define('COREROOT',ROOT);
    define('COREHTTP',HTTP);
}

define('CORECLASS',COREROOT.'/class/');
define('COREFCT',COREROOT.'/lib/');

//load classes
require_once(CORECLASS.'actions_flightlog.class.phpp');
require_once(CORECLASS.'bbcvols.class.php');
require_once(CORECLASS.'bbctypes.class.php');
require_once(CORECLASS.'flightLog.lib.php');
require_once(CORECLASS.'GraphicalData.php');
require_once(CORECLASS.'GraphicalType.php');
require_once(CORECLASS.'GraphicalValue.php');
require_once(CORECLASS.'GraphicalValueType.php');
require_once(CORECLASS.'YearGraphicalData.php');

require_once(CORECLASS.'card/Tab.php');
require_once(CORECLASS.'card/TabCollection.php');

//load lib
require_once(COREFCT.'card.lib.php');
require_once(COREFCT.'flightLog.lib.php');
require_once(COREFCT.'PilotService.php');

// Load translation files required by the page
$langs->load("mymodule@flightlog");
