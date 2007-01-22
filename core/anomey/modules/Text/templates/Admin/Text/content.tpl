{layout template="Admin/Text/layout.tpl" title="Content"}
  
  {form}   
   <fieldset>
    <legend><span>General</span></legend>
                     
    <div class="html">
     <textarea name="contentOfPage" id="contentOfPage" rows="12" cols="32" title="Content page.">{$form.contentOfPage}</textarea>
    </div>
   </fieldset>  

   <div>
    {submit  value="Save changes"}
    {cancel}
   </div>
  {/form}
{/layout}
