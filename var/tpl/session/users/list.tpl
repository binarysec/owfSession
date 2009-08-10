{css '/data/yui/build/button/assets/skins/sam/button.css'}
{css '/data/yui/build/container/assets/skins/sam/container.css'}

{js '/data/yui/build/yahoo-dom-event/yahoo-dom-event.js'}
{js '/data/yui/build/connection/connection-min.js'}
{js '/data/yui/build/element/element-min.js'}
{js '/data/yui/build/button/button-min.js'}
{js '/data/yui/build/dragdrop/dragdrop-min.js'}
{js '/data/yui/build/container/container-min.js'}

{literal}
<script type="text/javascript">

	/* ajax request */
	function set_form_add_user() {
		document.getElementById('form_add_user').reset();
	}

	function set_form_edit_user(id) {
		var div = document.getElementById('user_edition');
		
		YAHOO.dialog_edit_user.myDialog.show();
		
		div.innerHTML = 'Loading user data #' + id;
		var handleSuccess = function(o) {
			if(o.responseText !== undefined){
				div.innerHTML = o.responseText;
			}
		}
	
		var handleFailure = function(o) {
			if(o.responseText !== undefined){
				div.innerHTML = "Server error";
			}
		}
		
		var callback = {
			success:handleSuccess,
			failure:handleFailure,
			argument: { foo:"foo", bar:"bar" }
		};
	
		{/literal}
		var request = YAHOO.util.Connect.asyncRequest(
			'GET', 
			'{link '/admin/session/user/showedit'}' + '?uid=' + id, 
			callback
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

	/* dialod add user */
	YAHOO.namespace("dialog_add_user");
	function init_add_user() {
	
		var handleSubmit = function() {
			this.submit();
		};
		var handleCancel = function() {
			this.cancel();
		};

		YAHOO.dialog_add_user.myDialog = new YAHOO.widget.Dialog(
			"add_user", {
				width : "450px",
				fixedcenter : true,
				visible : false,
				constraintoviewport : true,
				buttons : [
					{text:"Valider", handler:handleSubmit, isDefault:true},
					{text:"Annuler", handler:handleCancel}
				],
				postmethod : "form"		}
		);
	
		YAHOO.dialog_add_user.myDialog.validate = function() {
			return true;
		};

		YAHOO.dialog_add_user.myDialog.render();
	}
	
	YAHOO.util.Event.onDOMReady(init_add_user);
	
	/* dialod add user */
	YAHOO.namespace("dialog_edit_user");
	function init_edit_user() {
	
		var handleSubmit = function() {
			this.submit();
		};
		var handleCancel = function() {
			this.cancel();
		};

		YAHOO.dialog_edit_user.myDialog = new YAHOO.widget.Dialog(
			"edit_user", {
				width : "450px",
				fixedcenter : true,
				visible : false,
				constraintoviewport : true,
				buttons : [
					{text:"Valider", handler:handleSubmit, isDefault:true},
					{text:"Annuler", handler:handleCancel}
				],
				postmethod : "form"		}
		);
	
		YAHOO.dialog_edit_user.myDialog.validate = function() {
			return true;
		};

		YAHOO.dialog_edit_user.myDialog.render();
	}
	
	YAHOO.util.Event.onDOMReady(init_edit_user);

	/* dialod delete user */
	YAHOO.namespace("dialog_delete_user");
	function init_delete_user() {
	
		var handleSubmit = function() {
			this.submit();
		};
		var handleCancel = function() {
			this.cancel();
		};

		YAHOO.dialog_delete_user.myDialog = new YAHOO.widget.Dialog(
			"delete_user", {
				width : "450px",
				fixedcenter : true,
				visible : false,
				constraintoviewport : true,
				buttons : [
					{text:"Supprimer", handler:handleSubmit, isDefault:true},
					{text:"Annuler", handler:handleCancel}
				],
				postmethod : "form"		}
		);
	
		YAHOO.dialog_delete_user.myDialog.validate = function() {
			return true;
		};

		YAHOO.dialog_delete_user.myDialog.render();
	}
	
	YAHOO.util.Event.onDOMReady(init_delete_user);
	
	
</script>
{/literal}

<h1>Gestion de la base de données utilisateur</h1>

<a class="btn two" onclick="javascript:
	set_form_add_user();
	YAHOO.dialog_add_user.myDialog.show();">
	{@ 'Ajouter un utilisateur'}
</a><br><br>

<!-- User add form -->
<div id="add_user">
<div class="hd">Ajouter un nouvel utilisateur</div>
<div class="bd">
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

</div>
</div>

<!-- User edit form -->
<div id="edit_user">
	<div class="hd">Edition d'un utilisateur</div>
	<div class="bd">
		<form id="form_edit_user" class="form_dialog" method="POST" action="{link '/admin/session/user/edit'}">
			<div id="user_edition">
			</div>
		</form>
	</div>
</div>

<!-- User delete form -->
<div id="delete_user">
	<div class="hd">Suppression d'un utilisateur</div>
	<div class="bd">
		<form id="form_delete_user" class="form_dialog" method="POST" action="{link '/admin/session/user/delete'}">
			<input type="hidden" id="form_delete_user_id" name="id" value="" />
			Voulez-vous vraiment supprimer l'utilisateur
			<strong><span id="form_delete_user_email">???</span></strong> ?
		</form>
	</div>
</div>

{$dataset}
