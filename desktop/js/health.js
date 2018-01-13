
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

$('.bt_signalDevice').on('click',function(){
	$Id = $(this).data('id');
	$LogicalId = $(this).data('logicalid');
	$SignNois = $(this).data('signnois');
	bootbox.confirm("Interrogation module id : " + $LogicalId + " - " + $SignNois + " Strenght ?", function (result) {
		if (result) {
			$.ajax({
				type: "POST",
				url: "plugins/JeePlcBus/core/ajax/JeePlcBus.ajax.php",
				data: {
					action: "getSignals_cmd",
					Id: $Id,
					LogicalId: $LogicalId,
					SignNois: $SignNois,
				},
				dataType: 'json',
				global: false,
				error: function (error) {
					$('#div_alert').showAlert({message: '{{Erreur action non réalisée}}', level: 'danger'});
				},
				success: function () {
					function sleep (time) {
						return new Promise((resolve) => setTimeout(resolve, time));
					}
					sleep(3500).then(() => {
						$('#div_alert').showAlert({message: '{{Action réalisée avec succès}}', level: 'success'});
						$('#md_modal').dialog({title: "{{Santé JeePlcBus}}"});
						$('#md_modal').load('index.php?v=d&plugin=JeePlcBus&modal=health').dialog('open');
					});
			   }
			});
		}
	});
});