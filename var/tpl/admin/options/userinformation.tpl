<center>
%{if $error}%
<p><strong>%{$error}%</strong></p>
%{/if}%
<p>You are editing user informations of <strong>%{$user['firstname']|html}% %{$user['name']|html}%</strong></p>
</center>
<form action="?" method="get" data-ajax="false">
	<input type="hidden" name="back" value="%{$back}%" />
	<input type="hidden" name="uid" value="%{$uid}%" />
	<input type="hidden" name="action" value="mod" />

	<label for="firstname">%{@ 'Firstname :'}%</label>
	<input type="text" name="firstname" id="firstname" value="%{$user["firstname"]|html}%" placeholder="%{@ 'Firstname'}%" data-mini="true"/>
	
	<label for="name">%{@ 'Name :'}%</label>
	<input type="text" name="name" id="name" value="%{$user["name"]|html}%" placeholder="%{@ 'Name'}%" data-mini="true"/>

	<label for="email">%{@ 'Mail address :'}%</label>
	<input type="text" name="email" id="email" value="%{$user["email"]|html}%" placeholder="%{@ 'Mail address'}%" data-mini="true"/>
	
	%{if $admin}%
	%{if isset($perms["session:god"])}%
	<label for="perm">%{@ 'Permissions :'}%</label>
	<select name="perm" data-mini="true">
		<option value="0" selected="selected">Super administrateur</option>
		<option value="1">Administrateur</option>
		<option value="2">Utilisateur simple</option>
		<option value="3">Web services</option>
	</select>
	%{elseif isset($perms["session:admin"])}%
	<label for="perm">%{@ 'Permissions :'}%</label>
	<select name="perm" data-mini="true">
		<option value="0">Super administrateur</option>
		<option value="1" selected="selected">Administrateur</option>
		<option value="2">Utilisateur simple</option>
		<option value="3">Web services</option>
	</select>
	%{elseif isset($perms["session:simple"])}%
	<label for="perm">%{@ 'Permissions :'}%</label>
	<select name="perm" data-mini="true">
		<option value="0">Super administrateur</option>
		<option value="1">Administrateur</option>
		<option value="2" selected="selected">Utilisateur simple</option>
		<option value="3">Web services</option>
	</select>
	%{elseif isset($perms["session:ws"])}%
	<label for="perm">%{@ 'Permissions :'}%</label>
	<select name="perm" data-mini="true">
		<option value="0">Super administrateur</option>
		<option value="1">Administrateur</option>
		<option value="2">Utilisateur simple</option>
		<option value="3" selected="selected">Web services</option>
	</select>
	%{/if}%
	%{/if}%
	
	<button type="submit" data-theme="b">Update information</button>
</form>
