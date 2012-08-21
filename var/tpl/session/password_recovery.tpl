<div class="content-secondary">
	<div id="jqm-homeheader">
		<h1 id="jqm-logo"><img src="%{link '/data/session/title_recovery.png'}%" alt="%{@ 'Recover password'}%" /></h1>
		<p>Recover your password</p>
	</div>
	
	<p class="intro">
		%{if($last_action == "search")}%
			%{@ "Fine %s %s, type your new password and we will reset it.", $firstname, $lastname}%<br/>
			%{@ "We will send you an email to \"%s\" with your new password, please keep it in your mailbox to remember your password this time !", $email}%<br/>
		%{else}%
			%{@ "Did you lose your password ? Then this page is for you."}%<br/>
			%{@ "You can type your username or your email address to recover your password."}%<br/>
			%{@ "We will send you an email with a link and you will be able to choose another one."}%
		%{/if}%
		<br/><br/>
		%{@ "If you find your password back, "}%<a href="%{link '/session/login'}%">%{@ "please log in here"}%</a>.
	</p>
</div>

<div class="content-primary">
	%{if count($errors) > 0}%
	<ul class="ui-listview" data-role="listview" data-inset="true">
		<li data-role="list-divider">%{@ 'Errors found'}%</li>
		
		%{foreach $errors as $error}%
		<li class="ui-li ui-li-static ui-btn-up-c ui-li-has-count">%{$error}%</li>
		%{/foreach}%
	</ul>
	%{/if}%
	
	<form action="?" method="post">
		%{if($last_action == "search")}%
		<input type="hidden" name="id" value="%{$id}%" />
		<input type="hidden" name="c" value="%{$code}%" />
		<input type="hidden" name="action" value="recover" />
		<ul data-role="listview" data-inset="true">
			
			<li data-role="fieldcontain">
				<label for="rec_password">%{@ 'New password :'}%</label>
				<input type="password" name="rec_password" id="rec_password" data-mini="true" />
			</li>
			
			<li data-role="fieldcontain">
				<label for="rec_password_confirm">%{@ 'Confirm :'}%</label>
				<input type="password" name="rec_password_confirm" id="rec_password_confirm" data-mini="true" />
			</li>
			
			<li data-role="fieldcontain">
				<button type="submit" data-theme="b">%{@ 'Recover'}%</button>
			</li>
		</ul>
		%{else}%
		<input type="hidden" name="action" value="search" />
		<ul data-role="listview" data-inset="true">
			
			<li data-role="fieldcontain">
				<label for="recovery">%{@ 'Username or email :'}%</label>
				<input type="text" name="recovery" id="recovery" data-mini="true" />
			</li>
			
			<li data-role="fieldcontain">
				<button type="submit" data-theme="b">%{@ 'Search'}%</button>
			</li>
		</ul>
		%{/if}%
	</form>
</div>
