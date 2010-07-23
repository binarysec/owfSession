<input type="hidden" id="form_edit_user_id" name="id" value="{$id}" />
<table>
	<tr>
		<td><label for="form_edit_user_email">Email <span class="required">(*)</span>&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_email" name="email" value="{$email|entities}" /></td>
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
		<td><label for="form_edit_user_name">Nom&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_name" name="name" value="{$name|entities}" /></td>
	</tr>	
	<tr>
		<td><label for="form_edit_user_firstname">Prénom&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_firstname" name="firstname" value="{$firstname|entities}" /></td>
	</tr>	
	<tr>
		<td><label for="form_edit_user_phone">Phone&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_phone" name="phone" value="{$phone|entities}" /></td>
	</tr>	
	<tr>
		<td><label for="form_edit_user_company">Entreprise&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_company" name="company" value="{$company|entities}" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_company_decription">Description entreprise&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_company_description" name="company_description" value="{$company_description|entities}" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_company_position">Position dans l'entreprise&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_company_position" name="company_position" value="{$company_position|entities}" /></td>
	</tr>
	<tr>
	<td><label>Adresse de livraison</label></td>
	</tr>	
	<tr>
		<td><label for="form_edit_user_delivery_address_street">Champs d'adresse&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_delivery_address_street" name="delivery_address_street" value="{$delivery_address_street|entities}" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_delivery_address_postcode">Code postal&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_delivery_address_postcode" name="delivery_address_postcode" value="{$delivery_address_postcode|entities}" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_delivery_address_town">Ville&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_delivery_address_town" name="delivery_address_town" value="{$delivery_address_town|entities}" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_delivery_address_country">Pays&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_delivery_address_country" name="delivery_address_country" value="{$delivery_address_country|entities}" /></td>
	</tr>
	<tr>
	<td><label>Adresse de facturation</label></td>
	<td><input type="checkbox" id="form_edit_same_address" name="same_address">Même adresse</input></td>
	</tr>
	<tr>
	<td><label for="form_edit_user_invoice_address_street">Champs d'adresse&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_invoice_address_street" name="invoice_address_street" value="{$invoice_address_street|entities}" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_invoice_address_postcode">Code postal&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_invoice_address_postcode" name="invoice_address_postcode" value="{$invoice_address_postcode|entities}" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_invoice_address_town">Ville&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_invoice_address_town" name="invoice_address_town" value="{$invoice_address_town|entities}" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_invoice_address_country">Pays&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_invoice_address_country" name="invoice_address_country" value="{$invoice_address_country|entities}" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_free_site">Sites gratuits&nbsp;:</label></td>
		<td><input type="text" id="form_edit_user_free_site" name="free_site" value="{$free_site|entities}" /></td>
	</tr>
	<tr>
		<td><label for="form_edit_user_perms">Permissions :</label></td>
		
		{if is_array($perms["session:god"]) || is_array($perms["session:admin"])}
		<td><select name="perm">
			<option value="1" selected="selected">Administrateur</option>
			<option value="2">Utilisateur simple</option>
			<option value="3">Web services</option>
		</select></td>
		{elseif is_array($perms["session:simple"])}
		<td><select name="perm">
			<option value="1">Administrateur</option>
			<option value="2" selected="selected">Utilisateur simple</option>
			<option value="3">Web services</option>
		</select></td>
		{elseif is_array($perms["session:ws"])}
		<td><select name="perm">
			<option value="1">Administrateur</option>
			<option value="2">Utilisateur simple</option>
			<option value="3" selected="selected">Web services</option>
		</select></td>
		{/if}
	</tr>
</table>
