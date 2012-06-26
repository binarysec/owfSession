<center>
	<br/>
	%{if($activated)}%
		Votre compte vient d'être activé !
		<br/>
		<a href="%{link '/'}%">%{@ 'Racite du site'}%</a>
	%{else}%
		Ce compte n'existe pas.
	%{/if}%
	<br/>
</center>