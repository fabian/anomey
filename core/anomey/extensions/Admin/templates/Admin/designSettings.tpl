{layout template="Admin/designLayout.tpl" title="Settings"}
  
  {form}
   <fieldset>
    <legend><span>General</span></legend>
                     
    <div class="text">
     <label for="title" title="Title of the design">Title<span class="required" title="Required">*</span></label><br />
     <input type="text" name="title" id="title" value="{$form.title}" title="Title of the design" />
    </div>
                     
    <div class="text odd">
     <label for="name" title="Name of the design">Name <span class="required" title="Required">*</span></label><br />
     <input type="text" name="name" id="name" value="{$form.name}" title="Name of the design" />
    </div>
                     
    <div class="text">
     <label for="author" title="Author of the design.">Author</label><br />
     <input type="text" name="author" id="author" value="{$form.author}" title="Author of the design." />
    </div>
                     
    <div class="text odd last">
     <label for="license" title="License of the design.">License</label><br />
     <input type="text" name="license" id="license" value="{$form.license}" title="License of the design." />
    </div>
   </fieldset>

   <div>    
    {submit  value="Save changes"}
    {cancel}
   </div>
  {/form}
{/layout}
