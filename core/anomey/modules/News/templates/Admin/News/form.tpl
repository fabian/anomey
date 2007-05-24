   <fieldset>
    <legend><span>General</span></legend>
                     
    <div class="text">
     <label for="title" title="Title of the entry.">Title <span class="required" title="Required">*</span></label><br />
     <input type="text" name="title" id="title" value="{$form.title}" title="Title of the entry." />
    </div>
    
    <div class="datetime odd last">
        <label title="The time point when the entry was/will be published.">Publication date</label><br />
        <div class="datetime_select">
        {html_select_date month_extra="title=\"Month\"" field_order="YMD" day_extra="title=\"Day\"" year_extra="title=\"Year\"" start_year="-5" month_format="%m" end_year="+5" time=$form.publicationDate field_array="publicationDate" prefix=""}
        at {html_select_time time=$form.publicationTime field_array="publicationTime" prefix="" display_seconds=no}
        </div>
    </div>
   </fieldset>
   
   <fieldset>
    <legend><span>Content</span></legend>
                     
    <div class="html">
     <textarea name="contentOfEntry" id="contentOfEntry" rows="12" cols="32" title="Content page.">{$form.contentOfEntry}</textarea>
    </div>
   </fieldset>
