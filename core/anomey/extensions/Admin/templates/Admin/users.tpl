{layout template="Admin/security.tpl" title="Users"}

{capture assign="actions"}
 {link trail="admin/security/users/add"}<ul id="actions">
  <li><a href="{$href}" class="action add_user">Add new user</a></li>
 </ul>{/link}
{/capture}
  
<table>
 <colgroup>
 	<col width="25%" />
 	<col width="25%" />
 	<col width="25%" />
 	<col width="25%" />
 </colgroup>
 <thead>
  <tr>
   <th>Username</th>
   <th>Fullname</th>
   <th>E-Mail</th>
   <th>Actions</th>
  </tr>
 </thead>
 <tbody>
 {foreach from=$users item=user}
 <tr{cycle values=", class=\"even\""}>
  <td>{$user.nick}</td>
  <td>{$user.fullname}</td>
  <td>{$user.mail}</td>
  <td><ul class="actions"><li>{link trail="admin/security/users/edit/`$user.id`"}<a href="{$href}" class="action edit">edit</a>{/link}</li>
  <li>{link trail="admin/security/users/delete" user=$user.id}<a href="{$href}" class="action delete">delete</a>{/link}</li>
  </ul></td>
 </tr>
 {/foreach}
 </tbody>
</table>

{/layout}
