<ul>
{foreach from=$links item=link key=name}
	{assign var=trail value="$ltrail/$name"}
    {if !$link.hide or $showHidden}
    	{link trail=$trail}<li>
    		{if $trail eq $request.trail}
    		{$link.title}
    		{else}
    		<a href="{$href}">{$link.title}</a>
    		{/if}
			{if $link.links}
			     {include file="menu.tpl" links=$link.links ltrail=$trail}
			{/if}
	    </li>{/link}
    {/if}
{/foreach}
</ul>