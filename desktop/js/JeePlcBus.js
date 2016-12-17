
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

 $("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

 function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
	
 if (init(_cmd.type) == 'info') {
     var disabled = (init(_cmd.configuration.virtualAction) == '1') ? 'disabled' : '';
     var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '" virtualAction="' + init(_cmd.configuration.virtualAction) + '">';
     tr += '<td>';
     tr += '<span class="cmdAttr" data-l1key="id"></span>';
     tr += '</td>';
     tr += '<td> ';
     tr += '<input disabled class="cmdAttr form-control input-sm" data-l1key="name" style="width : 100%;"></td>';
     tr += '<td>';
     tr += '<input class="cmdAttr form-control type input-sm expertModeVisible" data-l1key="type" value="info" disabled style="margin-bottom : 5px;" />';
     tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
     tr += '</td>';
     tr += '<td>';
     tr += '</td>';
     tr += '<td>';
     tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr bootstrapSwitch" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
     tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr bootstrapSwitch" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
     tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr expertModeVisible" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label></span><br/>';
     tr += '</td>';
     tr += '<td>';
     if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction " data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor expertModeVisible" data-action="remove"></i></td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}

if (init(_cmd.type) == 'action') {
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id"></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<div class="row">';
    tr += '<div class="col-sm-6">';
    tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fa fa-flag"></i> Icône</a>';
    tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
    tr += '</div>';
    tr += '<div class="col-sm-6">';
    tr += '<input disabled class="cmdAttr form-control input-sm" data-l1key="name">';
    tr += '</div>';
    tr += '</div>';
	if (_cmd.name != "Refresh") {
    tr += '<select class="cmdAttr form-control tooltips input-sm expertModeVisible" data-l1key="value" style="display : none;margin-top : 5px;margin-right : 10px;">';
    tr += '</select>';
	}
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control type input-sm expertModeVisible" data-l1key="type" value="action" disabled style="margin-bottom : 5px;" />';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '<input class="cmdAttr" data-l1key="configuration" data-l2key="virtualAction" value="1" style="display:none;" >';
    tr += '</td>';
    tr += '<td>';
	if (_cmd.name != "On" && _cmd.name != "Off" && _cmd.name != "Refresh") {
    tr += '<span>{{Durée du Dimmer (en seconde) : }}<br/></span><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="tmps_dim" style="margin-bottom : 5px;width : 50%; display : inline-block;" />';
	}
    tr += '</td>';
    tr += '<td>';
	if (_cmd.name != "Refresh") {
    tr += '<select class="cmdAttr form-control input-sm expertModeVisible" data-l1key="configuration" data-l2key="updateCmdId" style="display : none;margin-top : 5px;">';
    tr += '</select>';
    tr += '<input class="cmdAttr form-control input-sm expertModeVisible" data-l1key="configuration" data-l2key="updateCmdToValue" style="display : none;margin-top : 5px;">';
	}
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr bootstrapSwitch" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction " data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor expertModeVisible" data-action="remove"></i></td>';
    tr += '</tr>';

    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    var tr = $('#table_cmd tbody tr:last');
    jeedom.eqLogic.builSelectCmd({
       id: $(".li_eqLogic.active").attr('data-eqLogic_id'),
        filter: {type: 'info'},
        error: function (error) {
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success: function (result) {
            tr.find('.cmdAttr[data-l1key=value]').append(result);
			tr.find('.cmdAttr[data-l1key=configuration][data-l2key=value]').append(result);
            tr.find('.cmdAttr[data-l1key=configuration][data-l2key=updateCmdId]').append(result);
            tr.setValues(_cmd, '.cmdAttr');
            jeedom.cmd.changeType(tr, init(_cmd.subType));
        }
    });
}
}