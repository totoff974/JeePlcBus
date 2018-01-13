
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

 $(document).ready(function() {
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=device]').on('change', function () {
      if($('.li_eqLogic.active').attr('data-eqlogic_id') != ''){
        getModelList($(this).value(),$('.li_eqLogic.active').attr('data-eqlogic_id'));
        $('#img_device').attr("src", 'plugins/JeePlcBus/core/config/devices/'+$(this).value()+'.jpg');
    }else{
        $('#img_device').attr("src",'plugins/JeePlcBus/doc/images/JeePlcBus_icon.png');
    }  
});

    $('.eqLogicAttr[data-l1key=configuration][data-l2key=iconModel]').on('change', function () {
      if($(this).value() != '' && $(this).value() != null){
        $('#img_device').attr("src", 'plugins/JeePlcBus/core/config/devices/'+$(this).value()+'.jpg');
    }
});

});

 $('#bt_healthplcbus').on('click', function () {
    $('#md_modal').dialog({title: "{{Santé JeePlcBus}}"});
    $('#md_modal').load('index.php?v=d&plugin=JeePlcBus&modal=health').dialog('open');
});

 $('.eqLogicAttr[data-l1key=configuration][data-l2key=device]').on('change', function () {
    var instruction = $('.eqLogicAttr[data-l1key=configuration][data-l2key=device] option:selected').attr('data-instruction');
    $('#div_instruction').empty();
    if(instruction != '' && instruction != undefined){
       $('#div_instruction').html('<div class="alert alert-info">'+instruction+'</div>');
   }
});

 function getModelList(_conf,_id) {
    $.ajax({
        type: "POST", 
        url: "plugins/JeePlcBus/core/ajax/JeePlcBus.ajax.php", 
        data: {
            action: "getModelList",
            conf: _conf,
            id: _id,
        },
        dataType: 'json',
        global: false,
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            var options = '';
            var options = '';
            for (var i in data.result) {
                if (data.result[i]['selected'] == 1){
                    options += '<option style="display:none;" value="'+i+'" selected>'+data.result[i]['value']+'</option>';
                } else {
                    options += '<option style="display:none;" value="'+i+'">'+data.result[i]['value']+'</option>';
                }
            }
            $(".modelList").show();
            $(".listModel").html(options);
            $icon = $('.eqLogicAttr[data-l1key=configuration][data-l2key=iconModel]').value();
			if($icon != '' && $icon != null){
				$('#img_device').attr("src", 'plugins/JeePlcBus/core/config/devices/'+$icon+'.jpg');
			}
        }
    });
}

$('body').delegate('.cmd .cmdAttr[data-l1key=type]', 'change', function () {
    if ($(this).value() == 'action') {
        $(this).closest('.cmd').find('.cmdAttr[data-l1key=configuration][data-l2key=id]').show();
    } else {
        $(this).closest('.cmd').find('.cmdAttr[data-l1key=configuration][data-l2key=id]').hide();
    }
});


$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});


function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<div class="row">';
    tr += '<div class="col-sm-6">';
    tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fa fa-flag"></i> {{Icône}}</a>';
    tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
    tr += '</div>';
    tr += '<div class="col-sm-6">';
    tr += '<input disabled class="cmdAttr form-control input-sm" data-l1key="name">';
    tr += '</div>';
    tr += '</div>';
    tr += '<select disabled class="cmdAttr form-control input-sm" data-l1key="value" style="display : none;margin-top : 5px;" title="La valeur de la commande vaut par défaut la commande">';
    tr += '<option style="display:none;" value="">Aucune</option>';
    tr += '</select>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="id" style="display:none;">';
    tr += '<span style="display:none;" class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
    tr += '<span style="display:none;" class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';
    tr += '<td class="expertModeVisible"><input disabled class="cmdAttr form-control input-sm" data-l1key="logicalId" value="0" style="width : 70%; display : inline-block;" placeholder="{{Commande}}"><br/>';
    tr += '<input type="hidden" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="returnStateValue" placeholder="{{Valeur retour d\'état}}" style="width : 20%; display : inline-block;margin-top : 5px;margin-right : 5px;">';
    tr += '<input type="hidden" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="returnStateTime" placeholder="{{Durée avant retour d\'état (min)}}" style="width : 20%; display : inline-block;margin-top : 5px;margin-right : 5px;">';
    tr += '</td>';
    tr += '<td>';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr expertModeVisible" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label></span> ';
    tr += '</td>';
    tr += '<td>';
    tr += '<select disabled class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="updateCmdId" style="display:none;margin-top:5px;" title="Commande d\'information à mettre à jour">';
    tr += '<option style="display:none;" value="">Aucune</option>';
    tr += '</select>';
    tr += '<input type="hidden" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="updateCmdToValue" placeholder="Valeur de l\'information" style="display : none;margin-top : 5px;">';
    tr += '<input type="hidden" class="cmdAttr form-control input-sm" data-l1key="unite"  style="width : 100px;" placeholder="Unité" title="Unité">';
    tr += '<input type="hidden" class="cmdAttr form-control input-sm expertModeVisible" data-l1key="configuration" data-l2key="minValue" placeholder="Min" title="Min"> ';
    tr += '<input type="hidden" class="cmdAttr form-control input-sm expertModeVisible" data-l1key="configuration" data-l2key="maxValue" placeholder="Max" title="Max" style="margin-top : 5px;">';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> Tester</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    var tr = $('#table_cmd tbody tr:last');
    jeedom.eqLogic.builSelectCmd({
        id: $(".li_eqLogic.active").attr('data-eqLogic_id'),
        filter: {type: 'info'},
        error: function (error) {
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success: function (result) {
            tr.find('.cmdAttr[data-l1key=value]').append(result);
            tr.find('.cmdAttr[data-l1key=configuration][data-l2key=updateCmdId]').append(result);
            tr.setValues(_cmd, '.cmdAttr');
            jeedom.cmd.changeType(tr, init(_cmd.subType));
        }
    });
}
