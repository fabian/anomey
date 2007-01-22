{layout template="Admin/layout.tpl" title="Create new page"}
  
  {form}
   <fieldset>
    <legend><span>General</span></legend>
                     
    <div class="text">
     <label for="title" title="Title of the page.">Title <span class="required" title="Required">*</span></label><br />
     <input type="text" name="title" id="title" value="{$form.title}" title="Title of the page." />
    </div>
                     
    <div class="text odd">
     <label for="name" title="Name of the page.">Name <span class="required" title="Required">*</span></label><br />
     <input type="text" name="name" id="name" value="{$form.name}" title="Name of the page." />
    </div>
                     
    <div class="text">
     <label for="parent" title="Parent page.">Parent page</label><br />
     <select name="parent" id="parent">
     	{html_options options=$form->getElements() selected=$form.parent}
     </select>
    </div>
                     
    <div class="text odd">
     <label for="type" title="Page type.">Type</label><br />
     <select name="type" id="type">
     	{html_options output=$form->getTypes() values=$form->getTypes() selected=$form.type}
     </select>
    </div>
                     
    <div class="radio last">
     <input type="radio" name="display" id="show" value="show"{if $form.display eq "show"} checked="checked"{/if} title="Show page." /> <label for="show" title="Show page.">Show</label>
     <input type="radio" name="display" id="hide" value="hide"{if $form.display eq "hide"} checked="checked"{/if} title="Hide page." /> <label for="hide" title="Hide page.">Hide</label>
    </div>
   </fieldset>

   <div>
    {submit value="Create page"}
    {cancel}
   </div>
  {/form}
{/layout}
