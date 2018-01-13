<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
if (!jeedom::apiAccess(init('apikey'), 'JeePlcBus')) {
	echo __('Vous n\'etes pas autorisé à effectuer cette action', __FILE__);
	die();
}
if (isset($_GET['test'])) {
	echo 'OK';
	die();
}
$result = json_decode(file_get_contents("php://input"), true);
if (!is_array($result)) {
	die();
}

if (isset($result['devices'])) {
		
	foreach ($result['devices'] as $key => $datas) {
		
		if (!isset($datas['id'])) {
			if (isset($datas['housecode']) && isset($datas['unitcode'])) {
				$datas['id'] = $datas['housecode'] . $datas['unitcode'];
			} else {
				continue;
			}
		}

		log::add('JeePlcBus', 'debug', '******* Socket Informations DEB *******');
		log::add('JeePlcBus', 'debug', $datas['id']. '::' . $datas['command']. '::' . $datas['data1']. '::' . $datas['data2']);
		
		$logicalId = $datas['id'];
		$JeePlcBus = JeePlcBus::byLogicalId($datas['id'], 'JeePlcBus');

		if ($datas['command'] == "OFF" or $datas['command'] == "ON" or $datas['command'] == "DIM" or $datas['command'] == "BRIGHT" or $datas['command'] == "PRESET_DIM") {
				$etat = $datas['data1'];
				$type_maj_etat = 1;
		}
		
		elseif ($datas['command'] == "ALL_UNITS_OFF" or $datas['command'] == "ALL_LIGHTS_ON" or $datas['command'] == "ALL_LIGHTS_OFF" or $datas['command'] == "ALL_USER_LIGHTS_ON" or $datas['command'] == "ALL_USER_UNITS_OFF" or $datas['command'] == "ALL_USER_LIGHTS_OFF") {
				$etat = $datas['data1'];
				$type_maj_etat = 2;
		}

		elseif ($datas['command'] == "REPORT_SIGNAL_STRENGTH") {
				$etat = $datas['data1'];
				$type_maj_etat = 3;
		}
		
		elseif ($datas['command'] == "REPORT_NOISE_STRENGTH") {
				$etat = $datas['data1'];
				$type_maj_etat = 4;
		}
		
		elseif ($datas['command'] == "STATUS_OFF" or $datas['command'] == "STATUS_ON") {
				$etat = $datas['data1'];
				$type_maj_etat = 5;
		}
		
		else {
				$etat = -1;
				$type_maj_etat = 0;			
		}
		
		if ($etat >= 0 and $etat <= 100) {
			JeePlcBus::Maj_etat($logicalId, $etat, $type_maj_etat, $datas['num_ack']);		
		}

		log::add('JeePlcBus', 'debug', '******* Socket Informations FIN ******* ');
	}
}