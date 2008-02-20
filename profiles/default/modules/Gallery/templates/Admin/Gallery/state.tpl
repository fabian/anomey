{layout template="Admin/Gallery/layout.tpl" title="State"}

<br/><br/>
<fieldset>
    <legend><span>Tools</span></legend>
	<ul>
		<li>GD Graphics Library: {if $state.tools.gd}<font style="color: #00FF00;">installed!</font>{else}<font style="color: #FF0000;">not installed!</font>{/if}</li>
	</ul>
</fieldset>
<fieldset>
	<legend><span>Formats</span></legend>
	<ul>
		{foreach from=$state.formats key=mime item=value} 
		<li>{$mime} support: {if $value}<font style="color: #00FF00;">installed!</font>{else}<font style="color: #FF0000;">not installed!</font>{/if}</li>
		{/foreach}
	</ul>
</fieldset>

{/layout}
