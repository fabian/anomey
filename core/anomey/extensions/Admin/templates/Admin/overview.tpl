{layout template="Admin/layout.tpl" title="Overview"}

{capture assign="header"}
<script type="text/javascript" src="{resource file="javascripts/upgrade.js"}"></script>
{/capture}

<div id="overview">
 <p><a href="http://anomey.ch/">anomey</a> is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.</p>
 
 <p>anomey is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.</p>
 
 <p>You should have received a copy of the GNU General Public License
 along with anomey (license.txt); if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 or visit <a href="http://www.gnu.org/copyleft/gpl.html">http://www.gnu.org/copyleft/gpl.html</a>.</p>
 
 <p>The nice icons are part of the <a href="http://famfamfam.com/lab/icons/silk/">Silk</a> icon set and were created by Mark James.</p>
 
 <hr/>
 
 <p>{$server}</p>
 
 <p>Copyright &copy; 2006 - Adrian Egloff, 
 Cyril Gabathuler and Fabian Vogler</p>
</div>
{/layout}
