<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <title>{if $model.site}{$model.site.title}{else}{$model.title}{/if} / Admin {if $middleTitle}/ {$middleTitle}{/if}/ {$title}</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="{resource file="stylesheets/admin.css"}" />
    <!--[if IE]><script type="text/javascript" src="{resource file="javascripts/ie.js"}"></script><![endif]-->
    <script type="text/javascript" src="{resource file="javascripts/questions.js"}"></script>
    <script type="text/javascript" src="{resource file="javascripts/sorttable.js"}"></script>
</head>
<body>

<div id="container">

    <div id="header">
        <p id="logo" title="anomey content management"><em>anomey</em> content management</p>
    
        <ul id="userInfo">
            <li class="first">Username:&nbsp;{$request.user.nick}</li>
            {link trail="/"}<li class="last"><a href="{$href}">Back to site</a></li>{/link}
        </ul>

		<ul id="navigation" class="navigation">
	    	{link trail="/admin/overview"}<li><a{if $active} class="active"{/if} href="{$href}">Overview</a></li>{/link}
	    	{link trail="/admin/pages"}<li><a{if $active} class="active"{/if} href="{$href}">Pages</a></li>{/link}
	    	{link trail="/admin/designs"}<li><a{if $active} class="active"{/if} href="{$href}">Designs</a></li>{/link}
	    	{link trail="/admin/security"}<li><a{if $active} class="active"{/if} href="{$href}">Security</a></li>{/link}
	    	{link trail="/admin/settings"}<li><a{if $active} class="active"{/if} href="{$href}">Settings</a></li>{/link}
		</ul>
    </div>
    
    {if $middleTitle or $middle}
    <div id="middle">
    	{if $middleTitle}
	    <h1 id="title">{$middleTitle}</h1>
	    {/if}
		
	   	{$middle}
    </div>
    {/if}

    <div id="content">
    	{if $middleTitle && $title}
        <h2 id="title">{$title}</h2>
        {elseif $title}
        <h1 id="title">{$title}</h1>
        {/if}
        
        {$actions}
	
        {if count($request.messages) > 0}		
		<ul id="messages">
			{foreach from=$request.messages item=message}
			<li><img src="{resource file="images/icons/message/`$message.type`.png}" width="16" height="16" />{$message.value}</li>
			{/foreach}
		</ul>
		{/if}
		
		{$content}
		
    </div>
    
    <p id="footer">anomey {$version}</p>
    
    </div>

</body>
</html>


