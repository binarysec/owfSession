%{css '/data/yui/build/button/assets/skins/sam/button.css'}%
%{css '/data/yui/build/container/assets/skins/sam/container.css'}%

%{js '/data/yui/build/yahoo-dom-event/yahoo-dom-event.js'}%
%{js '/data/yui/build/connection/connection-min.js'}%
%{js '/data/yui/build/element/element-min.js'}%
%{js '/data/yui/build/button/button-min.js'}%
%{js '/data/yui/build/dragdrop/dragdrop-min.js'}%
%{js '/data/yui/build/container/container-min.js'}%

%{literal}%
<script type="text/javascript">
</script>
%{/literal}%

<h1><img src="%{link '/data/session/title_user.png'}%" alt="%{@ 'Mon profil'}%"/>%{@ 'Mon profil'}%</h1>
<div class="admin_content">
	<form id="profil_modif" method="post" action="%{link '/admin/myprofile/edit'}%">
		<input type="hidden" id="uid" name="uid" value="%{$user['id']}%"/>
		<table class="dataset_data_table">
			<tr%{alt ' class="alt"'}%>
				<td>%{@ 'Username' }%&nbsp;:&nbsp;</td>
				<td>%{$user["username"]}%</td>
			</tr>
			<tr%{alt ' class="alt"'}%>
				<td>%{@ 'Nom' }%&nbsp;:&nbsp;</td>
				<td><input id="user_name_modif" name="user_name_modif" value="%{$user["name"]}%"/></td>
			</tr>
			<tr%{alt ' class="alt"'}%>
				<td>%{@ 'Prénom' }%&nbsp;:&nbsp;</td>
				<td><input id="user_firstname_modif" name="user_firstname_modif" value="%{$user["firstname"]}%"/></td>
			</tr>
			<tr%{alt ' class="alt"'}%>
				<td>%{@ 'Mail' }%&nbsp;:&nbsp;</td>
				<td><input id="email_modif" name="email_modif" value="%{$user["email"]}%"/></td>
			</tr>
			<tr%{alt ' class="alt"'}%>
				<td>%{@ 'Tel' }%&nbsp;:&nbsp;</td>
				<td><input id="phone_modif" name="phone_modif" value="%{$user["phone"]}%"/></td>
			</tr>
		</table>
			<a class="btn one" onclick="document.getElementById('profil_modif').submit()">%{@ 'Sauvegarder'}%</a>
	</form>
</div>
<div style="clear:both;"/>
