{layout template="Admin/Text/layout.tpl" title="File upload"}
  
  {form enctype="multipart/form-data"}
   <fieldset>
    <legend><span>General</span></legend>
                     
    <div class="file">
     <label for="file" title="The file to upload.">File <span class="required" title="Required">*</span></label><br />
     <input type="file" name="file" id="file" title="The file to upload." />
    </div>
                     
    <div class="text odd">
     <label for="name" title="The new filename on the server (leave empty for using the original filename).">New filename</label><br />
     <input type="text" name="name" id="name" value="{$form.name}" title="The new filename on the server (leave empty for using the original filename)." />
    </div>
   </fieldset>

   <div>
    {submit value="Upload file"}
    {cancel}
   </div>
  {/form}
{/layout}
