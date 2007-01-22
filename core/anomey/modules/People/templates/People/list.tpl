{layout template="module.tpl" title=$model.title}
{$model.preface|anomey}

<table>
 <thead>
  <tr>
   <th>Username</th>
   <th>Lastname</th>
   <th>Prename</th>
   <th>Title</th>
   <th>URL</th>
  </tr>
 </thead>
 <tbody>
 {foreach from=$model.people item=user}
 <tr{cycle values=", class=\"even\""}>
  <td>{$user.nick}</td>
  <td>{$user.lastname|default:"-"}</td>
  <td>{$user.prename|default:"-"}</td>
  <td>{$user.title|default:"-"}</td>
  <td>{if $user.url}<a href="{$user.url}">{$user.url}</a>{else}-{/if}</td>
 </tr>
 {/foreach}
 </tbody>
</table>
{/layout}