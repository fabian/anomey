{layout template="Admin/edit.tpl"}

{capture assign="middle"}
<ul id="pageNavigation" class="navigation">
	{link trail="content"}<li><a{if $active} class="active"{/if} href="{$href}">Content</a></li>{/link}
	{link trail="media"}<li><a{if $active} class="active"{/if} href="{$href}">Media files</a></li>{/link}
	{link trail="settings"}<li><a{if $active} class="active"{/if} href="{$href}">Settings</a></li>{/link}
</ul>
{/capture}

{$content}
{/layout}
