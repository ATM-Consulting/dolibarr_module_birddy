<?php
/* @author Simon Samtleben <web@lemmingzshadow.net>
 * Copyright (C) 2016 Pierre-Henry Favre <phf@atm-consulting.fr>
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

require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

class Birddy extends \WebSocket\Application\Application
{
    private $_clients = array();
	private $_filename = '';
	
	public $db;
	public $langs;
	public $showuserpicto = false;
	public $speak_with_entities = false;
	public $answer_entity_zero_is_allow = false;
	
	protected function __construct()
	{
		global $db,$conf,$langs;
		
		$langs->load('birddy@birddy');

		$this->db = &$db;
		$this->langs = &$langs;
		if (!empty($conf->global->BIRDDY_SHOW_USER_PICTO) && method_exists(Form, 'showphoto')) $this->showuserpicto = true;
		if (!empty($conf->global->BIRDDY_USER_CAN_SPEAK_WITH_OTHER_ENTITY)) $this->speak_with_entities = true;
		if (!empty($conf->global->BIRDDY_USER_CAN_SPEAK_WITH_ENTITY_ZERO)) $this->answer_entity_zero_is_allow = true;
	}
	
	public function onConnect($client)
    {
		$id = $client->getClientId();
        $this->_clients[$id] = $client;
		
		$client->send($this->_encodeData('returnConnectId', array('connectId'=>$id)));
    }

    public function onDisconnect($client)
    {
        $id = $client->getClientId();
		unset($this->_clients[$id]);
    }

    public function onData($data, $client)
    {
    	//var_dump($this->_clients);
        $decodedData = $this->_decodeData($data);
		if($decodedData === false)
		{
			// @todo: invalid request trigger error...
		}
		
		// Propriétaire du message
		$decodedData['fromClientId'] = $client->getClientId();
		
		$actionName = '_action' . ucfirst($decodedData['action']);
		if(method_exists($this, $actionName))
		{
			call_user_func(array($this, $actionName), $decodedData, $client);
		}
    }
	
	/*
	public function onBinaryData($data, $client)
	{		
		$filePath = substr(__FILE__, 0, strpos(__FILE__, 'server')) . 'tmp/';
		$putfileResult = false;
		if(!empty($this->_filename))
		{
			$putfileResult = file_put_contents($filePath.$this->_filename, $data);
		}		
		if($putfileResult !== false)
		{
			
			$msg = 'File received. Saved: ' . $this->_filename;
		}
		else
		{
			$msg = 'Error receiving file.';
		}
		$client->send($this->_encodeData('echo', $msg));
		$this->_filename = '';
	}
	*/
	
	protected function _encodeData($action, $data)
	{
		if(empty($action))
		{
			return false;
		}
		
		$payload = $data;
		$payload['action'] = $action;
		
		return json_encode($payload);
	}
	
	protected function _decodeData($data)
	{
		$decodedData = json_decode($data, true);
		if($decodedData === null)
		{
			return false;
		}
		
		if(empty($decodedData['action']))
		{
			return false;
		}
		
		return $decodedData;
	}
	
	private function _actionEcho($data, $client)
	{
		if (empty($data['msg']) || empty($data['fk_user_target']) || empty($data['fk_user_origin'])) return false;

		$encodedData = $this->_encodeData('echo', $data);
		foreach($this->_clients as $sendto)
		{
			if (!empty($data['fk_user_target']))
			{
				$can_speak = $this->_checkIfUserCanSpeak($client, $sendto, $data);
				
				if ($can_speak === true) $sendto->send($encodedData);
				elseif ($can_speak === false) continue;
				elseif (!empty($can_speak))
				{
					// $can_speak contain error code
					$this->_systemEcho($client, $data['fk_user_target'], $can_speak);
					break;
				}
			}
			else
			{
				// TODO ajouter une condition pour passer par là qui permet à un admin d'envoyer un message de rappel à tous le monde
				$sendto->send($encodedData);
			}
        }


	}

	private function _checkIfUserCanSpeak(&$client, &$sendto, &$data)
	{
		// Target user sould be the user who I want to speak
		if ($sendto->fk_user == $data['fk_user_target'])
		{
			// If user come from entity 0 he can speak with everybody
			if ($client->fk_entity != 0)
			{
				// Case 1: target user come from entity 0 then the conf to speak with entity 0 must be enable
				if ($sendto->fk_entity == 0 && !$this->answer_entity_zero_is_allow) return 'CANT_ANSWER_FROM_ENTITY_ZERO';
				// Case 2: target user come from different entity then the conf to speak with all entites must be enable
				if ($sendto->fk_entity != 0 && $client->fk_entity != $sendto->fk_entity && !$this->speak_with_entities) return 'CANT_ANSWER_FROM_DIFFRENT_ENTITY';	
			}
			
			return true;
		}
		elseif ($sendto->fk_user == $data['fk_user_origin']) return true;

		return false;
	}

	private function _systemEcho($client, $fk_user_target, $error_code)
	{
		$data = array('action' => 'systemEcho', 'fk_user_target' => $fk_user_target, 'fk_user_origin' => $fk_user_target);

		switch($error_code) {
			case 'CANT_ANSWER_FROM_ENTITY_ZERO':
				$data['msg'] = $this->langs->trans('birddy_CANT_ANSWER_FROM_ENTITY_ZERO');
				break;

			case 'CANT_ANSWER_FROM_DIFFRENT_ENTITY':
				$data['msg'] = $this->langs->trans('birddy_CANT_ANSWER_FROM_DIFFRENT_ENTITY');
				break;
			
			default:
				$data['msg'] = $this->langs->trans('birddy_UNEXPECTED_ERROR');
				break;
		}
		
		$client->send($this->_encodeData('systemEcho', $data));
	}
	
	private function _actionSetUserToSocketClient($data, $client)
	{
		$u = new User($this->db);
		$u->fetch($data['fk_user_origin']);

		$client->fk_user = $data['fk_user_origin'];
		$client->username = $data['username'];
		$client->userpicto = '';
		$client->fk_entity = $u->entity;

		if ($this->showuserpicto) $client->userpicto = Form::showphoto('userphoto', $u, 16, 0, 0, 'photologintooltip', 'mini', 0, 1);
		
		$this->_actionGetAllClient($data, $client);
	}
	
	private function _actionGetAllClient($data, $client)
	{
		$Tab = array();
		
		foreach ($this->_clients as $sendto)
		{
			if (!$this->speak_with_entities && $sendto->fk_entity != $client->fk_entity && $client->fk_entity != 0) continue;
			if ($sendto->getClientId() !== $client->getClientId())
			{
				$Tab[] = array('fk_user' => $sendto->fk_user, 'username' => $sendto->username, 'userpicto' => $sendto->userpicto);
			}
		}
		
		usort($Tab, array('Birddy', '_sortByUsername'));
		
		$client->send($this->_encodeData('returnGetAllClient', array('TUser'=>$Tab)));
	}
	
	private static function _sortByUsername(&$a, &$b)
	{
		if ($a['username'] < $b['username']) return -1;
		elseif ($a['username'] > $b['username']) return 1;
		else return 0;
	}
	
	private function _actionSetFilename($filename)
	{
		if(strpos($filename, '\\') !== false)
		{
			$filename = substr($filename, strrpos($filename, '\\')+1);
		}
		elseif(strpos($filename, '/') !== false)
		{
			$filename = substr($filename, strrpos($filename, '/')+1);
		}
		if(!empty($filename))
		{
			$this->_filename = $filename;
			return true;
		}
		return false;
	}
	
}