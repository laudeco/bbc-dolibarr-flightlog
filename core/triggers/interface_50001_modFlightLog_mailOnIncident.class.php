<?php
/* Copyright (C) 2017 laurent De Coninck <lau.deconinck@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';
dol_include_once('/flightlog/class/bbcvols.class.php');
dol_include_once('/core/class/CMailFile.class.php');


/**
 *  Class of triggers for hello module
 */
class InterfaceMailOnIncident extends DolibarrTriggers
{
	/**
	 * @var DoliDB Database handler
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "Belgian balloon club";
		$this->description = "Trigger that send an e-mail on flight incident.";
		$this->version = '1.0';
		$this->picto = 'flightlog@flightlog';
	}

	/**
	 * Trigger name
	 *
	 * @return string Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Trigger description
	 *
	 * @return string Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}


	/**
	 * @param string 		$action 	Event action code
	 * @param Bbcvols 	    $object 	Object
	 * @param User 			$user 		Object user
	 * @param Translate 	$langs 		Object langs
	 * @param Conf 			$conf 		Object conf
	 * @return int              		<0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
        if (empty($conf->flightlog->enabled) || empty($conf->workflow->enabled)){
            return 0;
        }

        if(empty($conf->global->WORKFLOW_BBC_FLIGHTLOG_SEND_MAIL_ON_INCIDENT)){
            return 0;
        }

        if($action !== 'BBC_FLIGHT_LOG_ADD_FLIGHT'){
            return 0;
        }

        if(!$object->hasIncidents() && !$object->hasComment()){
            return 0;
        }

        if(!empty($conf->global->MAIN_DISABLE_ALL_MAILS)){
            return 0;
        }

        $message = "<p>Bonjour,</p><br/>";
        $message .= sprintf("<p>Vous recevez cet e-mail car un vol a été encodé avec un incident/ une remarque sur le ballon : %s dont vous êtes titulaire ou co-titulaire.</p>", $object->getBalloon()->immat);
        $message .= sprintf("<p><b>Vol id :</b> %d</p>", $object->getId());
        $message .= sprintf("<p><b>Réalisé par :</b> %s </p>", $object->getPilot()->getFullName($langs));
        if($object->hasComment()){
            $message .= sprintf("<p><b>Commentaire :</b> %s </p>", $object->getComment());
        }

        if($object->hasIncidents()){
            $message .= sprintf("<p><b>Incident :</b> %s </p>", $object->getIncident());
        }

        $message .= "<p>Ce mail est un mail informatif automatique, il ne faut pas$y répondre.</p>";

        $message .= "<p align=\"right\">Le Belgian balloon club.</p>";

        $to = [];

        $responsable = new User($user->db);
        $res = $responsable->fetch($object->getBalloon()->fk_responsable);
        if($res > 0){
            $to[] = $responsable->email;
        }

        $backup = new User($user->db);
        $res = $backup->fetch($object->getBalloon()->fk_co_responsable);
        if($res > 0){
            $to[] = $backup->email;
        }

        if(isset($conf->global->BBC_DAMAGE_EMAILS) && !empty($conf->global->BBC_DAMAGE_EMAILS)){
            $tmpEmails = explode(';', $conf->global->BBC_DAMAGE_EMAILS);
            foreach($tmpEmails as $currentEmail){
                if(null === $currentEmail || empty($currentEmail)){
                    continue;
                }

                $to[] = $currentEmail;
            }
        }

        if(empty($to)){
            return 0;
        }

        $mailfile = new CMailFile(
            sprintf("Un vol a été encodé avec un incident / un commentaire sur le ballon %s", $object->getBalloon()->immat),
            join(',', $to),
            $conf->global->MAIN_MAIL_EMAIL_FROM,
            $message,
            array(),
            array(),
            array(),
            '',
            '',
            0,
            -1,
            $conf->global->MAIN_MAIL_ERRORS_TO
        );


        if (! $mailfile->sendfile())
        {
            dol_syslog("Error while sending mail in flight log module : incident", LOG_ERR);
        }

		return 0;
	}
}
