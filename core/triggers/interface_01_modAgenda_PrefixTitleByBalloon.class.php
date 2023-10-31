<?php

class InterfacePrefixTitleByBalloon extends DolibarrTriggers
{

	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		if('ACTION_CREATE' !== $action){
			return 0 ;
		}

		if(!($object instanceof ActionComm)){
			return 0 ;
		}

		if(!isset($object->array_options['options_bal']) || empty($object->array_options['options_bal'])){
			return 0 ; // No balloon
		}

		$balloon = new Bbc_ballons($this->db);
		$balloon->fetch($object->array_options['options_bal']);
		if(empty($balloon->id)){
			return 0 ; // No balloon found
		}

		$object->label = '['.$balloon->immat.'] - '.$object->label;
		$object->update($user, true);

		return 1;
	}
}
