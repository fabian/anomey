{layout template="Admin/designLayout.tpl" title="Copy files"}

  {form}
   <fieldset>
    <legend><span>General</span></legend>
                     
     <div class="multiple large last">
     <label for="files" title="Select the files to copy.">Files to copy <span class="required" title="Required">*</span></label><br />
     <select multiple="multiple" name="filesToCopy[]" id="files" size="11">
      {html_options options=$form->getFiles() selected=$form.filesToCopy[]}
     </select>
    </div>
   </fieldset>

   <div>
    <input type="hidden" name="confirmed" id="confirmed" value="{$form.confirmed}" />
    
    {submit  value="Copy selected files"}
    {cancel}
   </div>
  {/form}
{/layout}
