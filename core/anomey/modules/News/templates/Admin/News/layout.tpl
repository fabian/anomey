{layout template="Admin/edit.tpl"}

{capture assign="middle"}
<ul id="pageNavigation" class="navigation">
	{link trail="entries"}<li><a{if $active} class="active"{/if} href="{$href}">Entries</a></li>{/link}
	{link trail="settings"}<li><a{if $active} class="active"{/if} href="{$href}">Settings</a></li>{/link}
</ul>
{/capture}

{$content}
{/layout}
