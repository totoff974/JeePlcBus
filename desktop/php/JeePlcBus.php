<?php
if (!isConnect('admin')) {
	throw new Exception('Error 401 Unauthorized');
}
$plugin = plugin::byId('JeePlcBus');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
function sortByOption($a, $b) {
	return strcmp($a['name'], $b['name']);
}
?>

<div class="row row-overflow">
  <div class="col-lg-2 col-md-3 col-sm-4">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter}}</a>
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="Rechercher" style="width: 100%"/></li>
        <?php
foreach ($eqLogics as $eqLogic) {
	$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
	echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '" style="' . $opacity . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
}
?>
     </ul>
   </div>
 </div>
 <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
   <legend><i class="fa fa-cog"></i>  {{Gestion}}</legend>
   <div class="eqLogicThumbnailContainer">
    <div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 140px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
     <center>
      <i class="fa fa-plus-circle" style="font-size : 6em;color:#94ca02;"></i>
    </center>
    <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#94ca02"><center>Ajouter</center></span>
  </div>
 
 <div class="cursor eqLogicAction" data-action="gotoPluginConf" style="background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;">
  <center>
    <i class="fa fa-wrench" style="font-size : 6em;color:#767676;"></i>
  </center>
  <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>{{Configuration}}</center></span>
</div>
<div class="cursor" id="bt_healthplcbus" style="background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
  <center>
    <i class="fa fa-medkit" style="font-size : 6em;color:#767676;"></i>
  </center>
  <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>{{Santé}}</center></span>
</div>
</div>
<legend><i class="fa fa-table"></i>  {{Mes équipements PlcBus}}</legend>
<div class="eqLogicThumbnailContainer">
  <?php
foreach ($eqLogics as $eqLogic) {
	$device_id = substr($eqLogic->getConfiguration('device'), 0, strpos($eqLogic->getConfiguration('device'), ':'));
	$id_full = str_replace('::', '_', $eqLogic->getConfiguration('device'));
	$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
	echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
	echo "<center>";
	$alternateImg = $eqLogic->getConfiguration('iconModel');
	if (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $alternateImg . '.jpg')) {
		echo '<img class="lazy" src="plugins/JeePlcBus/core/config/devices/' . $alternateImg . '.jpg" height="105" width="95" />';
	} elseif (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $eqLogic->getConfiguration('device') . '.jpg')) {
		echo '<img class="lazy" src="plugins/JeePlcBus/core/config/devices/' . $eqLogic->getConfiguration('device') . '.jpg" height="105" width="95" />';
	} else {
		echo '<img src="' . $plugin->getPathImgIcon() . '" height="105" width="95" />';
	}
	echo "</center>";
	echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
	echo '</div>';
}
?>
</div>
</div>

<div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
 <a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
 <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
 <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a>
 <a class="btn btn-default eqLogicAction pull-right" data-action="copy"><i class="fa fa-files-o"></i> {{Dupliquer}}</a>
 <ul class="nav nav-tabs" role="tablist">
  <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
  <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
  <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
</ul>
<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
  <div role="tabpanel" class="tab-pane active" id="eqlogictab">
    <br/>
    <div class="row">
      <div class="col-sm-6">
        <form class="form-horizontal">
          <fieldset>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
              <div class="col-sm-4">
                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="Nom de l'équipement PlcBus"/>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{ID}}</label>
              <div class="col-sm-4">
                <input type="text" class="eqLogicAttr form-control" data-l1key="logicalId" placeholder="Par exemple A1"/>
              </div>
            </div>		
            <div class="form-group">
              <label class="col-sm-3 control-label"></label>
              <div class="col-sm-9">
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Objet parent}}</label>
              <div class="col-sm-4">
                <select class="eqLogicAttr form-control" data-l1key="object_id">
                  <option value="">Aucun</option>
                  <?php
foreach (object::all() as $object) {
	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
}
?>
               </select>
             </div>
           </div>
           <div class="form-group">
            <label class="col-sm-3 control-label">{{Catégorie}}</label>
            <div class="col-sm-9">
              <?php
foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
	echo '<label class="checkbox-inline">';
	echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
	echo '</label>';
}
?>
           </div>
         </div>
		 <div class="form-group">
		   <label class="col-sm-3 control-label">{{ACK (accuser réception de la commande)}}</label>
		   <div class="col-sm-9">
		 	<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ack" checked/>{{Activer}}</label>
		   </div>
		</div>
      </fieldset>
    </form>
  </div>
  <div class="col-sm-6">
    <form class="form-horizontal">
      <fieldset>
        <div class="form-group">
          <label class="col-sm-3 control-label">{{Equipement}}</label>
          <div class="col-sm-6">
            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="device">
              <option value="">Aucun</option>
              <?php
$actuators = array();
$fctspeciales = array();

foreach (JeePlcBus::devicesParameters() as $packettype => $info) {
	if (isset($info['actuator']) && $info['actuator'] == 1) {
		$actuators[$packettype] = $info;
	} else {
		$fctspeciales[$packettype] = $info;
	}
}
uasort($fctspeciales, 'sortByOption');
uasort($actuators, 'sortByOption');
echo '<optgroup label="{{Fonctions Spéciales}}">';
foreach ($fctspeciales as $packettype => $info) {
	$instruction = '';
	if (isset($info['instruction'])) {
		$instruction = $info['instruction'];
	}
	foreach ($info['subtype'] as $subtype => $subInfo) {
		echo '<option value="' . $packettype . '::' . $subtype . '" data-instruction="' . $instruction . '">' . $info['name'] . ' - ' . $subInfo['name'] . '</option>';
	}
}
echo '</optgroup>';
echo '<optgroup label="{{Actionneur}}">';
foreach ($actuators as $packettype => $info) {
	$instruction = '';
	if (isset($info['instruction'])) {
		$instruction = $info['instruction'];
	}
	foreach ($info['subtype'] as $subtype => $subInfo) {
		echo '<option value="' . $packettype . '::' . $subtype . '" data-instruction="' . $instruction . '">' . $info['name'] . ' - ' . $subInfo['name'] . '</option>';
	}
}
echo '</optgroup>';
?>
      </select>
    </div>
  </div>
  <div class="form-group modelList" style="display:none;">
    <label class="col-sm-3 control-label">{{Modèle}}</label>
    <div class="col-sm-6">
     <select class="eqLogicAttr form-control listModel" data-l1key="configuration" data-l2key="iconModel">
     </select>
   </div>
 </div>
 <div id="div_instruction"></div>
 <div class="form-group expertModeVisible">
  <label class="col-sm-3 control-label">{{Création}}</label>
  <div class="col-sm-3">
    <span class="eqLogicAttr label label-default" data-l1key="configuration" data-l2key="createtime" title="{{Date de création de l'équipement}}" style="font-size : 1em;cursor : default;"></span>
  </div>
  <label class="col-sm-3 control-label">{{Communication}}</label>
  <div class="col-sm-3">
    <span class="eqLogicAttr label label-default" data-l1key="status" data-l2key="lastCommunication" title="{{Date de dernière communication}}" style="font-size : 1em;cursor : default;"></span>
  </div>
</div>
<div class="form-group expertModeVisible">
 <label class="col-sm-3 control-label">{{Statut}}</label>
 <div class="col-sm-3">
  <span class="eqLogicAttr label label-default" style="font-size : 1em;cursor : default;" data-l1key="status" data-l2key="state"></span>
</div>
</div>

<center>
  <img src="core/img/no_image.gif" data-original=".jpg" id="img_device" class="img-responsive" style="max-height : 250px;"  onerror="this.src='plugins/JeePlcBus/doc/images/JeePlcBus_icon.png'"/>
</center>
</fieldset>
</form>
</div>
</div>


</div>
<div role="tabpanel" class="tab-pane" id="commandtab">

<a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Ajouter une commande}}</a><br/><br/>
  <table id="table_cmd" class="table table-bordered table-condensed">
    <thead>
      <tr>
        <th style="width: 300px;">{{Nom}}</th>
        <th style="width: 130px;" class="expertModeVisible">{{Type}}</th>
        <th class="expertModeVisible">{{Logical ID (info) ou Commande brute (action)}}</th>
        <th >{{Paramètres}}</th>
        <th style="width: 100px;">{{Options}}</th>
        <th></th>
      </tr>
    </thead>
    <tbody>

    </tbody>
  </table>

</div>
</div>

</div>
</div>

<?php include_file('desktop', 'JeePlcBus', 'js', 'JeePlcBus');?>
<?php include_file('core', 'plugin.template', 'js');?>
