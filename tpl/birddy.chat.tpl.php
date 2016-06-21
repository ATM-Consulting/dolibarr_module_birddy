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
	
	<input type="button"  class="button" id="birddyshowclients" title="<?php echo $langs->transnoentitiesnoconv('birddy_show_users'); ?>" value="V" />
	<span id="birddystatus" class="offline"></span>
	
	<div id="birddytabuser-container">
		<p><?php echo $langs->trans('birddy_speakwith'); ?></p>
		<ul id="birddytabuser"></ul>
	</div>
	
	<div id="birddy-tab">
		<ul id="birddy-tab-list">
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