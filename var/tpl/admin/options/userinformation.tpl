<center>
%{if $error}%
<p><strong>%{$error}%</strong></p>
%{/if}%

%{if $uid == $me['id']}%
	<p>%{@ 'You are editing your user informations'}%</p>
%{else}%
	<p>%{@ 'You are editing user informations of'}% <strong>%{$user['firstname']|html}% %{$user['name']|html}%</strong></p>
%{/if}%

</center>
<form action="?" method="get" data-ajax="false">
	<input type="hidden" name="back" value="%{$back}%" />
	<input type="hidden" name="uid" value="%{$uid}%" />
	<input type="hidden" name="action" value="mod" />

	<label for="firstname">%{@ 'Firstname :'}%</label>
	<input type="text" name="firstname" id="firstname" value="%{$user["firstname"]|html}%" placeholder="%{@ 'Firstname :'}%" data-mini="true"/>
	
	<label for="name">%{@ 'Name :'}%</label>
	<input type="text" name="name" id="name" value="%{$user["name"]|html}%" placeholder="%{@ 'Name :'}%" data-mini="true"/>

	<label for="email">%{@ 'Mail address :'}%</label>
	<input type="text" name="email" id="email" value="%{$user["email"]|html}%" placeholder="%{@ 'Mail address :'}%" data-mini="true"/>
	
	%{if $admin}%
		<label for="perm">%{@ 'Permissions :'}%</label>
		<select name="perm" data-mini="true">
		%{if isset($perms["session:god"])}%
			%{if($god)}%<option value="%{const SESSION_USER_GOD}%" selected="selected">%{@ 'Super administrateur'}%</option>%{/if}%
			<option value="%{const SESSION_USER_ADMIN}%">%{@ 'Administrateur'}%</option>
			<option value="%{const SESSION_USER_SIMPLE}%">%{@ 'Utilisateur simple'}%</option>
			<option value="%{const SESSION_USER_WS}%">%{@ 'Web services'}%</option>
		%{elseif isset($perms["session:admin"])}%
			%{if($god)}%<option value="%{const SESSION_USER_GOD}%">%{@ 'Super administrateur'}%</option>%{/if}%
			<option value="%{const SESSION_USER_ADMIN}%" selected="selected">%{@ 'Administrateur'}%</option>
			<option value="%{const SESSION_USER_SIMPLE}%">%{@ 'Utilisateur simple'}%</option>
			<option value="%{const SESSION_USER_WS}%">%{@ 'Web services'}%</option>
		%{elseif isset($perms["session:simple"])}%
			%{if($god)}%<option value="%{const SESSION_USER_GOD}%">%{@ 'Super administrateur'}%</option>%{/if}%
			<option value="%{const SESSION_USER_ADMIN}%">%{@ 'Administrateur'}%</option>
			<option value="%{const SESSION_USER_SIMPLE}%" selected="selected">%{@ 'Utilisateur simple'}%</option>
			<option value="%{const SESSION_USER_WS}%">%{@ 'Web services'}%</option>
		%{elseif isset($perms["session:ws"])}%
			%{if($god)}%<option value="%{const SESSION_USER_GOD}%">%{@ 'Super administrateur'}%</option>%{/if}%
			<option value="%{const SESSION_USER_ADMIN}%">%{@ 'Administrateur'}%</option>
			<option value="%{const SESSION_USER_SIMPLE}%">%{@ 'Utilisateur simple'}%</option>
			<option value="%{const SESSION_USER_WS}%" selected="selected">%{@ 'Web services'}%</option>
		%{/if}%
		</select>
	%{/if}%
	
	<button type="submit" data-theme="b">%{@ 'Update informations'}%</button>
</form>
