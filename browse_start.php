<?php

echo '<html><head><title>Launching NLN Materials browser</title>';
echo '<style>BODY{background-color:#D3D3D3;font: "Verdana" 12px black;font-weight:bold;padding:0;margin:0} .blk{background-color:black;margin:0;padding:0;width:100%;} ';
echo 'H1 {font-size: 26px;font-family: "Trebuchet MS", "Bliss", "Arial";padding: 10px;text-transform: lowercase;margin: 0px; font-weight:bold;} ';
echo '.n1 {color:#F5851F; font-style:italic;} .n2 {color:#636466;} </style><script> ';
echo 'function go() { document.getElementById(\'url\').value=document.location; ';
echo 'document.getElementById(\'currId\').value=window.opener.document.getElementById(\'id_reference\').value; ';
// comment out the following line to prevent automatic forwarding to the Noodle site
echo 'document.getElementById(\'noodStart\').submit(); ';
echo '}; </script></head>';
echo '<body onload="go()">';
echo '<div class="blk" id="blk"><h1 ><span class="n1">NLN</span> <span class="n2">Materials</span></h1></div>';
echo '<p style="padding:20px; font-family: Verdana, Arial"><img align="absmiddle" src="busy.gif" /> Connecting to the NLN Materials browser...</p>';
echo '<form id="noodStart" action="http://noodle.nln.ac.uk/noodle.asp?act=Start" method="post">';
// bring in optional value for custom "add to moodle course" button text
if(isset($CFG->noodleAddBtn))
	{
	echo '<input type="hidden" name="noodleAddBtn" value="'.$CFG->noodleAddBtn.'" />';
	};
// bring in a value for ftReq to launch straight into search results for that page
echo '<input type="hidden" name="currId" id="currId" value="" />';
echo '<input type="hidden" id="url" name="url" value="" /><input type="hidden" name="source" value="moodle" />';
echo '</form></body></html>';
