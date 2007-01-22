{layout template="Admin/edit.tpl"}

{form enctype="multipart/form-data"}
<fieldset>
	<legend><span>General</span></legend>
         
	<div class="text">
		<label for="title" title="Online image.">Online image:</label><br />
		<input type="text" name="onlineimage" id="onlineimage" value="{$form.onlineimage}" title="Online image." />
	</div>

	<div class="file">
		<label for="title" title="Upload local image.">Upload image:</label><br />
		<input type="file" name="uploadimage" id="uploadimage" value="{$form.uploadimage}" title="Upload local image." />
	</div>
	
	<div class="text">
		<label for="parent" title="Parentgallery.">Parent <span class="required" title="Required">*</span></label><br />
		<select name="parentid">
			{html_options values=$form.parentids output=$form.parents selected=$form.parentid}
		</select>
	</div>
</fieldset>
	
<div>
	{submit  value="Create Image"}
	{cancel}
</div>
{/form}

{/layout}