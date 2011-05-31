<script type="text/javascript">

$(function() {
	// Add button 
	$("button, input:submit, a", "#add_user").button({ 
		icons: {
			primary:'ui-icon-gear'
		}
	});
	
	$("a", "#add_user").click(function() { 
		$("#add_user_dialog").dialog({
			width: 400,
			modal: true,
			autoOpen: true,
			resizable: false,
			buttons: { 
				OK: function() {
					$("#add_user_form").submit();
				},
				Cancel: function() {
					$("#add_user_dialog").dialog("close");
				}
			}
		});
		
		$.get("%{link '/admin/session/user/showadd'}%", function(data) {
			$("#add_user_form").html(data);
		});
		
		return(false);
	});

	// Edit button 
	$("button, input:submit, a", ".edit_user").button({ 
		icons: {
			primary:'ui-icon-scissors'
		}
	});
	
	$("a", ".edit_user").click(function() {
		$("#edit_user_dialog").dialog({
			width: 400,
			modal: true,
			autoOpen: true,
			resizable: false,
			buttons: { 
				OK: function() {
					$("#edit_user_form").submit();
				},
				Cancel: function() {
					$("#edit_user_dialog").dialog("close");
				}
			}
		});
		
		$.get("%{link '/admin/session/user/showedit'}%?uid=" + $(this).attr("id"), function(data) {
			$("#edit_user_form").html(data);
		});
		
		return(false);
	});
	
	// Delete button 
	$("button, input:submit, a", ".delete_user").button({ 
		icons: {
			primary:'ui-icon-close'
		}
	});
	
	$("a", ".delete_user").click(function() {
		$("#delete_user_dialog").dialog({
			width: 400,
			modal: true,
			autoOpen: true,
			resizable: false,
			buttons: { 
				OK: function() {
					$("#delete_user_form").submit();
				},
				Cancel: function() {
					$("#delete_user_dialog").dialog("close");
				}
			}
		});
		$("#delete_user_id").val($(this).attr("id"));
		return(false);
	});
	
	$("#delete_user_dialog").hide();
	
	
	
});
function auto_mdp() {
	check = document.getElementById("generated_password");
	mdp = document.getElementById("mdp_div");
	mdp_conf = document.getElementById("mdp_conf_div");
	if(check.checked) {
		mdp.style.display = "none";
		mdp_conf.style.display = "none";
	}else {
		mdp.style.display = "";
		mdp_conf.style.display = "";
	}
}

</script>

<h1><img src="%{link '/data/session/title_user.png'}%" alt="%{@ 'Gestion de la base de données utilisateur'}%"/>%{@ 'Gestion de la base de données utilisateur'}%</h1>

<!-- User add form -->
<span id="add_user">
<a href="">%{@ 'Ajouter un nouvel utilisateur'}%</a>
</span><br/><br/>
<div id="add_user_dialog" title="%{@ 'Ajouter un nouvel utilisateur'}%">
<form id="add_user_form" class="form_dialog" method="post" action="%{link '/admin/session/user/add'}%">
</form>
</div>



<!-- User edit form -->
<div id="edit_user_dialog" title="%{@ 'Edit user'}%">
<form id="edit_user_form" class="form_dialog" method="post" action="%{link '/admin/session/user/edit'}%">
</form>
</div>

<!-- User delete form -->
<div id="delete_user_dialog" title="%{@ "Delete a user"}%">
<form id="delete_user_form" class="form_dialog" method="post" action="%{link '/admin/session/user/delete'}%">
	<input type="hidden" id="delete_user_id" name="id" value="" />
	%{@ "Voulez-vous vraiment supprimer l'utilisateur ?"}%
</form>
</div>

%{$dataset}%


