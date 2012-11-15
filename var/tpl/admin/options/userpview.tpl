<center>
%{if $error}%
<p><strong>%{$error}%</strong></p>
%{/if}%

%{if $uid == $me['id']}%
	<p>You are editing your permission views</p>
%{else}%
	<p>You are editing permission views of <strong>%{$user['firstname']|html}% %{$user['name']|html}%</strong></p>
%{/if}%

<form action="?" method="get" data-ajax="false">
	<input type="hidden" name="back" value="%{$back}%" />
	<input type="hidden" name="uid" value="%{$uid}%" />
	<input type="hidden" name="action" value="mod" />
	
	<div data-role="collapsible-set">
	
		%{foreach $results as $name => $pview}%
		<div data-role="collapsible" data-mini="true" data-content-theme="d">
			<h3>%{$name}%</h3>
			<p>
				<ul data-role="listview">
				%{foreach $pview as $pviewname => $pviewtext}%
					<li data-role="fieldcontain" style="margin-bottom: 10px;">
						<label for="%{$name}%%{$pviewname}%" style="width: 65%;">%{$pviewtext}%</label>
						<div style="float: right;">
						<select name="%{$name}%%{$pviewname}%" id="%{$name}%%{$pviewname}%" data-role="slider" data-mini="true">
							<option value="off">Off</option>
							<option value="on">On</option>
						</select>
						</div>
					</li>
				%{/foreach}%
				</ul>
			</p>
		</div>
		%{/foreach}%
	
	</div>
	
	<button type="submit" data-theme="b">Update pviews</button>
	
</form>

</center>