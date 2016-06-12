<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2016 ATM Consulting <support@atm-consulting.fr>
 * Copyright (C) 2016 Pierre-Henry Favre <phf@atm-consulting.fr>
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

//DEFINE('INC_FROM_DOLIBARR', true);
require '../config.php';

if (empty($conf->global->BIRDDY_SERVER_ADDR) || empty($conf->global->BIRDDY_PORT))
{
	exit;
}
$langs->load('birddy@birddy');

?>
 
function includeJS(incFile)
{
   document.write('<script type="text/javascript" src="'+ incFile+ '"></script>');
}

$(function() {
	$.get('<?php echo dol_buildpath('/birddy/tpl/birddy.chat.tpl.php', 1); ?>', function(html) {
		$('body').append(html);
		
		
		var birddylog, birddyServerUrl, birddySocket;
		birddylog = function(msg) {
			return $('#birddylog').append("" + msg + "<br />");
		};
		// TODO replace server url 
		birddyServerUrl = 'ws://<?php echo $conf->global->BIRDDY_SERVER_ADDR.':'.$conf->global->BIRDDY_PORT; ?>/birddy';
		
		try {
			if (window.MozWebSocket) {
				birddySocket = new MozWebSocket(birddyServerUrl);
			} else if (window.WebSocket) {
				birddySocket = new WebSocket(birddyServerUrl);
			}	
		} catch (error) {
			console.log(error);
			return;
		}
		
		birddySocket.binaryType = 'blob';
		
		birddySocket.onopen = function(msg) {
			return $('#birddystatus').removeClass('offline').addClass('online').attr('title', 'connected');
		};
		birddySocket.onmessage = function(response) {
			//console.log(response);
			var data = JSON.parse(response.data);
			birddylog('<b>'+data.username+'</b>');
			return birddylog("<?php echo $langs->transnoentities('birddy_say'); ?> " + data.msg);
		};
		birddySocket.onclose = function(msg) {
			return $('#birddystatus').removeClass('online').addClass('offline').attr('title', 'disconnected');
		};
		
		/*
		$('#birddystatus').click(function() {
			return birddySocket.close();
		});
		*/
		
		$('#birddydata').keypress(function(event) {
			if (event.keyCode === 13) {
				var payload;
				payload = new Object();
				payload.action = $('#birddyaction').val();
				payload.msg = $('#birddydata').val();
				payload.username = '<?php echo $user->firstname.' '.$user->lastname; ?>';
				$('#birddydata').val('');
				return birddySocket.send(JSON.stringify(payload));
			}
		});
		
		/*
		return $('#sendfile').click(function() {
			var data, payload;
			data = document.binaryFrame.file.files[0];
			if (data) {
				payload = new Object();
				payload.action = 'setFilename';
				payload.data = $('#file').val();
				birddySocket.send(JSON.stringify(payload));
				birddySocket.send(data);
			}
			return false;
		});
		*/
		
		
		
	});
	
});