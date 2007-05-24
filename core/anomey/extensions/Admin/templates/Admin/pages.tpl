{layout template="Admin/layout.tpl" title="Pages"}

{capture assign="actions"}
 {link trail="admin/pages/new"}<ul id="actions">
  <li><a href="{$href}" class="action add_page">Create new page</a></li>
 </ul>{/link}
{/capture}
  
{form}
 <table>
  <colgroup>
   <col width="5%" />
   <col width="25%" />
   <col width="25%" />
   <col width="15%" />
   <col width="8%" />
   <col width="7%" />
   <col width="10%" />
  </colgroup>
  <thead>
   <tr>
    <th></th>
    <th>Title</th>
    <th>Path</th>
    <th>Type</th>
    <th colspan="3">Actions</th>
   </tr>
  </thead>
  <tbody>
  {include file="Admin/page.list.tpl" pages=$pages id=$model.id deep=0}
  </tbody>
 </table>
 
 <div>
  {submit value="Delete selected" class="delete"}
  {cancel}
 </div>
{/form}

{/layout}
