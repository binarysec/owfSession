<div class="content-secondary">
	<div id="jqm-homeheader">
		<h1 id="jqm-logo"><img src="%{link '/data/session/title_adduser.png'}%" alt="%{@ 'Create user'}%" /></h1>
		<p>
		%{if($registering)}%
			%{@ 'Register'}%
		%{else}%
			%{@ 'Create new user'}%
		%{/if}%
		</p>
	</div>

	<p class="intro">
	%{if($registering)}%
		%{@ 'In order to create your account you\'ll have to fill the form below. If you are already register please'}% <a href="%{link '/session/login'}%">%{@ 'login'}%</a>
	%{else}%
		%{@ 'In order to create an account you\'ll have to fill the form below.'}%
	%{/if}%
	</p>


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
					<label for="username">%{@ 'User name'}%</label>
					<input type="text" name="username" id="username" value="%{$username|html}%" placeholder="%{@ 'User name'}%" data-mini="true"/>
			</li>
			%{/if}%

			<li data-role="fieldcontain">
					<label for="firstname">%{@ 'First name'}%</label>
					<input type="text" name="firstname" id="firstname" value="%{$firstname|html}%" placeholder="%{@ 'First name'}%" data-mini="true"/>
			</li>

			<li data-role="fieldcontain">
					<label for="name">%{@ 'Last name'}%</label>
					<input type="text" name="name" id="name" value="%{$name|html}%"  placeholder="%{@ 'Last name'}%" data-mini="true"/>
			</li>

			<li data-role="fieldcontain">
					<label for="email">%{@ 'Email'}%</label>
					<input type="email" name="email" id="email" value="%{$email|html}%" placeholder="%{@ 'Email'}%" data-mini="true"/>
			</li>

			<li data-role="fieldcontain">
					<label for="email_confirm">%{@ 'Confirm email'}%</label>
					<input type="email" name="email_confirm" id="email_confirm" value="%{$email_confirm|html}%"  placeholder="%{@ 'Confirm email'}%" data-mini="true"/>
			</li>

			%{if $allow_pass_register}%
			<li data-role="fieldcontain">
					<label for="pass">%{@ 'Password'}%</label>
					<input type="password" name="pass" id="pass" placeholder="%{@ 'Password'}%" data-mini="true"/>
			</li>

			<li data-role="fieldcontain">
					<label for="pass_confirm">%{@ 'Confirm password'}%</label>
					<input type="password" name="pass_confirm" id="pass_confirm" placeholder="%{@ 'Confirm password'}%" data-mini="true"/>
			</li>
			%{/if}%

			%{if(!$registering)}%
			<li data-role="fieldcontain">
				<label for="auto_validate">%{@ 'Validated ?'}%</label>
				<input type="checkbox" name="auto_validate" id="auto_validate" data-mini="true" %{if($validated == "on")}%checked="checked"%{/if}% />
			</li>
			%{/if}%
			
			<li data-role="fieldcontain">
				<button type="submit" data-theme="b">%{@ 'Create account'}%</button>
			</li>
		</ul>
	</form>
</div>
