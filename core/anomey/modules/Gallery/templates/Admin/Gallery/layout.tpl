{layout template="Admin/edit.tpl"}

{capture assign="actions"}
<ul id="actions">
  {link trail="addgallery" id=$item.id}<li><a href="{$href}" class="action add_entry">Add new gallery</a></li>{/link}
  {link trail="addimage" id=$item.id}<li><a href="{$href}" class="action add_entry">Add new image</a></li>{/link}
  {link trail="addimport" id=$item.id}<li><a href="{$href}" class="action add_entry">Add new import</a></li>{/link}
 </ul>
{/capture}

{$content}

{/layout}