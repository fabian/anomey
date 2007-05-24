{layout template="Admin/layout.tpl" middleTitle="Edit design \"`$design.title`\""}

{capture assign="middle"}
<ul id="pageNavigation" class="navigation">
	{link trail="files"}<li><a{if $active} class="active"{/if} href="{$href}">Files</a></li>{/link}
	{link trail="settings"}<li><a{if $active} class="active"{/if} href="{$href}">Settings</a></li>{/link}
</ul>
{/capture}

{$content}

{/layout}
