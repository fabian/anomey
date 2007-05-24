{layout template="Admin/layout.tpl" title="Settings"}
  
  {form}
   <fieldset>
    <legend><span>General</span></legend>
                     
    <div class="text">
     <label for="title" title="Title of the page.">Title <span class="required" title="Required">*</span></label><br />
     <input type="text" name="title" id="title" value="{$form.title}" title="Title of the page." />
    </div>
                     
    <div class="text odd">
     <label for="design" title="Design of the page.">Design</label><br />
     <select name="design" id="design">
     	{html_options options=$form->getDesigns() selected=$form.design}
     </select>
    </div>
   </fieldset>
   
   <fieldset>
    <legend><span>URL configuration</span></legend>
                     
    <div class="text">
     <label for="title" title="Path to home page.">Home page <span class="required" title="Required">*</span></label><br />
     <input type="text" name="home" id="home" value="{$form.home}" title="Path to home page." />
    </div>
                     
    <div class="text odd">
     <label for="accessDenied" title="Path to 'access denied' page.">Access denied <span class="required" title="Required">*</span></label><br />
     <input type="text" name="accessDenied" id="accessDenied" value="{$form.accessDenied}" title="Path to 'access denied' page." />
    </div>
                     
    <div class="text">
     <label for="pageNotFound" title="Path to 'page not found' page.">Page not found <span class="required" title="Required">*</span></label><br />
     <input type="text" name="pageNotFound" id="pageNotFound" value="{$form.pageNotFound}" title="Path to 'page not found' page." />
    </div>
   </fieldset>  

   <div>
    {submit value="Save changes"}
    {cancel}
   </div>
  {/form}
{/layout}
