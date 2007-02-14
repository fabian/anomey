{layout template="Admin/News/layout.tpl" title="Entries"}

{capture assign="actions"}
 {link trail="entries/add"}<ul id="actions">
  <li><a href="{$href}" class="action add_entry">Write new entry</a></li>
 </ul>{/link}
{/capture}

{form}
 <table>
  <colgroup>
   <col width="5%" />
   <col width="18%" />
   <col width="45%" />
   <col width="20%" />
   <col width="12%" />
  </colgroup>
  <thead>
   <tr>
    <th></th>
    <th>Publication date</th>
    <th>Title</th>
    <th>Author</th>
    <th>Actions</th>
   </tr>
  </thead>
  <tbody>
  {foreach from=$publications item=publication}
  {foreach from=$publication item=entry}
  <tr{cycle values=", class=\"even\""}>
   <td><input id="entry{$entry.id}" name="toDelete[]" type="checkbox" value="{$entry.id}" /></td>
   <td>{$entry.publication|date_format:"%Y-%m-%d %H:%M"}</td>
   <td>{$entry.title}</td>
   <td>{if $entry.author}{$entry.author.nick}{else}-{/if}</td>
   <td>{link trail="entries/edit/`$entry.id`"}<a href="{$href}" class="action edit">edit</a>{/link}</td>
  </tr>
  {/foreach}
  {/foreach}
  </tbody>
 </table>
 
 <div>
  {submit value="Delete selected" class="delete"}
  {cancel}
 </div>
{/form}

{/layout}
