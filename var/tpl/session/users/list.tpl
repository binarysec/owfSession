{literal}
	<script type="text/javascript">
		function set_form_add_user() {
			document.getElementById('form_add_user').reset();
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

{$scripts}

<button onclick="javascript:
	set_form_add_user();
	YAHOO.dialog_add_user.myDialog.show();">
	<img src="{link '/data/icons/22x22/add.png'}" />
	{@ 'Ajouter un utilisateur'}
</button>

{$dataset}
