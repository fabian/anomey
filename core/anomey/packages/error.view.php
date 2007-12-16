<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Error occured</title>
<style type="text/css" media="all">
body {
	margin: 0;
	padding: 1em;
	border-left: 0.5em solid #A00;
	font-family: Verdana, Helvetica, Arial, sans-serif;
	font-size: 0.8em;
}

.block {
	color: #333;
	font-size: 1.2em;
}

table.block {
	border-collapse: collapse;
}

table.block td {
	border: 1px solid #DDD;
	padding: 0.1em 0.2em;
}

table.block .even td {
	background-color: #EEE;
}

.background {
	color: #666;
}

h1, h2, h3, h4, h5, h6 {
	font-family: "Trebuchet MS", sans-serif;
}

.call {
	color: #043E5F;
}
</style>
</head>
<body>
<h1>Error occured</h1>

<?php if($debug_enabled): ?>
<h2>Trace</h2>

<p class="block background"><code> <?php echo get_class($e); ?>:&nbsp;<?php echo $e->getMessage(); ?><br />
<?php foreach ($e->getTrace() as $tail): ?> <?php if(isset($tail['file']) and isset($tail['line'])): ?>
&nbsp;at <?php echo $tail['file']; ?>:<?php echo $tail['line']; ?><br />
&nbsp;&nbsp;&nbsp;&nbsp;<span class="call"><?php echo isset($tail['class']) ? $tail['class'] : ''; ?>
<?php echo isset($tail['type']) ? $tail['type'] : ''; ?><?php echo $tail['function']; ?>();</span><br />
<?php endif; ?> <?php endforeach; ?> </code></p>

<h2>Enviroment</h2>

<table class="block">
	<tr class="even">
		<td><code>Request:</code></td>
		<td><code><?php var_export(htmlentities($request->getMethod())); ?>;
			<?php var_export(htmlentities($request->getTrail())); ?>;
			</code></td>
	</tr>
	<tr>
		<td><code>URL:</code></td>
		<td><code><?php var_export(htmlentities($url->getScheme())); ?>; 
			<?php var_export(htmlentities($url->getHost())); ?>; 
			<?php var_export(htmlentities($url->getPath())); ?>;
			<?php var_export(htmlentities($url->getBase())); ?></code></td>
	</tr>
	<tr class="even">
		<td><code>Script name:</code></td>
		<td><code><?php var_export(htmlentities($_SERVER['SCRIPT_NAME'])); ?></code></td>
	</tr>
	<tr>
		<td><code>User:</code></td>
		<td><code><?php var_export($request->getUser() ? htmlentities($request->getUser()->getNick()) : htmlentities($request->getUser())); ?></code></td>
	</tr>
	<tr class="even">
		<td><code>PATH_INFO:</code></td>
		<td><code><?php var_export(htmlentities($_SERVER['PATH_INFO'])); ?></code></td>
	</tr>
	<tr>
		<td><code>ORIG_PATH_INFO:</code></td>
		<td><code><?php var_export(htmlentities($_SERVER['ORIG_PATH_INFO'])); ?></code></td>
	</tr>
	<tr class="even">
		<td><code>Profile:</code></td>
		<td><code><?php var_export(htmlentities($profile)); ?></code></td>
	</tr>
	<tr>
		<td><code>Server software:</code></td>
		<td><code><?php var_export(htmlentities($_SERVER['SERVER_SOFTWARE'])); ?></code></td>
	</tr>
	<tr class="even">
		<td><code>PHP version:</code></td>
		<td><code><?php var_export(phpversion()); ?></code></td>
	</tr>
	<tr>
		<td><code>anomey version:</code></td>
		<td><code><?php var_export(Anomey::VERSION); ?></code></td>
	</tr>
</table>

<h2>Output</h2>

<p class="block"><code> <?php echo htmlentities($code); ?> </code></p>

<hr/>

<p>You can see the trace and the output because debugging is enabled. Set <code>enabled=false</code>
in «<?php echo htmlentities($profile); ?>/config/debug.ini» to hide the trace for security reasons.</p>

<?php else: ?>

<p>Debugging has been disabled inside the file «<?php echo htmlentities($profile); ?>/config/debug.ini».</p>

<?php endif; ?>

<p>anomey <?php echo Anomey::VERSION; ?></p>
</body>
</html>
