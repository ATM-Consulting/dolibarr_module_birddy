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
			var fk_user;

			if (<?php echo (int) $user->id; ?> == data.fk_user_origin) fk_user = data.fk_user_target;
			else fk_user = data.fk_user_origin;
			
			var elBirddylog = $('#birddylog-'+fk_user);
			if (elBirddylog.length == 0) { 
				openChat(data.username_origin, fk_user);
				elBirddylog = $(elBirddylog.selector);
			}
			
			elBirddylog.append("<p class='"+ class_string +"'>" + msg + "</p>");
			elBirddylog.scrollTop = elBirddylog.scrollHeight;
		};
		 
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
					if (<?php echo (int) $user->id; ?> == data.fk_user_origin)
					{
						birddylog(data, "<?php echo $langs->transnoentities('birddy_You'); ?>", "lightblue bold");
						birddylog(data, "<?php echo $langs->transnoentities('birddy_say'); ?> " + data.msg, "lightblue");
					}
					else
					{
						birddylog(data, data.username_origin, "lightred bold");
						birddylog(data, "<?php echo $langs->transnoentities('birddy_say'); ?> " + data.msg, "lightred");	
					}

					break;
					
				case 'returnConnectId':
					$('#birddyconnectionid').val(data.connectId);
					
					var payload;
					payload = new Object();
					payload.action = 'setUserToSocketClient';
					payload.fk_user_origin = <?php echo (int) $user->id; ?>;
					payload.username = '<?php echo $user->firstname.' '.$user->lastname; ?>';
					birddySocket.send(JSON.stringify(payload));
					
					break;
					
				case 'returnGetAllClient':
					var TClassRow = ['impair', 'pair']
						, tabUser = $('#birddytabuser')
						, j = 0;
					
					tabUser.empty();
					for (var i=0; i<data.TUser.length; i++)
					{
						if (data.TUser[i].fk_user != <?php echo (int) $user->id; ?> && $('#birddytabuser li[data-fk-user='+data.TUser[i].fk_user+']').length == 0)
						{
							tabUser.append('<li class="'+TClassRow[j]+'" data-fk-user="'+data.TUser[i].fk_user+'">'+data.TUser[i].userpicto+data.TUser[i].username+'</li>');
							j = j ? 0 : 1;
						}
					}
					
					$('#birddytabuser li').dblclick(function(event) {
						openChat(event.target.textContent, event.target.dataset.fkUser);
					});
					
					break;
			}
			
			return;
		};
		
		birddySocket.onclose = function(msg) {
			return $('#birddystatus').removeClass('online').addClass('offline').attr('title', 'disconnected');
		};
		
		
		function openOrCloseListUser(callback) {
			var box = $("#birddytabuser-container"); 
			
			if (box.hasClass("open"))
			{
				box.animate({right:"143px"}, callback).removeClass("open");
			}
			else
			{
				var payload;
				payload = new Object();
				payload.action = 'getAllClient';
				
				birddySocket.send(JSON.stringify(payload));
			
				box.animate({right:"275px"}, callback).addClass("open");
			}
		}
		
		$('#birddyshowclients').click(function(event) {
			openOrCloseListUser();
		});
		
		function minimizeChat(step) {
			var birddychat = $("#birddychat");
			
			if (step == 1) {
				tabUserHide();
				birddychat.animate({bottom:-258}, function() { minimizeChat(2); });
			} else {
				birddychat.animate({width:75}).removeClass('open').addClass('close');
			}
		}
		
		function maximizeChat(step) {
			var birddychat = $("#birddychat");
			
			if (step == 1) {
				birddychat.animate({width:270}, function() { maximizeChat(2); });
			} else {
				birddychat.animate({bottom:0}, tabUserShow).removeClass('close').addClass('open');
			}
		}
		
		function hideOrShowChat() {
			var birddychat = $("#birddychat");
			
			if (birddychat.hasClass('close')) {
				maximizeChat(1);
			} else {
				minimizeChat(1);
			}
		}
		
		function tabUserHide() {
			$("#birddytabuser-container").hide()
		}
		
		function tabUserShow() {
			$("#birddytabuser-container").show()
		}
		
		$("#birddy-reduce-window").click(function(event) {
			var tabUser = $("#birddytabuser-container");
			
			if (tabUser.hasClass('open')) openOrCloseListUser(hideOrShowChat);
			else hideOrShowChat();
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
				payload.username_origin = '<?php echo $user->firstname.' '.$user->lastname; ?>';
				payload.fk_user_origin = <?php echo (int) $user->id; ?>;
				payload.fk_user_target = $('#birddy-tab-container .birddylog.active').data('fk-user');
				
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
		
		function openChat(username, fk_user) {
			if ($('#birddytab-'+fk_user).length == 0)
			{
				var shortName = username;
				if (shortName.length > 8) shortName = shortName.substring(0, 8)+'...';
				var li = $('<li id="birddytab-'+fk_user+'" data-fk-user="'+fk_user+'" class="birddytab" title="'+username+'">'+shortName+' <i class="birddy-close-tab fa fa-times" data-fk-user="'+fk_user+'" title="<?php echo $langs->trans('birddy_close_tab'); ?>"></i></li>');
				var log = $('<div id="birddylog-'+fk_user+'" data-fk-user="'+fk_user+'" class="birddylog"></div>');
				
				$('#birddy-tab-list').append(li);
				$('#birddy-tab-container').append(log)	
			}
			
			$('#birddy-tab-list li').unbind().bind('click', function(event) {
				// Select the tab
				$('#birddy-tab-list li.active').removeClass('active');
				$('#birddytab-'+event.target.dataset.fkUser).addClass('active');
				
				// Select the dialog to show
				$('#birddy-tab-container .birddylog.active').removeClass('active');
				$('#birddylog-'+event.target.dataset.fkUser).addClass('active');
			});
			
			$('#birddychat .birddy-close-tab').unbind().bind('click', function(event) {
				var fk_user = event.target.dataset.fkUser
					,otherTab = $('#birddytab-'+fk_user).prev();
				
				if (otherTab.length == 0) otherTab = $('#birddytab-'+fk_user).next();
				
				$('#birddytab-'+fk_user+', #birddylog-'+fk_user).remove();
				
				setTimeout(function() {
					otherTab.trigger('click');
				},1);
			});
			
			$('#birddytab-'+fk_user).trigger('click');
		}
		
		function moveTab(direction) {
			var tab = $('#birddy-tab-list');
			
			if (tab.data('currently-moving') == 0)
			{
				var pos = tab.css('left');
				if (direction == 'right' && pos == '0px') return;
				
				tab.data('currently-moving', 1);
				var move = '-=50px';
				if (direction == 'right') move = '+=50';
				
				$('#birddy-tab-list').animate({left:move}, function() {
					var newpos = tab.css('left'); 
					if (newpos == '0px') $('#birddychat .birddy-move-tab.direction-left').fadeOut(200);
					else $('#birddychat .birddy-move-tab.direction-left').fadeIn(200)
					// TODO test if pos == end
					
					tab.data('currently-moving', 0); 
				});
			}
			
			return;
		}
		
		$('#birddychat .birddy-move-tab').click(function(event) {
			moveTab(event.target.dataset.direction);
		});
		
	});
	
});