{layout template="Admin/layout.tpl" title="Pages"}

{capture assign="actions"}
 {link trail="admin/pages/new"}<ul id="actions">
  <li><a href="{$href}" class="action add_page">Create new page</a></li>
 </ul>{/link}
{/capture}
  
  
<table>
 <colgroup>
 	<col width="25%" />
 	<col width="25%" />
 	<col width="15%" />
 	<col width="8%" />
 	<col width="10%" />
 	<col width="7%" />
 	<col width="10%" />
 </colgroup>
 <thead>
  <tr>
   <th>Title</th>
   <th>Path</th>
   <th>Type</th>
   <th colspan="4">Actions</th>
  </tr>
 </thead>
 <tbody>
 {include file="Admin/page.list.tpl" pages=$pages id=$model.id deep=0}
 </tbody>
</table>

{/layout}
