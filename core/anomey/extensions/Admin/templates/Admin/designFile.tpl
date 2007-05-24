{layout template="Admin/designLayout.tpl" title="Edit file"}

  {form}
   <fieldset>
    <legend><span>General</span></legend>
                     
    <div class="text last">
     <label for="file" title="Filename of the file">Filename <span class="required" title="Required">*</span></label><br />
     <input type="text" name="file" id="file" value="{$form.file}" title="Name of the file." />
    </div>
   </fieldset>
   
   <fieldset>
    <legend><span>Content</span></legend>
                     
    <div class="html">
     <textarea name="contentOfFile" id="contentOfFile" rows="12" cols="32" wrap="off" title="Content of file">{$form.contentOfFile}</textarea>
    </div>
   </fieldset>

   <div>
    <input type="hidden" name="confirmed" id="confirmed" value="{$form.confirmed}" />
    
    {submit  value="Save changes"}
    {cancel}
   </div>
  {/form}
{/layout}
