<h1>Error occured</h1>

<p class="block"><samp>
<?php echo get_class($e); ?>:&nbsp;<?php echo $e->getMessage(); ?><br/>
<?php foreach ($e->getTrace() as $tail): ?>
<?php if(isset($tail['file']) and isset($tail['line'])): ?>
&nbsp;at <?php echo $tail['file']; ?>:<?php echo $tail['line']; ?><br/>
&nbsp;&nbsp;&nbsp;&nbsp;<?php echo isset($tail['class']) ? $tail['class'] : ''; ?>
<?php echo isset($tail['type']) ? $tail['type'] : ''; ?><?php echo $tail['function']; ?>();<br/>
<?php endif; ?>
<?php endforeach; ?>
</samp></p>

<p>You can see the trace because debugging is enabled. Set <code>enabled=false</code> in
«conf/debug.properties» to hide the trace for security reasons.</p>
