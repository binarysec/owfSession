<!DOCTYPE html> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<link rel="stylesheet" type="text/css" href="%{link '/data/admin/css/jqm-docs.css'}%" />
<link rel="stylesheet" type="text/css" href="%{link '/data/css/jquery.mobile.min.css'}%" />
<script type="text/javascript" src="%{link '/data/js/jquery-1.7.js'}%"></script>
<script type="text/javascript" src="%{link '/data/js/jquery.mobile.min.js'}%"></script>
<meta http-equiv="Content-Language" content="fr"/>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>%{@ 'Please login'}%</title>
<style>
	.ui-dialog .ui-header .ui-btn-icon-notext { display:none;} 
	.ui-footer { font-size: 12px; }
	.owf-links { font-size: 12px; text-align: center; }
	.owf-login-field { text-align: right; padding-right: 5%; }
</style>

</head>

<body>
	<div data-role="dialog" data-theme="b"> 
		<div data-role="header">
			<h1>%{@ 'Account activation'}%</h1>
		</div>
		
		<div data-role="content">
			<center>
				%{if($action == "validate")}%
					%{@ "Your account was activated successfully."}%
				%{else}%
					%{@ "We just sent you an email with a validation link."}%
					<br/>
					%{@ "Please take a look at it in order to activate your account."}%
				%{/if}%
			</center>
		</div>

		<!--<div data-role="footer">
			<p><center>
			%{if isset($via_addr)}%
			
			%{else}%
			
			%{/if}%
			</center></p>
		</div>-->
	</div>
</body>
</html>