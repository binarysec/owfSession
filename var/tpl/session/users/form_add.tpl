<form id="form_add_user" class="form_dialog" method="POST" action="{link '/admin/session/user/add'}">
	<table>
		<tr>
			<td><label for="form_add_user_email">Email <span class="required">(*)</span>&nbsp;:</label></td>
			<td><input type="text" id="form_add_user_email" name="email" value="" /></td>
		</tr>
		<tr>
			<td><label for="form_add_user_password">Mot de passe <span class="required">(*)</span>&nbsp;:</label></td>
			<td><input type="password" id="form_add_user_password" name="password" value="" /></td>
		</tr>
		<tr>
			<td><label for="form_add_user_password">Mot de passe (confirmation) <span class="required">(*)</span>&nbsp;:</label></td>
			<td><input type="password" id="form_add_user_password_confirm" name="password_confirm" value="" /></td>
		</tr>
		<tr>
			<td><label for="form_add_user_name">Nom&nbsp;:</label></td>
			<td><input type="text" id="form_add_user_name" name="name" value="" /></td>
		</tr>
		<tr>
			<td><label for="form_add_user_perms">Permissions&nbsp;:</label></td>
			<td><select name="perm">
				<option value="1" selected="selected">Administrateur</option>
				<option value="2">Utilisateur simple</option>
				<option value="3">Web services</option>
			</select></td>
		</tr>
	</table>
</form>
