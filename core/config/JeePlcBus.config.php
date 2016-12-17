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

global $listCmdJeePlcBus_NODIM;
global $listCmdJeePlcBus_DIM;

$listCmdJeePlcBus_NODIM = array(
    array(
        'name' => 'Refresh',
        'type' => 'action',
        'subType' => 'other',
		'order' => 8,
        'description' => 'Pour MaJ du module',
		'generic_type' => 'GENERIC_ACTION',
		'forceReturnLineAfter' => '0',
    ),	
	
    array(
        'name' => 'Etat',
        'type' => 'info',
        'subType' => 'numeric',
		'order' => 2,
        'description' => 'Etat du module',
		'generic_type' => 'LIGHT_STATE',
		'forceReturnLineAfter' => '1',
    ),
	
    array(
        'name' => 'Off',
        'type' => 'action',
		'configuration' => array(
			'updateCmdToValue' => '0',
        ),
        'subType' => 'other',
		'order' => 1,
        'description' => 'Pour envoyer ON au module',
		'generic_type' => 'LIGHT_OFF',
		'forceReturnLineAfter' => '1',
    ),
	
    array(
        'name' => 'On',
        'type' => 'action',
		'configuration' => array(
			'updateCmdToValue' => '99',
        ),
        'subType' => 'other',
		'order' => 0,
        'description' => 'Pour envoyer ON au module',
		'generic_type' => 'LIGHT_ON',
		'forceReturnLineAfter' => '0',
    ),	
	
);

$listCmdJeePlcBus_DIM = array(
    array(
        'name' => 'Refresh',
        'type' => 'action',
        'subType' => 'other',
		'order' => 8,
        'description' => 'Pour MaJ du module',
		'generic_type' => 'GENERIC_ACTION',
		'forceReturnLineAfter' => '0',
    ),	
	
    array(
        'name' => 'Etat',
        'type' => 'info',
        'subType' => 'numeric',
		'order' => 2,
        'description' => 'Etat du module',
		'generic_type' => 'LIGHT_STATE',
		'forceReturnLineAfter' => '1',
    ),
	
/*     array(
        'name' => 'DIM',
        'configuration' => array(
			'tmps_dim' => '5',
        ),
        'type' => 'action',
        'subType' => 'slider',
		'order' => 3,
        'description' => 'Pour fonction dimmer',
		'generic_type' => 'LIGHT_SLIDER',
		'forceReturnLineAfter' => '1',
    ), */
	
    array(
        'name' => '25',
        'type' => 'action',
		'configuration' => array(
			'updateCmdToValue' => '25',
			'tmps_dim' => '5',
        ),
        'subType' => 'other',
		'order' => 4,
        'description' => 'Pour envoyer DIM a 25 au module',
		'generic_type' => 'LIGHT_MODE',
		'forceReturnLineAfter' => '0',
    ),
	
    array(
        'name' => '50',
        'type' => 'action',
		'configuration' => array(
			'updateCmdToValue' => '50',
			'tmps_dim' => '5',
        ),
        'subType' => 'other',
		'order' => 5,
        'description' => 'Pour envoyer DIM a 50 au module',
		'generic_type' => 'LIGHT_MODE',
		'forceReturnLineAfter' => '0',
    ),	

    array(
        'name' => '75',
        'type' => 'action',
		'configuration' => array(
			'updateCmdToValue' => '75',
			'tmps_dim' => '5',
        ),
        'subType' => 'other',
		'order' => 6,
        'description' => 'Pour envoyer DIM a 75 au module',
		'generic_type' => 'LIGHT_MODE',
		'forceReturnLineAfter' => '0',
    ),		
	
    array(
        'name' => 'Off',
        'type' => 'action',
		'configuration' => array(
			'updateCmdToValue' => '0',
        ),
        'subType' => 'other',
		'order' => 1,
        'description' => 'Pour envoyer ON au module',
		'generic_type' => 'LIGHT_OFF',
		'forceReturnLineAfter' => '1',
    ),
	
    array(
        'name' => 'On',
        'type' => 'action',
		'configuration' => array(
			'updateCmdToValue' => '99',
        ),
        'subType' => 'other',
		'order' => 0,
        'description' => 'Pour envoyer ON au module',
		'generic_type' => 'LIGHT_ON',
		'forceReturnLineAfter' => '0',
    ),	
	
);
?>
