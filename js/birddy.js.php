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

$(function() {
	$.get('<?php echo dol_buildpath('/birddy/tpl/birddy.chat.tpl.php', 1); ?>', function(html) {
		$('body').append(html);
		
		var birddyServerUrl, birddySocket;
		
		function birddylog(data, msg, class_string) {
			var clientId;

			if ($('#birddyconnectionid').val() == data.fromClientId) clientId = data.clientIdTarget;
			else clientId = data.fromClientId;
			
			var elBirddylog = $('#birddylog-'+clientId);
			if (elBirddylog.length == 0) openChat(data.username, clientId);
			
			elBirddylog.append("<p class='"+ class_string +"'>" + msg + "</p>");
			elBirddylog.scrollTop = elBirddylog.scrollHeight;
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
		
		birddySocket.onopen = function(event) {
			return $('#birddystatus').removeClass('offline').addClass('online').attr('title', 'connected');
		};
		birddySocket.onmessage = function(event) {
			var data = JSON.parse(event.data);
			
			switch (data.action) {
				case 'echo':
					if ($('#birddyconnectionid').val() == data.fromClientId)
					{
						birddylog(data, "<b><?php echo $langs->transnoentities('birddy_You'); ?></b>");
						birddylog(data, "<?php echo $langs->transnoentities('birddy_say'); ?> " + data.msg);
					}
					else
					{
						birddylog(data, "<b>" + data.username + "</b>");
						birddylog(data, "<?php echo $langs->transnoentities('birddy_say'); ?> " + data.msg);	
					}

					break;
					
				case 'returnConnectId':
					$('#birddyconnectionid').val(data.connectId);
					
					var payload;
					payload = new Object();
					payload.action = 'setUserToSocketClient';
					payload.userId = <?php echo (int) $user->id; ?>;
					payload.username = '<?php echo $user->firstname.' '.$user->lastname; ?>';
					birddySocket.send(JSON.stringify(payload));
					
					break;
					
				case 'returnGetAllClient':
					var tabUser = $('#birddytabuser');
					tabUser.empty();
					for (var i=0; i<data.TUser.length; i++)
					{
						tabUser.append('<li data-fk-user="'+data.TUser[i].userId+'" data-client-id="'+data.TUser[i].clientId+'">'+data.TUser[i].username+'</li>');
					}
					
					$('#birddytabuser li').dblclick(function(event) {
						openChat(event.target.innerText, event.target.dataset.clientId);
					});
					
					break;
			}
			
			return;
		};
		birddySocket.onclose = function(msg) {
			return $('#birddystatus').removeClass('online').addClass('offline').attr('title', 'disconnected');
		};
		
		
		$('#birddyshowclients').click(function(event) {
			var payload;
			payload = new Object();
			payload.action = 'getAllClient';
			console.log('sdgdf');
			return birddySocket.send(JSON.stringify(payload));
		});
		
		/*
		$('#birddystatus').click(function() {
			return birddySocket.close();
		});
		*/
		
		$('#birddydata').keypress(function(event) {
			if (event.keyCode === 13) {
				var payload;
				payload = new Object();
				payload.action = 'echo';
				payload.msg = $('#birddydata').val();
				payload.username = '<?php echo $user->firstname.' '.$user->lastname; ?>';
				payload.fromClientId = $('#birddyconnectionid').val();
				payload.clientIdTarget = $('#birddy-tab-container .birddylog.active').data('client-id');
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
		
		function openChat(username, clientId) {
			if ($('#birddytab-'+clientId).length == 0)
			{
				var li = $('<li id="birddytab-'+clientId+'" data-client-id="'+clientId+'" class="birddytab">'+username+'</li>');
				var log = $('<div id="birddylog-'+clientId+'" data-client-id="'+clientId+'" class="birddylog"></div>');
				
				$('#birddy-tab-list').append(li);
				$('#birddy-tab-container').append(log)	
			}
			
			$('#birddy-tab-list li').unbind().bind('click', function(event) {
				// Select the tab
				$('#birddy-tab-list li.active').removeClass('active');
				$('#birddytab-'+event.target.dataset.clientId).addClass('active');
				
				// Select the dialog to show
				$('#birddy-tab-container .birddylog.active').removeClass('active');
				$('#birddylog-'+event.target.dataset.clientId).addClass('active');
			});
			
			$('#birddytab-'+clientId).trigger('click');
		}
		
	});
	
});