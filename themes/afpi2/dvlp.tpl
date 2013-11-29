{strip}
<div id="dvlpModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="myModalLabel">Developpeur information</h3>
			</div>
			<div class="modal-body">
				<a href="#modal-infodev-config">config</a>
					<div style="pull-left">
						<pre style="font-size:9px;"> 
							{$smarty.session|print_r}
						</pre>
					</div>
					<div class="pull-righ">
						<pre style="font-size:9px;"> 
							{$registry->smarty->tpls_used|print_r}
						</pre>
					</div>
					<div class="clearfix"></div>
				
				Requetes SQL : 
				<pre style="font-size:9px;"> 
					{$registry->db->queries|print_r}
				</pre>
				Serveur
				<pre style="font-size:9px;"> 
					{$smarty.server|print_r}
				</pre>
				POST/GET
				<pre style="font-size:9px;"> 
					{$smarty.request|print_r}
				</pre>
				<hr/>
				<h4 id="modal-infodev-config">Config:</h4>
				<pre style="font-size:9px;">{$registry->config|print_r}</pre>
			</div>
			<div class="modal-footer">
				<button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Close</button>
				<button class="btn btn-primary">Save changes</button>
			</div>
		</div><!--/content -->
	</div><!--/dialog-->
</div><!-- /modal -->
{/strip}