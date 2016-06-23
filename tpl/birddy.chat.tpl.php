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
 
 require '../config.php';
 $langs->load('birddy@birddy');
 
 ?>
 
<div id="birddychat">
	<input id="birddyconnectionid" type="hidden" value="" />
	<input id="birddyclientid" type="hidden" value="" />
	
	<i id="birddyshowclients" class=" fa fa-user-plus" aria-hidden="true" title="<?php echo $langs->transnoentitiesnoconv('birddy_show_users'); ?>"></i>
	<i id="birddy-reduce-window" class="fa fa-minus-square-o" aria-hidden="true" title="<?php echo $langs->transnoentitiesnoconv('birddy_reduce_box'); ?>"></i>

	
	<i id="birddystatus" class="offline fa fa-circle" aria-hidden="true"></i>

	
	
	<div id="birddy-tab">
		<i data-direction="right" class="birddy-move-tab direction-left fa fa-chevron-left" aria-hidden="true"></i>
		<i data-direction="left" class="birddy-move-tab direction-right fa fa-chevron-right" aria-hidden="true"></i>
		
		<ul style='' id="birddy-tab-list" data-currently-moving="0">
		</ul>
		
		<div id="birddy-tab-container">
		</div>	
	</div>
	
	
    <!-- <div id="birddylog"></div> -->
    
	
	<div id="birddyactionbar">
	    <input id="birddydata" placeholder="text..." type="text" class="lightblue" />
	    <!-- <input id="birddysend" type="button" value="Send" /> -->
	</div>
	
	
	<!--
	<h2>Send Binary Frame</h2>
	<form name="binaryFrame" action="#">
		<input type="file" name="file" id="file">
		<button id="sendfile">Send Binary</button>
	</form>
	-->
</div>

<div id="birddytabuser-container">
	<i id="birddytabuser-logo" class="fa fa-users" aria-hidden="true" title="<?php echo $langs->trans('birddy_speakwith'); ?>"></i>
	<ul id="birddytabuser"></ul>
</div>
	