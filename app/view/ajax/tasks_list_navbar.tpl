{strip}
<div id="nav-tasks-list">
	<h4 class="text-center">Mes taches</h4>
	{if !empty($mytasks)}	
		<ul class="list-group" id="nav-task-list">
		{foreach $mytasks as $row}
			<li class="list-group-item">
				<small>
					<input type="checkbox" value="{$row.id}" onclick="task_process({$row.id})"/>
					{if !empty($row.link)}<a href="{$row.link}" title="">{/if}<span id="task-id-{$row.id}">{$row.task}{if !empty($task.link)}{/if}</span></a>
				</small>
			</li>
		{/foreach}
		</ul>
	{/if}
	<div class="text-center"><a href="{$Helper->getLink("tasks/mytasks")}" title="Voir toutes">Voir toutes</a></div>
</div>
{/strip}