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

<h2>Trace</h2>

<p class="block background"><samp> <?php echo get_class($e); ?>:&nbsp;<?php echo $e->getMessage(); ?><br />
<?php foreach ($e->getTrace() as $tail): ?> <?php if(isset($tail['file']) and isset($tail['line'])): ?>
&nbsp;at <?php echo $tail['file']; ?>:<?php echo $tail['line']; ?><br />
&nbsp;&nbsp;&nbsp;&nbsp;<span class="call"><?php echo isset($tail['class']) ? $tail['class'] : ''; ?>
<?php echo isset($tail['type']) ? $tail['type'] : ''; ?><?php echo $tail['function']; ?>();</span><br />
<?php endif; ?> <?php endforeach; ?> </samp></p>

<h2>Output</h2>

<p class="block"><samp> <?php echo htmlentities($code); ?> </samp></p>

<p>You can see the trace and the output because debugging is enabled. Set <code>enabled=false</code>
in «conf/debug.properties» to hide the trace for security reasons.</p>

<hr/>

<p>anomey <?php echo Anomey::VERSION; ?></p>
</body>
</html>
