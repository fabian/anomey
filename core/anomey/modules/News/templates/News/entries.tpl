{layout template="module.tpl" title=$model.title}
{link trail="feed"}
{capture assign="header"}
<link rel="alternate" type="application/atom+xml" title="{$entry.title} Feed" href="{$href}" />
{/capture}
{/link}
{$model.preface|anomey}
{foreach from=$model.publications item=publication}
{foreach from=$publication item=entry}
<h2>{$entry.title}</h2>
{$entry.content|anomey}
<p class="minor"><em>Published on {$entry.publication|date_format:"%Y-%m-%d"} by {if $entry.author.nick}{$entry.author.nick}{else}unknown{/if}</em></p>
{/foreach}
{/foreach}
{/layout}