{if $action instanceof Action}
    {if $action.parent}
        {include file="position.smarty" action=$action.parent sub="true"}
    {/if}
    
    {if $sub}
        <a href="{link trail=$action.trail}">{$action.title}</a> »
    {else}
        {$action.title} 
    {/if}
{/if} 