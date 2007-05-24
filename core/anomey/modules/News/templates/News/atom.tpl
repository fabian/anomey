<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">

	<title>{$model.site.title}</title>
	<subtitle>{$model.title}</subtitle>
	{link trail="" url="true"}<link ref="alternate" href="{$href}" />{/link}
	
	<link ref="self" href="{link trail="feed" url="true"}{$href}{/link}" />
	<updated>{$model.lastModified|date}</updated>
	<id>{link trail="feed" url="true"}{$href}{/link}</id>
	<generator version="{$version}">anomey</generator>
	
{foreach from=$model.publications item=publication}
{foreach from=$publication item=entry}
	<entry>
		<id>tag:{$processor.url.host},{$entry.created|date:"Y-m-d"}:{$entry.id}</id>
		<title>{$entry.title}</title>
		<published>{$entry.publication|date}</published>
		<updated>{$entry.publication|date}</updated>
		<author>
			<name>{if $entry.author.fullname}{$entry.author.fullname}{elseif $entry.author.nick}{$entry.author.nick}{else}unknown{/if}</name>
		</author>
		<content type="html">
			{$entry.content|anomey|htmlspecialchars}
		</content>
	</entry>
{/foreach}
{/foreach}
</feed>

