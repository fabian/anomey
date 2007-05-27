{layout template="Admin/edit.tpl"}

Please enter a <i>Picasa rss feed</i> <b>or</b> a <i>Flickr photoset</i>.
<br/><br/>
{form}
<fieldset>
	<legend><span>General</span></legend>
         
	<div class="text">
		<label for="rss" title="Picasa rss feed.">Picasa</label><br />
		<input type="text" name="rss" id="rss" value="{$form.rss}" title=""Picasa rss feed." />
	</div>
	
	<div class="text">
		<label for="rss" title="Flickr photoset.">Flickr</label><br />
		<input type="text" name="photoset" id="photoset" value="{$form.photoset}" title=""Picasa rss feed." />
	</div>
	
	<div class="text">
		<label for="parent" title="Parentgallery.">Parent <span class="required" title="Required">*</span></label><br />
		<select name="parentid">
			{html_options values=$form.parentids output=$form.parents selected=$form.parentid}
		</select>
	</div>
</fieldset>
	
<div>
	{submit  value="Create Import"}
	{cancel}
</div>
{/form}

{/layout}