{layout template="Admin/layout.tpl"}

{capture assign="middle"}
<ul id="pageNavigation" class="navigation">
	{link trail="/admin/security/permissions"}<li><a{if $active} class="active"{/if} href="{$href}">Permissions</a></li>{/link}
	{link trail="/admin/security/users"}<li><a{if $active} class="active"{/if} href="{$href}">Users</a></li>{/link}
	{link trail="/admin/security/groups"}<li><a{if $active} class="active"{/if} href="{$href}">Groups</a></li>{/link}
</ul>
{/capture}

{$content}

{/layout}