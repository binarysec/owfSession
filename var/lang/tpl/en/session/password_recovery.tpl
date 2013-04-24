<div class="content-secondary">
	<div id="jqm-homeheader">
		<h1 id="jqm-logo"><img src="%{link '/data/session/title_recovery.png'}%" alt="%{@ 'Recover password'}%" /></h1>
		<p>Recover your password</p>
	</div>
	
	<p class="intro">
		%{@ "Did you lose your password ? Then this page is for you."}%<br/>
		%{@ "You can type your username or your email address to recover your password."}%<br/>
		%{@ "We will send you an email with a link to a page where you will be able to choose another one."}%
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
		<ul data-role="listview" data-inset="true">
			
			<li data-role="fieldcontain">
				<label for="recovery">%{@ 'Username or email :'}%</label>
				<input type="text" name="recovery" id="recovery" data-mini="true" />
			</li>
			
			<li data-role="fieldcontain">
				<button type="submit" data-theme="b">%{@ 'Search'}%</button>
			</li>
		</ul>
	</form>
</div>
