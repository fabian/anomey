{layout template="Admin/Text/layout.tpl" title="Media files"}

{capture assign="actions"}
 {link trail="media/add"}<ul id="actions">
  <li><a href="{$href}" class="action add_file">Upload a file</a></li>
 </ul>{/link}
{/capture}
  
<table>
 <colgroup>
 	<col width="50%" />
 	<col width="20%" />
 	<col width="20%" />
 </colgroup>
 <thead>
  <tr>
   <th>Filename</th>
   <th>Last modified</th>
   <th>Actions</th>
  </tr>
 </thead>
 <tbody>
 {foreach from=$files item=file}
 <tr{cycle values=", class=\"even\""}>
  <td><a href="{media file=$file.name}">{$file.name}</a></td>
  <td>{$file.modified|date_format:"%Y-%m-%d %H:%M"}</td>
  <td>{link trail="media/delete" file=$file.name}<a href="{$href}" class="action delete">delete</a>{/link}</td>
 </tr>
 {foreachelse}
 <tr>
  <td colspan="3">No media files here.</td>
 </tr>
 {/foreach}
 </tbody>
</table>

{/layout}
