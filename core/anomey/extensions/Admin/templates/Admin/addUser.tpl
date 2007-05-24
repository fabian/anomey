{layout template="Admin/security.tpl" title="Add User"}
  
  {form}
   <fieldset>
    <legend><span>General</span></legend>
                     
    <div class="text">
     <label for="nick" title="Username of the user.">Username <span class="required" title="Required">*</span></label><br />
     <input type="text" name="nick" id="nick" value="{$form.nick}" title="Username of the user." />
    </div>
                     
    <div class="text odd">
     <label for="mail" title="E-mail address of the user.">E-mail address</label><br />
     <input type="text" name="mail" id="mail" value="{$form.mail}" title="E-mail address of the user." />
    </div>
                     
    <div class="text">
     <label for="prename" title="Prename of the user.">Prename</label><br />
     <input type="text" name="prename" id="prename" value="{$form.prename}" title="Prename of the user." />
    </div>
                     
    <div class="text odd">
     <label for="lastname" title="E-mail address of the user.">Lastname</label><br />
     <input type="text" name="lastname" id="lastname" value="{$form.lastname}" title="Lastname of the user." />
    </div>
   </fieldset>
   
   <fieldset>
    <legend><span>Password</span></legend>
                     
    <div class="text">
     <label for="newPassword" title="Password of the user.">Password <span class="required" title="Required">*</span></label><br />
     <input type="password" name="newPassword" id="newPassword" value="" title="Password of the user." />
    </div>
                     
    <div class="text odd">
     <label for="newPasswordRepeat" title="Repetation of the password of the user.">Password again <span class="required" title="Required">*</span></label><br />
     <input type="password" name="newPasswordRepeat" id="newPasswordRepeat" value="" title="Repetation of the password of the user." />
    </div>
   </fieldset>

   <div>
    {submit value="Add user"}
    {cancel}
   </div>
  {/form}
{/layout}
