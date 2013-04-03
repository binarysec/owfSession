<script type='text/javascript'>
	$(function() {
		$(".owf-session-pview-checkbox").click(function() {
			var	data = $(this).attr("id").split("-"),
				uid = data[data.length - 2],
				name = data[data.length - 1],
				checked = $(this).is(':checked');
			
			$.post(
				"%{link '/admin/options/session/userpview'}%",
				{	action: "mod",
					pview: "%{$pview}%",
					oid: "%{$oid}%",
					uid: uid,
					pvname: name,
					pvvalue: checked,
				},
				function(data, textStatus, jqXHR) {
					if(textStatus == 'success') {
						var message = data ?
							'Success updating "'+data["name"]+'" to "'+data["value"]+'".' :
							'An error occured while updating permission';
						%{msg message}%
					}
					else if(textStatus == 'error')
						%{msg "An error occured while updating permission", true}%
					else if(textStatus == 'timeout')
						%{msg "Connection timed out", true}%
				},
				"json"
			);
			return false;
		});
	});
	
</script>

<div class="content-secondary">
	<div id="jqm-homeheader">
		<h1 id="jqm-logo"><img src="%{link '/data/session/title_pview.png'}%" alt="%{@ 'OWF pviews'}%" /></h1>
		<p>%{@ 'Gestion des permissions'}%</p>
	</div>

	<p class="intro">
		%{@ "Cette section vous permet de paramétrer des permissions avancées."}%
	</p>
	
	<form action="%{link '/admin/options/session/userpview'}%">
		<input type="hidden" name="action" value="add" />
		<input type="hidden" name="back" value="%{$back|entities}%" />
		<input type="hidden" name="pview" value="%{$pview}%" />
		<input type="hidden" name="oid" value="%{$oid}%" />
		<ul data-role="listview" data-inset="true" data-mine="true">
			<li>
				%{@ "Nom de la permission"}%&nbsp;:&nbsp;<i>%{$pview}%</i>
			</li>
			<li>
				%{@ "Identifiant de l'objet"}%&nbsp;:&nbsp;<i>%{$oid}%</i>
			</li>
			<li data-role="fieldcontain">
				<label for="owf-session-pview-adduser">%{@ "Ajouter un utilisateur"}%</label>
				<input id="owf-session-pview-adduser" type="text" name="user" />
			</li>
			<li data-role="fieldcontain">
				<input data-role="button" data-theme="b" type="submit" value='%{@ "Ajouter"}%' />
			</li>
			%{if(count($errors))}%
				%{foreach($errors as $err)}%
					<li style="background-color: #FF9999;">%{@ "Erreur : %s",$err}%</li>
				%{/foreach}%
			%{/if}%
		</ul>
	</form>
</div>

<div class="content-primary">
	
	%{if(count($results))}%
	
	<div data-role="collapsible-set">
		%{foreach $results as $pviewset}%
		<div data-role="collapsible" data-theme="b">
			<h3>
				%{$pviewset["user"]["firstname"]|entities}% %{$pviewset["user"]["name"]|entities}% (%{$pviewset["user"]["username"]|entities}%) -
				<small>%{$pviewset["create_time"]}%</small>
			</h3>
			<ul data-role="listview">
				<li data-role="fieldcontain" data-mini="true" data-icon="false">
					<a href="#owf-session-pview-delete" data-rel="popup" data-position-to="window" data-role="button" data-inline="true" data-transition="pop" data-theme="f" style="width: 100%;">%{@ "Supprimer"}%</a>'
					<div data-role="popup" id="owf-session-pview-delete" data-theme="f" class="ui-corner-all">
						<a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">%{@ "Close"}%</a>
						<div data-role="header" data-theme="a" class="ui-corner-top"><h1>%{@ "Delete this object ?"}%</h1></div>
						<div data-role="content" data-theme="b" class="ui-corner-bottom ui-content">
							<h3 class="ui-title">%{@ "Are you sure you want to delete this object ?"}%</h3>
							<p>%{@ "This action cannot be undone."}%</p>
							<a href="#" data-role="button" data-inline="true" data-rel="back" style="width: 40%;">%{@ "Cancel"}%</a>
							<a href="%{link '/admin/options/session/userpview'}%?action=del&back=%{$back|entities}%&pview=%{$pview}%&uid=%{$pviewset['user']['id']}%&oid=%{$oid}%" data-theme="f" data-role="button" data-inline="true" data-transition="flow" style="width: 40%;">%{@ "Delete"}%</a>
						</div>
					</div>
				</li>
				%{foreach $pviewset["perm"] as $name => $checked}%
				<li data-role="fieldcontain" data-mini="true">
					<fieldset data-role='controlgroup'>
						<label for='owf-session-pview-%{$pviewset["user"]["id"]}%-%{$name}%'>%{$name}%</label>
						<input id='owf-session-pview-%{$pviewset["user"]["id"]}%-%{$name}%' class="owf-session-pview-checkbox" type='checkbox' name='%{$name}%' data-mini="true" %{if($checked == "on")}%checked='checked' %{/if}%/>
					</fieldset>
				</li>
				%{/foreach}%
			</ul>
		</div>
		%{/foreach}%
	</div>
	
	%{else}%
	<p style="text-align:center;">
		%{@ "Il n'y a aucun utilisateur associé à cette permission."}%
	</p>
	%{/if}%
	
</div>
