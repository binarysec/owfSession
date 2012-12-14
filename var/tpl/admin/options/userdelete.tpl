<center>
<p>You are deleting user <strong>%{$user['firstname']|html}% %{$user['name']|html}%</strong>.</p>

	<form action="?" method="get" data-ajax="false">
		<input type="hidden" name="back" value="%{$back}%" />
		<input type="hidden" name="uid" value="%{$uid}%" />
		<input type="hidden" name="action" value="mod" />
		<button type="submit" data-theme="b">Delete</button>
	</form>
</center>