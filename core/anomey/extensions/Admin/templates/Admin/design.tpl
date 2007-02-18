{layout template="Admin/designLayout.tpl" title="Files"}

{capture assign="middle"}
<ul id="pageNavigation" class="navigation">
	{link trail="files"}<li><a{if $active} class="active"{/if} href="{$href}">Files</a></li>{/link}
	{link trail="settings"}<li><a{if $active} class="active"{/if} href="{$href}">Settings</a></li>{/link}
</ul>
{/capture}

{capture assign="actions"}
 {link trail="admin/designs/add"}<ul id="actions">
  <li><a href="{$href}" class="action add_file">Create file</a></li>
  <li><a href="{$href}" class="action copy">Copy file</a></li>
  <li><a href="{$href}" class="action upload">Upload file</a></li>
 </ul>{/link}
{/capture}

{form}
<table>
 <colgroup>
 	<col width="5%" />
 	<col width="50%" />
 	<col width="33%" />
 	<col width="12%" />
 </colgroup>
 <thead>
  <tr>
   <th></th>
   <th>File</th>
   <th>Last modified</th>
   <th>Actions</th>
  </tr>
 </thead>
 <tbody>
 {foreach from=$files item=file}
 <tr{cycle values=", class=\"even\""}>
  <td><input id="page{$page.id}" name="toDelete[]" type="checkbox" value="{$page.id}" /></td>
  <td>{$file.path}</td>
  <td>{$file.modified|date_format:"%Y-%m-%d %H:%M"}</td>
  <td>{link trail="files/`$file.encoded`"}<a href="{$href}" class="action edit">edit</a>{/link}</td>
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
