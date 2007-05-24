{foreach from=$pages item=page}
<tr class="even">
	<td>Page {$page.title}</td>
	<td>{$page.path}</td>
	<td><ul class="actions"><li>{link trail="admin/security/permissions/change/`$page.id`"}<a href="{$href}" class="action edit">change allowed users</a>{/link}</li></ul></td>
</tr>
{foreach from=$page.allPermissions item=permission}
<tr>
	<td title="{$page.availablePermissions[$permission.name]}">&nbsp;&nbsp;&nbsp;{$permission.name}</td>
	<td>{strip}&nbsp;&nbsp;&nbsp;{if $permission.everyone}everyone{elseif count($permission.groups) > 0 or count($permission.users) > 0}
	{foreach from=$permission.users item=user name="userpermissions"}{$user.nick}{if not $smarty.foreach.userpermissions.last}, {/if}{/foreach}{if count($permission.groups) > 0 and  count($permission.users) > 0}, {/if}
	{foreach from=$permission.groups item=group name="grouppermissions"}{$group.name}{if not $smarty.foreach.grouppermissions.last}, {/if}{/foreach}
	{else}nobody{/if}{/strip}</td>
	<td></td>
</tr>
{/foreach}
{include file="Admin/permissions.list.tpl" pages=$page.childs}
{/foreach}