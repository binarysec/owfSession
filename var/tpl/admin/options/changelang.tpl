<center>
	%{if $error}%
	<p><strong>%{$error}%</strong></p>
	%{/if}%
	<p>
		Changement de la langue de l'interface
	</p>
</center>
<form action="?" method="get" data-ajax="false">
	<input type="hidden" name="back" value="%{$back}%" />
	<input type="hidden" name="uid" value="%{$uid}%" />
	<input type="hidden" name="action" value="mod" />
	
	<select name="lang" data-native-menu="false">
		%{foreach($langs as $lang)}%
			<option value="%{$lang['code']}%" %{if($lang['code']==$user['lang'])}%selected=selected%{/if}%>%{$lang['name']}%</option>
		%{/foreach}%
	</select>
	
	<button type="submit" data-theme="b">Changer la langue</button>
</form>
