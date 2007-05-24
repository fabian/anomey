{layout template="module.tpl" title="Add new entry"}
{form}
<p>
 Your name:
 <input id="name" name="name" value="{$form.name}" />
</p>
<p>
 Your content:
 <textarea id="comment" name="comment" value="{$form.comment}"></textarea>
</p>
{submit value="Add entry"} {cancel}
{/form}
{/layout}