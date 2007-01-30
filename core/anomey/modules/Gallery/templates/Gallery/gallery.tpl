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
{foreachelse}
	<li>Nothing found.</li>
{/foreach}
</ul>

{/if}

{/layout}