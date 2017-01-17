<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

global $listCmdJeePlcBus;
include_file('core', 'JeePlcBus', 'config', 'JeePlcBus');
sendVarToJS('eqType', 'JeePlcBus');
$eqLogics = eqLogic::byType('JeePlcBus');

foreach ($eqLogics as $eqLogic) {
	if(!$eqLogic->getConfiguration('device_code')){
		$nouveau = true;
	}	
}


?>

<div class="row row-overflow">
    <div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un Module}}</a>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
foreach ($eqLogics as $eqLogic) {
	echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
}
?>
           </ul>
       </div>
   </div>

   <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
    <legend>{{Gestion}}
    </legend>

    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
         <center>
            <i class="fa fa-plus-circle" style="font-size : 7em;color:#94ca02;"></i>
        </center>
        <span style="font-size : 1.1em;position:relative; top : 23px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#94ca02"><center>{{Ajouter}}</center></span>
	</div>
	</div>
<legend><i class="icon meteo-soleil"></i>  {{Mes Modules}}
</legend>
<div class="eqLogicThumbnailContainer">
    <?php
foreach ($eqLogics as $eqLogic) {
	echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
	echo "<center>";
	echo '<img src="plugins/JeePlcBus/doc/images/JeePlcBus_icon.png" height="105" width="95" />';
	echo "</center>";
	echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
	echo '</div>';
}
?>
</div>
</div>

<div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
<?php if(!$nouveau){ ?>
   <a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
   <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
<?php } ?>
   <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
	<?php if(!$nouveau){?>
	<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li><?php }?>
</ul>

<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
		<form class="form-horizontal">
			<fieldset>
				<legend><i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}<i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i></legend>
				<div class="form-group">
					<label class="col-lg-2 control-label">{{Nom du Module}}</label>
					<div class="col-lg-3">
						<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
						<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom du module}}"/>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label" >{{Objet parent}}</label>
					<div class="col-lg-3">
						<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
							<option value="">{{Aucun}}</option>
							<?php
								foreach (object::all() as $object) {
									echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-2 control-label">{{Catégorie}}</label>
					<div class="col-lg-8">
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
					<label class="col-sm-2 control-label" ></label>
					<div class="col-sm-9">
						<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
						<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
					</div>
				</div>
				<div class="alert alert-info">
					{{Obligatoire pour que cela fonctionne. Exemple : A1 pour le module code maison A et adresse 1}}
				</div>
				<div class="form-group">
					<label class="col-lg-3 control-label">{{Device Code}}</label>
					<div class="col-sm-2">
						<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="device_code" placeholder="{{Device Code}}"/>
					</div>
					<?php if($nouveau){ ?>
							<div class="col-sm-2">
							<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="dimmable" checked/>{{DIMMABLE}}</label>
							</div>
					<?php } ?>
  					<div class="col-sm-2">
						<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="retour_etat" checked/>{{Retour d'état}}</label>
					</div>
				</div>

			</fieldset> 
		</form>
		
		<?php if($nouveau){ ?>
			<a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Suivant}}</a>
		<?php } 
		if(!$nouveau){ ?>
			<a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
		<?php } ?>
			<a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
</div>
<?php 
if(!$nouveau){
?>
<div role="tabpanel" class="tab-pane" id="commandtab">
	<legend>{{Configuration du Module}}</legend>
			<table id="table_cmd" class="table table-bordered table-condensed">
				<thead>
					<tr>
					<th>#</th>
					<th>{{Nom}}</th>
					<th>{{Sous-Type}}</th>
					<th>{{Valeur}}</th>
					<th>{{Paramètres}}</th>
					<th></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
</div>
<?php 
}
?>
</div>
</div>

<?php include_file('desktop', 'JeePlcBus', 'js', 'JeePlcBus');?>
<?php include_file('core', 'plugin.template', 'js');?>