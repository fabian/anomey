{layout template="Admin/security.tpl" title="Groups" subLinks=$link.links}

{capture assign="actions"}
 {link trail="admin/security/groups/add"}<ul id="actions">
  <li><a href="{$href}" class="action add_group">Add new group</a></li>
 </ul>{/link}
{/capture}
  
  
<table>
 <colgroup>
 	<col width="25%" />
 	<col width="50%" />
 	<col width="25%" />
 </colgroup>
 <thead>
  <tr>
   <th>Name</th>
   <th>Users</th>
   <th>Actions</th>
  </tr>
 </thead>
 <tbody>
 {foreach from=$groups item=group}
 <tr{cycle values=", class=\"even\""}>
  <td>{$group.name}</td>
  <td>{foreach name="users" from=$group.users item=user}{$user.nick}{if not $smarty.foreach.users.last}, {/if}{/foreach}</td>
  <td><ul class="actions"><li>{link trail="admin/security/groups/edit/`$group.id`"}<a href="{$href}" class="action edit">edit</a>{/link}</li>
  <li>{link trail="admin/security/groups/delete" group=$group.id}<a href="{$href}" class="action delete">delete</a>{/link}</li>
  </ul></td>
 </tr>
 {/foreach}
 </tbody>
</table>

{/layout}
