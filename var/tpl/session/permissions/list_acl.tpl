{literal}
	<script type="text/javascript">
		function set_form_{/literal}{$id}{literal}(id) {
			var div = document.getElementById('user_edition');
			div.innerHTML = 'Loading user data #' + id;
			
			YAHOO.dialog_edit_user.myDialog.show();
			{/literal}
			YAHOO.async_req_user_edition.send(
				'{link '/admin/session/user/showedit'}' + '?uid=' + id
			);
			{literal}
		}

	</script>
{/literal}


{$ajax}

{$dialog}

salut