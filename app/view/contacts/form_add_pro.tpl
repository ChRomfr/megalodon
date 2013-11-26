<div class="form-group">
	<label class="control-label col-sm-2">Raison social :</label>
	<div class="col-sm-5">
		<input type="text" name="contact[ets][raison_social]" id="contact-ets-raisonsocial" required class="form-control" />
	</div>
</div>
	
<div class="form-group">
	<label class="control-label col-sm-2">Siret :</label>
	<div class="col-sm-5">
		<input type="text" name="contact[ets][siret]" id="contact-ets-siret" class="form-control" />
	</div>
</div>

<div class="form-group">
	<label class="control-label col-sm-2">Effectif :</label>
	<div class="col-sm-5">
		<input type="text" name="contact[ets][effectif]" id="contact-ets-effectif" class="form-control">
	</div>
</div>

<div class="form-group">
	<label class="control-label col-sm-2">APE :</label>
	<div class="col-sm-5">
		<select name="contact[ets][ape_id]" id="contact-ets-apeid" class="chozen form-control">
			<option value=""></option>
			{foreach $apes as $row}
			<option value="{$row.id}">{$row.code}</option>
			{/foreach}
		</select>
	</div>
</div>

<div class="form-group">
	<label class="control-label col-sm-2">Siège social :</label>
	<div class="col-sm-5">
		<select name="contact[ets][mother]" id="siege-social" class="form-control">
			<option value="0">Non</option>
			<option value="1">Oui</option>
		</select>
	</div>
</div>

{literal}
<script type="text/javascript">
<!--
$(".chozen").chosen();

jQuery(document).ready(function(){
	// binds form submission and fields to the validation engine
	$("#form-add-contacts").validate({
		rules:{
			"contact[ets][raison_social]":{
				required:true,
			},
		},
		messages:{
			"contact[ets][raison_social]":{
				required:"Veuillez indiquer la raison social",
			},
		},
		highlight:function(element)
        {
            $(element).parents('.control-group').removeClass('success');
            $(element).parents('.control-group').addClass('error');
        },
        unhighlight: function(element)
        {
            $(element).parents('.control-group').removeClass('error');
            $(element).parents('.control-group').addClass('success');
        }
	});
});

//-->
</script>
{/literal}