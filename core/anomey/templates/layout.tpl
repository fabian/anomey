<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
	    <title>{$caption}{if $title} / {$title}{/if}</title>
	    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="{resource file="stylesheets/screen.css"}" />
	    {$header}
	    {if $mootools or $slimbox}
	    <script type="text/javascript" src="{resource file="javascript/mootools.v1.00.js"}"></script>
	    {if $slimbox}
		<link rel="stylesheet" href="{resource file="stylesheets/slimbox.css"}" type="text/css" media="screen" />
		<script type="text/javascript" src="{resource file="javascript/slimbox.js"}"></script>
		{assign var="mootools" value="true"}
		{/if}
	    {/if}
	</head>
	<body>
	
	<p id="logo">{if $request.trail eq $processor.homeTrail}{$caption}{else}{link trail="/"}<a href="{$href}">{$caption}</a>{/link}{/if}</p>
		
	<div id="header">
		
		{include file="menu.tpl" links=$processor.links}

		<h1>{$title}</h1>
	</div>
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
		
		<ul>
			{link trail="/admin"}<li><a href="{$href}">Admin</a></li>{/link}
			{if $request.user}
			<li>Logged in as {$request.user.nick} {if $request.user.trusted == true}(trusted){/if}</li>
			{link trail="/logout"}<li><a href="{$href}">Logout</a></li>{/link}
			{else}
			{link trail="/login"}<li><a href="{$href}">Login</a></li>{/link}
			{/if}
			<li>Powered by anomey {$version}</li>
		</ul>
	</div>
	</body>
</html>
