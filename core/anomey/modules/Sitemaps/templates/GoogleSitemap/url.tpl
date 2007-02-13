{foreach from=$pages item="page"}
{link trail=$page.path url="true"}
<url>
	<loc>{$href}</loc>
	<lastmod>{$page.modified}</lastmod>
</url>
{/link}
{include file="GoogleSitemap/url.tpl" pages=$page.childs}
{/foreach}