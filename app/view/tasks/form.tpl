<form method="post" role="form" action="{$Helper->getLink("tasks/add")}" class="form-horizontal">
	<div class="form-group">
		<label class="col-sm-3 control-label">Tâche :</label>
		<div class="col-sm-8">
			<textarea name="task[tache]" id="task-tache" row="5" required class="form-control"></textarea>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">Echéance :</label>
		<div class="col-sm-3">
			<input type="text" name="task[date_echance]" id="task-date-echance" class="form-control" data-date-format="YYYY-MM-DD"/> 
		</div>
	</div>
	<div class="text-center">
		<input type="hidden" name="task[third_type]" value="{$controller}" />
		<input type="hidden" name="task[third_id]" value="{$controller_id}" />
		<button type="submit" class="btn btn-primary">Enregistrer</button>
	</div>
</form>
{literal}
<script type="text/javascript">
$(document).ready(function() {
	$('#task-date-echance').mask("9999-99-99");
	$('#task-date-echance').datetimepicker({pickTime: false,language:'fr'});
});
</script>
{/literal}