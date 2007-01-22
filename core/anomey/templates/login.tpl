{layout template="site.tpl" title="Login"}
{form}
<input id="forward" name="forward" value="{$form.forward}" type="hidden" />
<p>
  Username: <input id="username" name="username" value="{$form.username}" />
</p>

<p>
 Password: <input id="password" name="password" type="password" />
</p>

<p>
 <input id="remember" name="remember" type="checkbox" value="true" /> <label for="remember">Remember me</label>
</p>
		
{submit value="Login"}
{/form}
{/layout}