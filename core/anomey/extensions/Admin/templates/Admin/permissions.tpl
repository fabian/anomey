{layout template="Admin/security.tpl" title="Permissions" subLinks=$link.links}
  
<table>
 <colgroup>
 	<col width="30%" />
 	<col width="40%" />
 	<col width="30%" />
 </colgroup>
 <thead>
  <tr>
   <th>Title</th>
   <th>Path / Allowed users</th>
   <th>Actions</th>
  </tr>
 </thead>
 <tbody>
 <tr class="even">
  <td>Site</td>
  <td>/</td>
  <td><ul class="actions"><li>{link trail="admin/security/permissions/change/`$model.id`"}<a href="{$href}" class="action edit">change allowed users</a>{/link}</li></ul></td>
 </tr>
 {foreach from=$model.allPermissions item=permission}
 <tr>
  <td title="{$model.availablePermissions[$permission.name]}">&nbsp;&nbsp;&nbsp;{$permission.name}</td>
  <td>{strip}&nbsp;&nbsp;&nbsp;{if $permission.everyone}everyone{elseif count($permission.groups) > 0 or count($permission.users) > 0}
   {foreach from=$permission.users item=user name="userpermissions"}{$user.nick}{if not $smarty.foreach.userpermissions.last}, {/if}{/foreach}{if count($permission.groups) > 0 and  count($permission.users) > 0}, {/if}
   {foreach from=$permission.groups item=group name="grouppermissions"}{$group.name}{if not $smarty.foreach.grouppermissions.last}, {/if}{/foreach}
   {else}nobody{/if}{/strip}</td>
   <td></td>
 </tr>
 {/foreach}
 {include file="Admin/permissions.list.tpl" pages=$pages id=$model.id deep=0}
 </tbody>
</table>

{/layout}
