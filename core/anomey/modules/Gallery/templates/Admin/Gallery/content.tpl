{layout template="Admin/Gallery/layout.tpl"}

{capture assign="actions"}
<ul id="actions">
  {link trail="addgallery" id=$item.id}<li><a href="{$href}" class="action add_entry">Add new gallery</a></li>{/link}
  {link trail="addimage" id=$item.id}<li><a href="{$href}" class="action add_entry">Add new image</a></li>{/link}
  {link trail="addimport" id=$item.id}<li><a href="{$href}" class="action add_entry">Add new import</a></li>{/link}
 </ul>
{/capture}


<h2>Galleries</h2>
<table>
<tr><th>Title</th><th>Class</th><th>Actions</th></tr>
{foreach name=gallery from=$galleries item=gallery}
{if $gallery.id}
<tr>
<td>
{$gallery.deep}
{$gallery.title}

</td>
<td>{$gallery.class}</td>
<td>
{link trail="delete" id=$gallery.id}<a href="{$href}">delete</a>{/link}




{if $imports[$gallery.id]}
{link trail="import" id=$gallery.id}<a href="{$href}">import</a>{/link}
{else}
{link trail="edit" id=$gallery.id}<a href="{$href}">edit</a>{/link}
{/if}

</td>
</tr>
{/if}
{/foreach}
</table>

{/layout}
