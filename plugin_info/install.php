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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function JeePlcBus_install() {
	if (config::byKey('api::JeePlcBus::mode') == '') {
		config::save('api::JeePlcBus::mode', 'localhost');
	}
}

function JeePlcBus_update() {
	if (config::byKey('api::JeePlcBus::mode') == '') {
		config::save('api::JeePlcBus::mode', 'localhost');
	}
	foreach (eqLogic::byType('JeePlcBus') as $eqLogic) {
		$device_type = explode('::', $eqLogic->getConfiguration('device'));
		$packettype = $device_type[0];
		$subtype = $device_type[1];
		$device = JeePlcBus::devicesParameters($packettype);
		if (!isset($device['subtype'][$subtype])) {
			if (count($device['subtype']) != 1) {
				continue;
			}
			$device = reset($device['subtype']);
		} else {
			$device = $device['subtype'][$subtype];
		}
		foreach ($device['commands'] as $command) {
			if (!isset($command['logicalId'])) {
				continue;
			}
			$cmd = $eqLogic->getCmd(null, $command['logicalId']);
			if (is_object($cmd) && $cmd->getDisplay('generic_type') == '' && isset($command['display']['generic_type'])) {
				$cmd->setDisplay('generic_type', $command['display']['generic_type']);
				$cmd->save();
			}
		}
	}
}

function JeePlcBus_remove() {

}

?>
