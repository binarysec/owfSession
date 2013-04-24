<div class="content-secondary">
	<div id="jqm-homeheader">
		<h1 id="jqm-logo"><img src="%{link '/data/admin/images/title_session.png'}%" alt="%{@ 'OWF System session'}%" /></h1>
		<p>%{@ 'Gestion de la base de données utilisateur'}%</p>
	</div>

	<p class="intro">
		%{@ 'C\'est ici que vous pouvez gérer les utilisateurs <strong>OWF</strong> de votre application.'}%
	</p>
	
	<a href="%{link '/session/create'}%" data-role="button" data-transition="slidedown">%{@ 'Add user'}%</a>
</div>

<div class="content-primary">
	%{$dataset}%
</div>

