{strip}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil"><i class="fa fa-home"></i>&nbsp;&nbsp;Accueil</a></li>
	<li><a href="{$Helper->getLink("mailing")}" title="Mailing"><i class="fa fa-envelope"></i>&nbsp;&nbsp;Mailing</a></li>	
	<li class="active">
		{if isset($smarty.session.utilisateur.mailing_adm) && $smarty.session.utilisateur.mailing_adm == 1}
			Nouveau mailing
		{else}
			Demande
		{/if}
	</li>
</ol>

<div class="well">
	<form method="post" action="#" class="form-horizontal" id="form-mailing-request">
		<fieldset>
			
			<legend>{if isset($smarty.session.utilisateur.mailing_adm) && $smarty.session.utilisateur.mailing_adm == 1}Nouveau{else}Demande{/if}</legend>
			
			{* START LIBELLE *}
			<div class="form-group">
				<label class="col-sm-2 control-label">Nom du mailing :</label>
				<div class="col-sm-5">
					<input type="text" name="mailing[libelle]" id="libelle" required class="form-control" />
				</div>			
			</div>
			{* END LIBELLE *}

			{* START DESCRIPTION *}
			<div class="form-group">
				<label class="col-sm-2 control-label">Description :</label>
				<div class="col-sm-5">
					<textarea name="mailing[description]" id="description" rows="6" class="form-control" required></textarea>
				</div>
			</div>
			{* END DESCRIPTION *}

			{* START DATE_WISH *}
			<div class="form-group">
				<label class="col-sm-2 control-label">Date d'envoie souhaitée :</label>
				<div class="col-sm-5">
					<input type="text" name="mailing[date_wish]" id="date_wish" class="form-control"/>
				</div>
			</div>
			{* END DATE_WISH *}
			
			{if $smarty.session.utilisateur.mailing_adm > 0}
			<div class="form-group">
				<label class="col-sm-2 control-label">Demandeur :</label>
				<div class="col-sm-5">
					<input type="text" name="mailing[caller]" id="caller" class="form-control"/>
				</div>			
			</div>
			{/if}

			<div class="form-group">
				<label class="col-sm-2 control-label">Type:</label>
				<div class="col-sm-5">
					<select name="mailing[type_id]" class="form-control">
						<option value="0"></option>
						{foreach $types as $type}
						<option value="{$type.id}">{$type.libelle}</option>
						{/foreach}
					</select>
				</div>
			</div>
			
		</fieldset>
		<fieldset>
			<legend>Cible du mailing</legend>
			{* START CRITERES RECHERCHE *}
					
			<div class="form-group">
				<label class="col-sm-2 control-label">Departement:</label>
				<div class="col-sm-5">
					<select name="mailing[cible][departement][]" class="form-control chzn-select" multiple>
						<option value=""></option>
						{foreach $global_departements as $k => $v}
						<option value="{$v.dpt}" {if isset($smarty.get.filtre.departement) && $smarty.get.filtre.departement == $v}selected="selected"{/if}>{$v.dpt}</option>
						{/foreach}
					</select>
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-2 control-label">Categorie :</label>
				<div class="col-sm-5">
					<select name="mailing[cible][categorie][]" class="form-control chzn-select" multiple>
						{foreach $global_categories as $row}
						<option value="{$row.id}"
							{if isset($smarty.get.filtre.categorie)}
								{foreach $smarty.get.filtre.categorie as $k => $v}
									{if $row.id == $v}
										selected="selected"
									{/if}
								{/foreach}
							{/if}
							>{$row.libelle}
						</option>
						{/foreach}
					</select>
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-2 control-label">Condition categorie :</label>
				<div class="col-sm-2">
					<select name="mailing[cible][categorie_condition]" class="input-mini form-control">
						<option value="OR">Ou</option>
						<option value="AND">Et</option>
					</select>
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-2 control-label">Effectif : </label>
				<div class="col-sm-5">
					<div class="col-xs-2">
						<input type="text" name="mailing[cible][effectif_mini]" class="form-control"  /> 
					</div>
					<div class="col-xs-1">
						à
					</div>
					<div class="col-xs-2">
						<input type="text" name="mailing[cible][effectif_max]" class="form-control" />
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-2 control-label">Poste :</label>
				<div class="col-sm-5">		
					<select name="mailing[cible][poste]" class="form-control">
						<option value=""></option>
						{foreach $global_postes as $row}
						<option value="{$row.id}">{$row.libelle}</option>
						{/foreach}
					</select>
				</div>
			</div>
			
			<div class="form-group">
				<label class="col-sm-2 control-label">Service :</label>	
				<div class="col-sm-5">				
					<select name="mailing[cible][service]" class="form-control">
						<option value=""></option>
						{foreach $global_services as $row}
						<option value="{$row.id}">{$row.libelle}</option>
						{/foreach}
					</select>
				</div> 
			</div>

			<div class="form-group">
				<label class="col-sm-2 control-label">APE :</label>
				<div class="col-sm-5">
					<select name="mailing[cible][ape][]" class="form-control chzn-select" multiple>
						{foreach $global_ape as $row}
						<option value="{$row.id}">{$row.code}</option>
						{/foreach}
					</select>
				</div>
			</div>
			
			<div id="champs-contacts"></div>
			
			<div class="form-group">
				<label class="col-sm-2 control-label">Client :</label>
				<div class="col-sm-5">
					<input type="checkbox" name="mailing[cible][is_client]" value="1"/>
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-2 control-label">Societe :</label>
				<div class="col-sm-5">
					<input type="checkbox" name="mailing[cible][ctype][]" value="societes" />
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-2 control-label">Contact societe :</label>
				<div class="col-sm-5">
					<input type="checkbox" name="mailing[cible][ctype][]" value="societe_contact" />
				</div>
			</div>
			
			<div class="form-group">
				<label class="col-sm-2 control-label">Particulier :</label>
				<div class="col-sm-5">
					<input type="checkbox" name="mailing[cible][ctype][]" value="particulier" />
				</div>
			</div>

			{* END CRITERES RECHERCHE *}

			{if isset($smarty.session.acl.mailing_adm) || $smarty.session.utilisateur.isAdmin > 0}
			<input type="hidden" name="mailing[cible][email_is_valid]" value="1" />
			{else}
			<input type="hidden" name="mailing[valid]" value="0" />
			<input type="hidden" name="mailing[cible][email_is_valid]" value="1" />
			{/if}

		</fieldset>
		
		<div class="text-center">
			<hr/>
			<button type="submit" class="btn btn-primary">Enregistrer</button>
			&nbsp;&nbsp;
			<a href="{$Helper->getLink("mailing")}" title="" class="btn btn-danger">Annuler</a>
		</div>
	</form>
</div>{* /well *}
{/strip}
<script type="text/javascript">
<!--
$(document).ready(function() {
	$('#date_wish').mask("99/99/9999");
	{if isset($smarty.session.acl.mailing_adm) || $smarty.session.utilisateur.isAdmin > 0}
	{literal}
	$( "#date_wish" ).datepicker({ dateFormat: 'dd/mm/yy', changeMonth:true, changeYear:true, showButtonPanel: true });
	{/literal}
	{else}
	{literal}
	$( "#date_wish" ).datepicker({ dateFormat: 'dd/mm/yy', changeMonth:true, changeYear:true, showButtonPanel: true, minDate:+1, maxDate:'+1M +10D' });
	{/literal}
	{/if}
});

function champformcontact(){
	if($('#cible-cible').val() == 2 || $('#cible-cible').val() == 3){
		$('#champs-contacts').html('<i class="icon-spinner icon-spin icon-large"></i>');
		
		$.get(		
			'{$Helper->getLink("mailing/getchampscontactsajax")}',{literal}
			{nohtml:'nohtml'},{/literal}
			function(data){
				$('#champs-contacts').html(data);
			}
		);
	}else{
		$('#champs-contacts').html('');
	}
}

jQuery(document).ready(function(){
    $('#form-mailing-request').validate({
        rules:{
            "mailing[date_wish]": {
                required:true,
                remote:'{$Helper->getLink("mailing/checkdatewish/'+ $('#date_wish').val() +'?nohtml")}'
            },
            "mailing[cible][ctype][]":{
				required:true,
				minlength:1,
			}
        },
		messages:{
			"mailing[date_wish]":{
				required: "Veuillez indiquer la date d envoie souhaitée",
				remote: "Le nombre maximum de mailing programmé ce jour est atteint"
			},
		},
		highlight:function(element)
        {
            $(element).parents('.form-group').removeClass('text-success');
            $(element).parents('.form-group').addClass('text-danger');
        },
        unhighlight: function(element)
        {
            $(element).parents('.form-group').removeClass('text-danger');
            $(element).parents('.form-group').addClass('text-success');
        }
    });
});
//-->
</script>