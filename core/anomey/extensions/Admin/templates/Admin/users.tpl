{layout template="Admin/security.tpl" title="Users"} {capture
assign="actions"} {link trail="admin/security/users/add"}
<ul id="actions">
	<li><a href="{$href}" class="action add_user">Add new user</a></li>
</ul>
{/link} {/capture} {form}
<table>
	<colgroup>
		<col width="5%" />
		<col width="20%" />
		<col width="28%" />
		<col width="35%" />
		<col width="12%" />
	</colgroup>
	<thead>
		<tr>
			<th></th>
			<th>Username</th>
			<th>Fullname</th>
			<th>E-Mail</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$users item=user}
		<tr {cycle values=", class=\"even\""}>
			<td><input id="user{$user.id}" name="toDelete[]" type="checkbox"
				value="{$user.id}" /></td>
			<td>{$user.nick}</td>
			<td>{$user.fullname}</td>
			<td>{$user.mail}</td>
			<td>{link trail="admin/security/users/edit/`$user.id`"}<a
				href="{$href}" class="action edit">edit</a>{/link}</td>
		</tr>
		{/foreach}
	</tbody>
</table>

<div>{submit value="Delete selected" class="delete"} {cancel}</div>
{/form} {/layout}
