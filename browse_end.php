<?php
  $xtid = $_POST['xtid'];
  $title = $_POST['title'];
  $description = $_POST['description'];
  if(!empty($description)) $description = str_replace('"',"\"",$description);
?>
<html><head><script>
	
function init()
	{
	var goneErr = "Sorry, but this window cannot find the original resource edit page in Moodle, "+
		"so it can't update its values. Please close this window and return to Moodle to try again."+
		"<br /><button onclick='window.close()'>Close window</button>";
	try
		{
		var f = window.opener;
		var d = false;
		if(f) d=f.document;
		if((!f)||(!d)||(!d.getElementById))
			{
			document.getElementById('err').innerHTML = goneErr;
			return(0);
			};
		d.getElementById('id_loid').value = '<?php echo $xtid ?>';
		d.getElementById('id_name').value = '<?php echo $title ?>';
	  	var gBox = d.getElementById('general');
	  	if(gBox)
	  		{
	  		var	fram = gBox.getElementsByTagName('iframe');
	  		if(fram)
	  			{
	  			if(fram.length>0) { fram=fram[0] } else { fram=false; }
	  			if(fram)
	  				{
	  				var oDoc = fram.contentWindow || fram.contentDocument;
	    			var framDoc = false;
	    			if (oDoc.document) { framDoc = oDoc.document; }
	    			if(framDoc)
	  					{
	  					framDoc.body.innerHTML = "<?php echo $description?>";
	  					};
	  				};
	  			}
			};
	  	window.close();
		}
	catch(err)
		{
		document.getElementById('err').innerHTML = goneErr + "<br />error: "+err.message;
		return(0);
		};
	};
  </script></head>
  <body onload="init()">
  	<noscript>Sorry, you must have scripting enabled to use this feature</noscript>
  	<p id="err"></p>
  </body>
</html>