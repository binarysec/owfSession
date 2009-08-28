<!-- {css '/data/session/base.css'} -->

{literal}
<script type="text/javascript">
	function session_pview_reset_matrix() {
		document.getElementById('session_pview_matrix').reset();
	}
	
	function session_pview_send_matrix() {
		document.getElementById('session_pview_matrix').submit();
	}
	
	function session_pview_send_user() {
		document.getElementById('session_pview_user').submit();
	}
	
	function set_form_edit_user(id) {
		var div = document.getElementById('user_edition');
		div.innerHTML = 'Loading user data #' + id;
		
		YAHOO.dialog_edit_user.myDialog.show();
		{/literal}
		YAHOO.async_req_user_edition.send(
			'{link '/admin/session/user/showedit'}' + '?uid=' + id
		);
		{literal}
	}

	function set_form_delete_user(id, email) {
		var field_id    = document.getElementById('form_delete_user_id');
		var field_email = document.getElementById('form_delete_user_email');
		
		YAHOO.dialog_delete_user.myDialog.show();
		
		field_id.value        = id;
		field_email.innerHTML = email;
	}
	

</script>
{/literal}

<h1><img src="{link '/data/session/title_perm.png'}"/>{$title}</h1>

<div class="admin_content">

<table border="0">
	<tr>
<!--	<td>
		<button onclick="javascript:
			set_form_add_user();
			YAHOO.dialog_add_user.myDialog.show();">
			<img src="{link '/data/icons/22x22/add.png'}" />
			{@ 'Retour'}
		</button>
	</td>-->
	
	<td style="padding-right: 4px;">
		<a class="btn" href="#" onclick="javascript:session_pview_reset_matrix();">
			{@ 'Annuler les modifications'}
		</a>
	</td>
	
	<td style="padding-right: 4px;">
		<a class="btn one" onclick="javascript:session_pview_send_matrix();">
			{@ 'Sauvegarder les permissions'}
		</a>
	</td>

	<td style="padding-right: 4px;">
		<a class="btn two" onclick="javascript:session_pview_send_user();">
			{@ 'Ajouter l\'utilisateur'}
		</a>
	</td>
	
	<td>
		<form id="session_pview_user" method="POST" action="{link '/session/permissions/user'}">
		<input type="hidden" name="pview" value="{$pview}"/>
		<input type="hidden" name="oid" value="{$oid}"/>
		<input type="text" name="user"/>
		</form>
	</td>
	
	</tr>

</table><br>

<form id="session_pview_matrix" method="POST" action="{link '/session/permissions/matrix'}">
<input type="hidden" name="pview" value="{$pview}"/>
<input type="hidden" name="oid" value="{$oid}"/>
{$dataset}
</form>

</div>
