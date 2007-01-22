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
	<li class="item">
		{if $item.class == "Image"}
			<a href="{$item.item}" rel="lightbox[{$gallery.id}]"><img src="{$item.thumb}" /></a>
		{elseif $item.class == "Gallery"}
			{link trail="" id=$item.id}<a href="{$href}"><img src="{$item.thumb}" /> {$item.title}</a>{/link}
		{/if}
	</li>
{/foreach}
</ul>

<p>If you don't see any pictures go to the admin-panel and import the photos of the flickr and picasa testgalleries.</p>
{/if}

{/layout}