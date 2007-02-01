{layout template="Admin/Gallery/layout.tpl" title="State"}

<br/><br/>
<fieldset>
    <legend><span>Tools</span></legend>
	<ul>
		<li>GD Graphics Library: {if $state.tools['gd']}<font style="color: #00FF00;">installed!</font>{else}<font style="color: #FF0000;">not installed!</font>{/if}</li>
	</ul>
</fieldset>
<fieldset>
	<legend><span>Formats</span></legend>
	<ul>
		<li>png image support: {if $state.formats['image/png']}<font style="color: #00FF00;">installed!</font>{else}<font style="color: #FF0000;">not installed!</font>{/if}</li>
		<li>jpeg image support: {if $state.formats['image/jpeg']}<font style="color: #00FF00;">installed!</font>{else}<font style="color: #FF0000;">not installed!</font>{/if}</li>
		<li>gif image support: {if $state.formats['image/gif']}<font style="color: #00FF00;">installed!</font>{else}<font style="color: #FF0000;">not installed!</font>{/if}</li>
		<li>svg image support: {if $state.formats['image/svg']}<font style="color: #00FF00;">installed!</font>{else}<font style="color: #FF0000;">not installed!</font>{/if}</li>
		<li>avi movie support: {if $state.formats['movie/avi']}<font style="color: #00FF00;">installed!</font>{else}<font style="color: #FF0000;">not installed!</font>{/if}</li>
	</ul>
</fieldset>

{/layout}
