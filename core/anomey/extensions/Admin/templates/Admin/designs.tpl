{layout template="Admin/layout.tpl" title="Designs"}

{capture assign="actions"}
 {link trail="admin/designs/add"}<ul id="actions">
  <li><a href="{$href}" class="action add_design">Add new design</a></li>
 </ul>{/link}
{/capture}
  
<table>
 <colgroup>
 	<col width="40%" />
 	<col width="30%" />
 	<col width="30%" />
 </colgroup>
 <thead>
  <tr>
   <th>Name</th>
   <th>Author</th>
   <th>Actions</th>
  </tr>
 </thead>
 <tbody>
 {foreach from=$designs item=design key=name}
 <tr{cycle values=", class=\"even\""}>
  <td>{$design.title}</td>
  <td>{$design.author}</td>
  <td><ul class="actions"><li>{link trail="admin/designs/edit/`$name`"}<a href="{$href}" class="action edit">edit</a>{/link}</li>
  <li>{link trail="admin/designs/delete" design=$name}<a href="{$href}" class="action delete">delete</a>{/link}</li>
  </ul></td>
 </tr>
 {/foreach}
 </tbody>
</table>

{/layout}
