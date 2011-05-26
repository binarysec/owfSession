<input type="hidden" id="form_edit_user_id" name="id" value="%{$id}%" />
<table>
	<tr>
		<td><label for="form_edit_user_name">Username<span class="required">(*)</span>&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_username" name="username" value="%{$username|entities}%" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_name">Nom&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_name" name="name" value="%{$name|entities}%" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_firstname">Prénom&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_firstname" name="firstname" value="%{$firstname|entities}%" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_email">Email <span class="required">(*)</span>&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_email" name="email" value="%{$email|entities}%" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_password">Mot de passe automatique&nbsp;:</label></td>
		<td><input type="checkbox" id="generated_password" name="generated_password" checked="checked" onChange="javascript:auto_mdp();"/></td>
	</tr>
	<tr id="mdp_div" style="display:none;">
		<td><label for="form_edit_user_password">Mot de passe&nbsp;:</label></td>
		<td><input type="password" id="form_edit_user_password" name="password" value="" /></td>
	</tr>
	<tr id="mdp_conf_div" style="display:none;">
		<td><label for="form_edit_user_password">Mot de passe (confirmation)&nbsp;:</label></td>
		<td><input type="password" id="form_edit_user_password_confirm" name="password_confirm" value="" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_phone">Tél&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_phone" name="phone" value="%{$phone|entities}%" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_perms">Permissions :</label></td>
		<td>
			<select name="perm">
				<option value="1" selected="selected">Administrateur</option>
				<option value="2">Utilisateur simple</option>
				<option value="3">Web services</option>
			</select>
		</td>
	</tr>
</table>
