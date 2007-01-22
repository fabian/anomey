{layout template="Admin/edit.tpl"}

{form}
<fieldset>
	<legend><span>General</span></legend>
         
	<div class="text">
		<label for="title" title="Title of the gallery.">Title <span class="required" title="Required">*</span></label><br />
		<input type="text" name="title" id="title" value="{$form.title}" title="Title of the gallery." />
	</div>

	<div class="datetime odd last">
		<label title="The time point when the gallery was made.">Date</label><br />
		<div class="datetime_select">
			{html_select_date month_extra="title=\"Month\"" field_order="YMD" day_extra="title=\"Day\"" year_extra="title=\"Year\"" start_year="-5" month_format="%m" end_year="+5" time=$form.date field_array="date" prefix=""}
		</div>	
	</div>
	
	<div class="text">
		<label for="parent" title="Parentgallery.">Parent <span class="required" title="Required">*</span></label><br />
		<select name="parentid">
			{html_options values=$form.parentids output=$form.parents selected=$form.parentid}
		</select>
	</div>
</fieldset>
	
<div>
	{submit  value="Create Gallery"}
	{cancel}
</div>
{/form}

{/layout}