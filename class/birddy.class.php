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

if (!defined('INC_FROM_DOLIBARR')) define('INC_FROM_DOLIBARR', true);
require_once '../config.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

class Birddy extends \WebSocket\Application\Application
{
    private $_clients = array();
	private $_filename = '';
	
	public $db;
	public $showuserpicto = false;
	
	protected function __construct()
	{
		global $db,$conf;
		
		$this->db = &$db;
		if (!empty($conf->global->BIRDDY_SHOW_USER_PICTO) && method_exists(Form, 'showphoto')) $this->showuserpicto = true;
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
	
	private function _actionEcho($data)
	{
		if (empty($data['msg']) || empty($data['fk_user_target']) || empty($data['fk_user_origin'])) return false;
		
		$encodedData = $this->_encodeData('echo', $data);
		foreach($this->_clients as $sendto)
		{
			if (!empty($data['fk_user_target']))
			{
				if (in_array($sendto->fk_user, array($data['fk_user_target'], $data['fk_user_origin']))) $sendto->send($encodedData);
			}
			else
			{
				$sendto->send($encodedData);
			}
        }
	}
	
	private function _actionSetUserToSocketClient($data, $client)
	{
		$u = new User($this->db);
		$u->fetch($data['fk_user_origin']);
					
		$client->fk_user = $data['fk_user_origin'];
		$client->username = $data['username'];
		$client->userpicto = '';
		if ($this->showuserpicto) $client->userpicto = Form::showphoto('userphoto', $u, 16, 0, 0, 'photologintooltip', 'mini', 0, 1);
		
		$this->_actionGetAllClient($data, $client);
	}
	
	private function _actionGetAllClient($data, $client)
	{
		$Tab = array();
		
		foreach ($this->_clients as $sendto)
		{
			if ($sendto->getClientId() !== $client->getClientId())
			{
				$Tab[] = array('fk_user' => $sendto->fk_user, 'username' => $sendto->username, 'userpicto' => $sendto->userpicto);
			}
		}

		$client->send($this->_encodeData('returnGetAllClient', array('TUser'=>$Tab)));
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