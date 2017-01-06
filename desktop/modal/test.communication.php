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
if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
include_file('core', 'JeePlcBus', 'config', 'JeePlcBus');

?>

<div class="col-md-12">
  <div class="panel panel-success">
    <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-check-circle"></i> Résultat du test</h3></div>

    <table id="table_testComPlcBus" class="table table-bordered table-condensed tablesorter">
    <thead>
        <tr>
            <th>{{Adresse du Module}}</th>
            <th>{{Retour de la requête}}</th>
            <th>{{Etat}}</th>
        </tr>
    </thead>
	<tbody>
	<?php
	$code_maison = init('codemaison');
		for ($i=1; $i<17; $i++) {
			$d_code = $code_maison.$i;
			$d_retour = JeePlcBus::Requete_MaJ($d_code);
			
			$tr = '<tr>';
			$tr .= '<td>'.$d_code.'</td>';
			$tr .= '<td>'.$d_retour.'</td>';
			
			if ($d_retour != 'ERROR pas de reponse') {
				$tr .= '<td><span class="btn btn-xs btn-success">OK</span></td>';
			}
			else {
				$tr .= '<td><span class="btn btn-xs btn-danger">NOK</span></td>';
			}
			
			echo $tr;
		}
	?>	
    </tbody>
</table>
</div>