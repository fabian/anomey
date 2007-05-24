{layout template="module.tpl" title=$model.title}

{assign var="slimbox" value="true"}
{if count($gallerypath) > 0}
{foreach from=$gallerypath item=gallerypathelement}
{link trail="" id=$gallerypathelement.id}<a href="{$href}">{$gallerypathelement.title}</a>{/link} »
{/foreach}
{$gallery.title}

<h2>{$gallery.title}</h2>
{/if}

{$gallery.date|date_format:"%d. %B %Y"}

{if $gallery.children}
<ul class="box">
{foreach name=items from=$gallery.children item=item}
	
	{if $rows == 0 || ($smarty.foreach.items.iteration > $start && $smarty.foreach.items.iteration - 1 < $end)}
		<li>
			{if $item.class == "Image"}
				<a href="{$item.item}" rel="lightbox[{$gallery.id}]" class="item"><img src="{$item.thumb}" /></a>
			{elseif $item.class == "Gallery"}
				{link trail="" id=$item.id}<a href="{$href}" class="item"><img src="{$item.thumb}" /></a>
				<div class="detail">
					<a href="{$href}" class="title">{$item.title}</a>
					<p class="minor">{$item.childrenSize} Pictures</p>
				</div>
				{/link}
			{/if}
		</li>
		{if $cols != 0 && $smarty.foreach.items.iteration % $cols == 0}
			</ul><ul class="box">
		{/if}
	{else}
		{if $item.class == "Image"}
			<a href="{$item.item}" class="hide" rel="lightbox[{$gallery.id}]" class="item"><img src="{$item.thumb}" /></a>
		{/if}
	{/if}
{foreachelse}
	<li>Nothing found.</li>
{/foreach}
</ul>
{if $page > 0}
	{link trail="" id=$gallery.id page=$prevpage}<a href="{$href}">&lt; preview page</a>{/link}
{/if}
 
{if $smarty.foreach.items.iteration > $end}
	{link trail="" id=$gallery.id page=$nextpage}<a href="{$href}">next page &gt;</a>{/link}
{/if}

{/if}

{/layout}