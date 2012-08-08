<style>
/* 	.ui-dialog .ui-header .ui-btn-icon-notext { display:none;}  */
	.ui-dialog .ui-footer { font-size: 12px; }
	.ui-dialog .owf-links { font-size: 12px; text-align: center; }

</style>

<div class="content-secondary">
	<div id="jqm-homeheader">
		<h1 id="jqm-logo"><img src="%{link '/data/session/title_adduser.png'}%" alt="%{@ 'Create user'}%" /></h1>
		<p>%{@ 'Create new user'}%</p>
	</div>

	<p class="intro">%{@ 'In order to create your account you\'ll have to fill the form below. If you are already register please'}% <a href="%{link '/session/login'}%">%{@ 'click here'}%</a></p>


	%{if count($errors) > 0}%
	<ul class="ui-listview" data-role="listview" data-inset="true">
		<li data-role="list-divider">%{@ 'Errors found'}%</li>
	%{/if}%
	%{foreach $errors as $error}%
		<li class="ui-li ui-li-static ui-btn-up-c ui-li-has-count">%{$error}%</li>
	%{/foreach}%
	%{if count($errors) > 0}%</ul>%{/if}%

</div>

<div class="content-primary">
	<form action="?" method="post">
		<input type="hidden" name="action" value="mod" />
		<ul data-role="listview" data-inset="true">
			%{if $allow_user_register}%
			<li data-role="fieldcontain">
				<label for="username">%{@ 'Username :'}%</label>
				<input type="text" name="username" id="username" value="%{$username|html}%" placeholder="%{@ 'Username'}%" data-mini="true"/>
			</li>
			%{/if}%
			
			<li data-role="fieldcontain">
				<label for="firstname">%{@ 'Your first name :'}%</label>
				<input type="text" name="firstname" id="firstname" value="%{$firstname|html}%" placeholder="%{@ 'Firstname'}%" data-mini="true"/>
			</li>
			
			<li data-role="fieldcontain">
				<label for="name">%{@ 'Your last name :'}%</label>
				<input type="text" name="name" id="name" value="%{$name|html}%"  placeholder="%{@ 'Name'}%" data-mini="true"/>
			</li>
			
			<li data-role="fieldcontain">
				<label for="email">%{@ 'Your Mail address :'}%</label>
				<input type="email" name="email" id="email" value="%{$email|html}%" placeholder="%{@ 'Mail address'}%" data-mini="true"/>
			</li>
			
			<li data-role="fieldcontain">
				<label for="email_confirm">%{@ 'Repeat your mail address :'}%</label>
				<input type="email" name="email_confirm" id="email_confirm" value="%{$email_confirm|html}%"  placeholder="%{@ 'Mail address confirmation'}%" data-mini="true"/>
			</li>
			
			%{if $allow_pass_register}%
			<li data-role="fieldcontain">			
				<label for="pass">%{@ 'Password :'}%</label>
				<input type="password" name="pass" id="pass" placeholder="%{@ 'Password'}%" data-mini="true"/>
			</li>
			
			<li data-role="fieldcontain">
				<label for="pass_confirm">%{@ 'Password repeated:'}%</label>
				<input type="password" name="pass_confirm" id="pass_confirm" placeholder="%{@ 'Password repeated'}%" data-mini="true"/>
			</li>
			%{/if}%
			
			<li data-role="fieldcontain">
				<button type="submit" data-theme="b">%{@ 'Create my account'}%</button>
			</li>
		</ul>
	</form>
</div>
