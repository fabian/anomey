{layout template="Admin/security.tpl" title="Edit permissions of Page \"`$title`\""}
  
  {form}
   {foreach from=$form->getPermissions() item="permission"}
   <fieldset>
    <legend><span>Permission "{$permission}"</span></legend>
                     
    <div class="radio">
     <input type="radio" name="who[{$permission}]" value="everyone"{if $form.who.$permission eq "everyone"} checked="checked"{/if} title="Give everyone this permission." /> <label for="show" title="Give everyone this permission.">Allow everyone</label>
     <input type="radio" name="who[{$permission}]" value="users"{if $form.who.$permission eq "users"} checked="checked"{/if} title="Give this permission only to specified users and/or groups." /> <label for="hide" title="Give this permission only to specified users and/or groups.">Specified users and groups</label>
    </div>
                     
    <div class="text">
    </div>
                     
    <div class="multiple last">
     <label for="users" title="Select the users to which you want to give this permissions.">Users</label><br />
     <select multiple="multiple" name="users[{$permission}][]" size="5">
      {html_options options=$form->getAllUsers() selected=$form.users.$permission}
     </select>
    </div>
                     
    <div class="multiple last">
     <label for="users" title="Select the groups to which you want to give this permissions.">Groups</label><br />
     <select multiple="multiple" name="groups[{$permission}][]" size="5">
      {html_options options=$form->getAllGroups() selected=$form.groups.$permission}
     </select>
    </div>
   </fieldset>
   {/foreach}

   <div>
    <input type="hidden" name="confirmed" id="confirmed" value="{$form.confirmed}" />
    
    {submit value="Save changes"}
    {cancel}
   </div>
  {/form}
{/layout}
