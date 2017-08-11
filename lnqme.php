<?php
/*
Plugin Name: 	LNQ.me url shortener
Plugin URI: 	http://www.lnq.me/
Description: 	With LNQ.me url shortener you can shorten your wordpress pages and posts to a LNQ.ME url. 
				You can follow the statistics for each other post and page.
Version:		1.2
Author: 		DigiFactory Webworks
Author URI: 	http://www.digifactory.nl/

Copyright 2011 DigiFactory Webworks (info@digifactory.nl)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once("lnqme-options.php");
require_once("lnqme-views.php");
require_once("screenmetalinks.php");

/*
 * Create an instance of lnqmeOptions
 */
$lnqme = new lnqmeOptions(array("lnqme_username" => "", "lnqme_api_key"  => ""));

add_action("admin_init", "createTwitterButton");
add_action("admin_init", "lnqmeUserDataInit");
add_action("admin_init", "addLNQMEStatisticsBox", 1);
add_action("admin_menu", "addPages");

add_filter("plugin_action_links", "addSettingsLink", 10, 2);
add_filter("pre_get_shortlink", "getShortlink", 10, 4);

?>