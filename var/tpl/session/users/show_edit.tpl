<input type="hidden" id="form_edit_user_id" name="id" value="%{$id}%" />
<table>
	<tr>
		<td>Username</td>
		<td>%{$username|entities}%</td>
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
		<td><label for="form_edit_user_password">Mot de passe&nbsp;:</label></td>
		<td><input type="password" id="form_edit_user_password" name="password" value="" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_password">Mot de passe (confirmation)&nbsp;:</label></td>
		<td><input type="password" id="form_edit_user_password_confirm" name="password_confirm" value="" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_phone">Tél&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_phone" name="phone" value="%{$phone|entities}%" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_perms">Permissions :</label></td>
		
		%{if is_array($perms["session:god"]) || is_array($perms["session:admin"])}%
		<td><select name="perm">
			<option value="1" selected="selected">Administrateur</option>
			<option value="2">Utilisateur simple</option>
			<option value="3">Web services</option>
		</select></td>
		%{elseif is_array($perms["session:simple"])}%
		<td><select name="perm">
			<option value="1">Administrateur</option>
			<option value="2" selected="selected">Utilisateur simple</option>
			<option value="3">Web services</option>
		</select></td>
		%{elseif is_array($perms["session:ws"])}%
		<td><select name="perm">
			<option value="1">Administrateur</option>
			<option value="2">Utilisateur simple</option>
			<option value="3" selected="selected">Web services</option>
		</select></td>
		%{/if}%
	</tr>
</table>
