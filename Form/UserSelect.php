<?php
/**
 *
 */

namespace flightlog\form;

use User;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class UserSelect extends Select
{

    /**
     * @var \DoliDB
     */
    private $db;

    /**
     * @inheritDoc
     */
    public function __construct($name, array $options = [], \DoliDB $db)
    {
        parent::__construct($name, $options);
        $this->db = $db;
        $this->buildOptions();
    }

    /**
     * Build the options of the select
     */
    private function buildOptions()
    {

        if ((boolean) $this->getOption('show_empty')) {
            $this->addValueOption(-1, ' ');
        }

        if ((boolean) $this->getOption('show_every')) {
            $this->addValueOption(-2, 'Everybody');
        }

        // Forge request to select users
        $sql = "SELECT DISTINCT u.rowid, u.lastname as lastname, u.firstname, u.statut, u.login, u.admin, u.entity";
        $sql .= " FROM " . MAIN_DB_PREFIX . "user as u";
        $sql .= " WHERE u.entity IN (0,1)";
        if (!empty($this->getOption('USER_HIDE_INACTIVE_IN_COMBOBOX'))) {
            $sql .= " AND u.statut <> 0";
        }

        if (empty($this->getOption('MAIN_FIRSTNAME_NAME_POSITION'))) {
            $sql .= " ORDER BY u.firstname ASC";
        } else {
            $sql .= " ORDER BY u.lastname ASC";
        }

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num) {
                $userstatic = new User($this->db);

                while ($i < $num) {
                    $obj = $this->db->fetch_object($resql);

                    $userstatic->id = $obj->rowid;
                    $userstatic->lastname = $obj->lastname;
                    $userstatic->firstname = $obj->firstname;

                    $fullNameMode = 0; //Lastname + firstname
                    if (empty($this->getOption('MAIN_FIRSTNAME_NAME_POSITION'))) {
                        $fullNameMode = 1; //firstname + lastname
                    }

                    $this->addValueOption($obj->rowid, $userstatic->getFullName(null, $fullNameMode));
                    $i++;
                }
            }


        }
    }
}