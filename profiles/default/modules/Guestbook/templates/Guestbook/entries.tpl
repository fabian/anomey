{layout template="module.tpl" title=$model.title}
{link trail="add"}<a href="{$href}">Add new entry</a>{/link}
{foreach from=$entries item=entry}
<h2>{$entry.name}</h2>
<p>{$entry.comment}</p>
{/foreach}
{/layout}