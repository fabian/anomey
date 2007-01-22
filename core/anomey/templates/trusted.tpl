{layout template="site.tpl" title="Login"}
{form}
<input id="forward" name="forward" value="{$form.forward}" type="hidden" />
<p>
  Benutzername: <strong>{$request.user.nick}</strong>
</p>

<p>
 Passwort: <input id="password" name="password" type="password" />
</p>
		
{submit value="OK"}
{/form}
{/layout}