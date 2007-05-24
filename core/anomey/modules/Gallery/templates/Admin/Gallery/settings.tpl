{layout template="Admin/Gallery/layout.tpl" title="Settings"}
  
  {form}
   <fieldset>
    <legend><span>Thumbnail</span></legend>
                     
    <div class="text">
     <label for="thumbwidth" title="Width of the thumbnail.">Width <span class="required" title="Required">*</span></label><br />
     <input type="text" name="thumbwidth" id="thumbwidth" value="{$form.thumbwidth}" title="Width of the thumbnail." />
    </div>
                     
    <div class="text odd">
     <label for="thumbheight" title="Height of the thumbnail.">Height <span class="required" title="Required">*</span></label><br />
     <input type="text" name="thumbheight" id="thumbheight" value="{$form.thumbheight}" title="Height of the thumbnail." />
    </div>
    
    <div class="checkbox last">
    	<input type="checkbox" name="thumbcrop" id="show" value="show"{if $form.thumbcrop} checked="checked"{/if} title="Crop thumbnail." /> <label for="thumbcrop" title="Crop thumbnail.">Crop thumbnail</label>
    </div>
   </fieldset>
   
   <fieldset>
    <legend><span>Image</span></legend>
                     
    <div class="text">
     <label for="imagewidth" title="Width of the image.">Width <span class="required" title="Required">*</span></label><br />
     <input type="text" name="imagewidth" id="imagewidth" value="{$form.imagewidth}" title="Width of the image." />
    </div>
                     
    <div class="text odd">
     <label for="imageheight" title="Height of the image.">Height <span class="required" title="Required">*</span></label><br />
     <input type="text" name="imageheight" id="imageheight" value="{$form.imageheight}" title="Height of the image." />
    </div>
              
    <div class="checkbox last">
    	<input type="checkbox" name="imagecrop" id="show" value="show"{if $form.imagecrop} checked="checked"{/if} title="Crop image." /> <label for="imagecrop" title="Crop image.">Crop image</label>
    </div>
   </fieldset>
   
   <fieldset>
    <legend><span>Pages</span></legend>
    
    <div class="text">
     <label for="rows" title="Number of rows.">Number of rows</label><br />
     <input type="text" name="rows" id="rows" value="{$form.rows}" title="Number of rows." />
    </div>
    
    <div class="text odd">
     <label for="cols" title="Number of cols.">Number of cols</label><br />
     <input type="text" name="cols" id="cols" value="{$form.cols}" title="Number of cols." />
    </div>
    
   </fieldset>

   <div>
    {submit  value="Save changes"}
    {cancel}
   </div>
  {/form}
{/layout}
