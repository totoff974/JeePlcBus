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
			<th>{{Force du Signal}}</th>
			<th>{{Intensité du Bruit}}</th>
            <th>{{Etat}}</th>
        </tr>
    </thead>
	<tbody>
	<?php
	$code_maison = init('codemaison');
		for ($i=1; $i<17; $i++) {
			$d_code = $code_maison.$i;
			$d_retour = JeePlcBus::Requete_MaJ($d_code, true);
			$fin = '</span></td>';
			$tr = '<tr>';
			
			if ($d_retour != 'ERREUR pas de reponse') {
				$deb = '<td><span style="width:100%;" class="btn btn-xs btn-success">';
				$force_du_signal = JeePlcBus::force_du_signal($d_code, true);
				$intensite_du_bruit = JeePlcBus::intensite_du_bruit($d_code, true);
				$etat = 'OK';
				
				$tr .= $deb . $d_code . $fin;
				$tr .= $deb . $d_retour . $fin;
				$tr .= $deb . $force_du_signal . $fin;
				$tr .= $deb . $intensite_du_bruit . $fin;
				$tr .= $deb . $etat . $fin;				
			}
			else {
				$deb = '<td><span style="width:100%;" class="btn btn-xs btn-danger">';
				$etat = 'NOK';
				
				$tr .= $deb . $d_code . $fin;
				$tr .= $deb . $d_retour . $fin;
				$tr .= '<td></td>';
				$tr .= '<td></td>';
				$tr .= $deb . $etat . $fin;
			}
			
			echo $tr;
			
		}
	?>	
    </tbody>
</table>
</div>