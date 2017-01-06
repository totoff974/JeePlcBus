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
include_file('core', 'authentification', 'php');
if (!isConnect()) {
	include_file('desktop', '404', 'php');
	die();
}
?>
<form class="form-horizontal">
    <fieldset>
            <legend><i class="icon loisir-darth"></i> {{Aide au développement}}</legend>
			<span><i>Ce plugin est gratuit, le don est laissé au libre choix de chacun en fonction de sa satisfaction pour m'aider au développement. Merci.</i></span>
			<div class="form-group" align="center">	
                <div align="center">
					<a class="btn" id="bt_paypal" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7BBF45ZDD9Y8L" target="_new" >
					<img src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_donateCC_LG.gif" border="0" alt="{{Faire un don via Paypal au développeur}}">
					<img alt="" border="0" src="https://www.paypalobjects.com/fr_XC/i/scr/pixel.gif" width="1" height="1">
					</a>
               </div>
           </div>		
    </fieldset>
	
    <fieldset>
            <legend><i class="icon loisir-darth"></i> {{Configuration du PLCBus}}</legend>
            <div class="form-group">
                <label class="col-sm-4 control-label">{{Port PLCBus :}}</label>
                <div class="col-sm-4">
                    <select class="configKey form-control" data-l1key="port">
                        <option value="none">{{Aucun}}</option>
                        <?php
foreach (jeedom::getUsbMapping() as $name => $value) {
	echo '<option value="' . $name . '">' . $name . ' (' . $value . ')</option>';
}
foreach (ls('/dev/', 'tty*') as $value) {
	echo '<option value="/dev/' . $value . '">/dev/' . $value . '</option>';
}
?>
                   </select>
               </div>
           </div>
		
        <div class="form-group expertModeVisible">
                <label class="col-sm-4 control-label">{{Nombre de Phases :}}</label>
                <div class="col-sm-4">
                    <select class="configKey form-control" data-l1key="phase">
                        <option value="1">{{1}}</option>
						<option value="3">{{3}}</option>
                   </select>
               </div>
        </div>
		
	    <div class="form-group expertModeVisible">
            <label class="col-sm-4 control-label">{{UserCode :}}</label>
            <div class="col-sm-2">
                <input class="configKey form-control" data-l1key="plcbus_usercode"/>
            </div>
        </div>
		
        <div class="form-group">
            <label class="col-sm-4 control-label">{{Test de Communication pour le code Maison : }}</label>
            <div class="col-sm-2">
			    <select id="c_maison" class="configKey form-control">
                    <option value="A">{{A}}</option>
					<option value="B">{{B}}</option>
					<option value="C">{{C}}</option>
					<option value="D">{{D}}</option>
					<option value="E">{{E}}</option>
					<option value="F">{{F}}</option>
					<option value="G">{{G}}</option>
					<option value="H">{{H}}</option>
					<option value="I">{{I}}</option>
					<option value="J">{{J}}</option>
					<option value="K">{{K}}</option>
					<option value="L">{{L}}</option>
					<option value="M">{{M}}</option>
					<option value="N">{{N}}</option>
					<option value="O">{{O}}</option>
					<option value="P">{{P}}</option>
                </select>
			</div>
			
			<div class="col-sm-2">
                <a class="btn btn-default" id="bt_testComPlcBus" ><i class="fa fa-cogs"></i> {{Exécuter le test}}</a>
            </div>
			
        </div>
		
    </fieldset>
</form>

<script>
    $('#bt_testComPlcBus').on('click', function () {
		var codemaison = document.getElementById('c_maison').value;
        $('#md_modal2').dialog({title: "{{Test de la Communication}}"});
        $('#md_modal2').load('index.php?v=d&plugin=JeePlcBus&modal=test.communication&codemaison='+codemaison).dialog('open');
    });
</script>