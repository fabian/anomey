{layout template="module.tpl" title=$model.title}
{$model.preface|anomey}

{foreach from=$model.people item=user}
 <h2>{$user.name}</h2>
 {if $user.title}<p>{$user.title}</p>{/if}
 {if $user.url}<p><a href="{$user.url}">{$user.url}</a></p>{/if}

{/foreach}
{/layout}