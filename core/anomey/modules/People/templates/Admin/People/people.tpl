{layout template="Admin/edit.tpl" title="Content"}

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
                     
    <div class="radio odd last">
     <input type="radio" name="display" id="show" value="show"{if $form.display eq "show"} checked="checked"{/if} title="Show page." /> <label for="show" title="Show page.">Show</label>
     <input type="radio" name="display" id="hide" value="hide"{if $form.display eq "hide"} checked="checked"{/if} title="Hide page." /> <label for="hide" title="Hide page.">Hide</label>
    </div>
   </fieldset>
   
   <fieldset>
    <legend><span>People</span></legend>
                     
    <div class="text">
     <label for="displayMode" title="Display mode.">Display</label><br />
     <select name="displayMode" id="displayMode">
       {html_options options=$form->getDisplayModes() selected=$form.displayMode}
     </select>
    </div>
                     
    <div class="text last">
     <label for="order" title="Attribute to order.">Order</label><br />
     <select name="order" id="order">
       {html_options options=$form->getOrderAttributes() selected=$form.order}
     </select>
    </div>
   </fieldset>
   
   <fieldset>
    <legend><span>Preface</span></legend>
                     
    <div class="html">
     <textarea name="preface" id="preface" rows="12" cols="32" title="Preface of the people list">{$form.preface}</textarea>
    </div>
   </fieldset>  

   <div>
    {submit  value="Save changes"}
    {cancel}
   </div>
  {/form}
{/layout}
