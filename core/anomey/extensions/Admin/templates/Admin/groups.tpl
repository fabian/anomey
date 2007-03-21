{layout template="Admin/security.tpl" title="Groups"
subLinks=$link.links} {capture assign="actions"} {link
trail="admin/security/groups/add"}
<ul id="actions">
	<li><a href="{$href}" class="action add_group">Add new group</a></li>
</ul>
{/link} {/capture} {form}
<table>
	<colgroup>
		<col width="5%" />
		<col width="25%" />
		<col width="58%" />
		<col width="12%" />
	</colgroup>
	<thead>
		<tr>
			<th></th>
			<th>Name</th>
			<th>Users</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$groups item=group}
		<tr {cycle values=", class=\"even\""}>
			<td><input id="group{$group.id}" name="toDelete[]" type="checkbox"
				value="{$group.id}" /></td>
			<td>{$group.name}</td>
			<td>{foreach name="users" from=$group.users item=user}{$user.nick}{if
			not $smarty.foreach.users.last}, {/if}{/foreach}</td>
			<td>{link trail="admin/security/groups/edit/`$group.id`"}<a
				href="{$href}" class="action edit">edit</a>{/link}</td>
		</tr>
		{/foreach}
	</tbody>
</table>

<div>{submit value="Delete selected" class="delete"} {cancel}</div>
{/form} {/layout}
