{assign var="id" value="page`$id`"}
{foreach from=$pages item=page name=$id}
<tr{cycle values=", class=\"even\""}>
	<td><input id="page{$page.id}" name="toDelete[]" type="checkbox" value="{$page.id}" /></td>
	<td>{repeat count=$deep}&nbsp;&nbsp;&nbsp;{/repeat}{$page.title}</td>
	<td>{$page.path}</td>
	<td>{$page.type}</td>
	<td>{link trail="admin/pages/`$page.id`"}<a href="{$href}" class="action edit">edit</a>{/link}</td>
	<td>{if not $smarty.foreach.$id.first}{link trail="admin/pages/up" page=$page.id}<a href="{$href}" class="action move_up">up</a>{/link}{/if}</td>
	<td>{if not $smarty.foreach.$id.last}{link trail="admin/pages/down" page=$page.id}<a href="{$href}" class="action move_down">down</a>{/link}{/if}</td>
</tr>
{include file="Admin/page.list.tpl" pages=$page.childs id=$page.id deep=$deep+1}
{/foreach}