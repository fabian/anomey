<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
	    <title>{$caption}{if $title} / {$title}{/if}</title>
	    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="{resource file="style.css"}" />
	    {$header}
	</head>
	<body class="blue">
	
	<h1>{link trail=""}<a href="{$href}">{$caption}</a>{/link}</h1>
		
		
		{include file="menu.tpl" links=$processor.links}

		<h2>{$title}</h2>
	
	<div id="content">

		{if count($request.messages) > 0}		
		<ul>
			{foreach from=$request.messages item=message}
			<li class="message {$message.type}">{$message.value}</li>
			{/foreach}
		</ul>
		{/if}
		
		{$content}
		
		<hr/>
		
		<p id="footer">
			{link trail="/admin"}<a href="{$href}">Admin</a>, {/link}
			{if $request.user}
			Logged in as {$request.user.nick} {if $request.user.trusted == true}(trusted){/if}, 
			{link trail="/logout"}<a href="{$href}">Logout</a>{/link}
			{else}
			{link trail="/login"}<a href="{$href}">Login</a>{/link}
			{/if}
			, Powered by anomey {$version}
		</p>
	</div>
 </body>
</html>
