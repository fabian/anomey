{layout template="Admin/security.tpl" title="Edit permission"}
  
  {form}
   <fieldset>
    <legend><span>Allowed users</span></legend>
                     
    <div class="radio">
     <input type="radio" name="who" id="everyone" value="everyone"{if $form.who eq "everyone"} checked="checked"{/if} title="Give everyone this permission." /> <label for="show" title="Give everyone this permission.">Allow everyone</label>
     <input type="radio" name="who" id="users" value="users"{if $form.who eq "users"} checked="checked"{/if} title="Give this permission only to specified users and/or groups." /> <label for="hide" title="Give this permission only to specified users and/or groups.">Specified users and groups</label>
    </div>
                     
    <div class="text">
    </div>
                     
    <div class="multiple last">
     <label for="users" title="Select the users to which you want to give this permissions.">Users</label><br />
     <select multiple="multiple" name="users[]" id="users" size="5">
      {html_options options=$form->getAllUsers() selected=$form.users}
     </select>
    </div>
                     
    <div class="multiple last">
     <label for="users" title="Select the groups to which you want to give this permissions.">Groups</label><br />
     <select multiple="multiple" name="groups[]" id="groups" size="5">
      {html_options options=$form->getAllGroups() selected=$form.groups}
     </select>
    </div>
   </fieldset>

   <div>
    {submit value="Save changes"}
    {cancel}
   </div>
  {/form}
{/layout}
