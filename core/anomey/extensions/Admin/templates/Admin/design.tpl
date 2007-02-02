{layout template="Admin/layout.tpl" title="Designs"}

{capture assign="middle"}
<ul id="pageNavigation" class="navigation">
	{link trail=""}<li><a{if $active} class="active"{/if} href="{$href}">Files</a></li>{/link}
	{link trail="settings"}<li><a{if $active} class="active"{/if} href="{$href}">Settings</a></li>{/link}
</ul>
{/capture}

{capture assign="actions"}
 {link trail="admin/designs/add"}<ul id="actions">
  <li><a href="{$href}" class="action add_file">Create file</a></li>
  <li><a href="{$href}" class="action copy">Copy file</a></li>
  <li><a href="{$href}" class="action add_file">Upload file</a></li>
 </ul>{/link}
{/capture}
  
<table>
 <colgroup>
 	<col width="70%" />
 	<col width="30%" />
 </colgroup>
 <thead>
  <tr>
   <th>File</th>
   <th>Actions</th>
  </tr>
 </thead>
 <tbody>
 {foreach from=$files item=file key=name}
 <tr{cycle values=", class=\"even\""}>
  <td>{$file}</td>
  <td><ul class="actions"><li>{link trail="edit/`$name`"}<a href="{$href}" class="action edit">edit</a>{/link}</li>
  <li>{link trail="admin/designs/delete" design=$name}<a href="{$href}" class="action delete">delete</a>{/link}</li>
  </ul></td>
 </tr>
 {/foreach}
 </tbody>
</table>

{/layout}
