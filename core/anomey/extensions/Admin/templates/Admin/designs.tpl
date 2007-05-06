{layout template="Admin/layout.tpl" title="Designs"}

{capture assign="actions"}
 {link trail="admin/designs/add"}<ul id="actions">
  <li><a href="{$href}" class="action add_design">Add new design</a></li>
 </ul>{/link}
{/capture}
  
{form}
<table>
 <colgroup>
 	<col width="5%" />
 	<col width="43%" />
 	<col width="40%" />
 	<col width="12%" />
 </colgroup>
 <thead>
  <tr>
   <th></th>
   <th>Name</th>
   <th>Author</th>
   <th>Actions</th>
  </tr>
 </thead>
 <tbody>
 {foreach from=$designs item=design key=name}
 <tr{cycle values=", class=\"even\""}>
  <td><input id="design{$design.name}" name="toDelete[]" type="checkbox"
				value="{$design.name}" /></td>
  <td>{$design.title}</td>
  <td>{$design.author}</td>
  <td>{link trail="admin/designs/`$name`"}<a href="{$href}" class="action edit">edit</a>{/link}</td>
 </tr>
 {/foreach}
 </tbody>
</table>
 
 <div>
  {submit value="Delete selected" class="delete"}
  {cancel}
 </div>
{/form}

{/layout}
