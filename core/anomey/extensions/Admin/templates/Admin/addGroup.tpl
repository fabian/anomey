{layout template="Admin/security.tpl" title="Add group"}
  
  {form}
   <fieldset>
    <legend><span>General</span></legend>
                     
    <div class="text">
     <label for="nick" title="Name of the group.">Name <span class="required" title="Required">*</span></label><br />
     <input type="name" name="name" id="name" value="{$form.name}" title="Name of the group." />
    </div>
                     
    <div class="multiple odd last">
     <label for="users" title="Select the users of this group.">Users</label><br />
     <select multiple="multiple" name="users[]" id="users" size="5">
      {html_options options=$form->getAllUsers() selected=$form.users}
     </select>
    </div>
   </fieldset>

   <div>
    {submit value="Add group"}
    {cancel}
   </div>
  {/form}
{/layout}
