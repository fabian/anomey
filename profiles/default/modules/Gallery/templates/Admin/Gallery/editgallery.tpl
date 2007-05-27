{layout template="Admin/Gallery/layout.tpl"}

<h2>Items{if $item.title} of "{$item.title}{/if}"</h2>
<table>
<tr><th>Class</th><th>Source</th><th>Actions</th></tr>
{foreach name=subitems from=$item.children item=subitem}
<tr>
<td>{$subitem.class}</td>
<td>{$subitem.source}</td>
<td>
{link trail="delete" id=$subitem.id}<a href="{$href}">delete</a>{/link}
</td>
</tr>
{/foreach}
</table>

{/layout}
