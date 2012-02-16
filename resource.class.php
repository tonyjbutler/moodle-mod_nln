<?php // $Id: resource.class.php,v 0.4 2008/04/08 00:05:21 arborrow Exp $

/**
* Extend the base resource class for NLN resources. See the readme.txt file in this directory for more information
*/
class resource_nln extends resource_base {

    function resource_nln($cmid=0) {
	    parent::resource_base($cmid);
		}

/* Add new instance of nln resource
* @param    resource object
*/
	function add_instance($resource) {
		$this->_postprocess($resource);
		return parent::add_instance($resource);
		}

/* Update instance of nln resource
* @param    resource object
*/
    function update_instance($resource)
    	{
		$this->_postprocess($resource);
		return parent::update_instance($resource);
		}

    function _postprocess(&$resource)
    	{
        global $RESOURCE_WINDOW_OPTIONS;
        $alloptions = $RESOURCE_WINDOW_OPTIONS;

		$resource->alltext = '';
		$resource->popup = '';

        if ($resource->windowpopup)
        	{
			$optionlist = array();
			foreach ($alloptions as $option)
				{
				$optionlist[] = $option."=".$resource->$option;
				unset($resource->$option);
				}
			$resource->popup = implode(',', $optionlist);
			unset($resource->windowpopup);
			$resource->options = '';
			}
		}


/**
* Display the NLN resource
*
* Launches a window using reference property of the resource record as the NLN LO GUID
* @param    CFG     global object
*/
    function display() {

        global $CFG, $THEME, $USER;

        parent::display();

		/// Set up some shorthand variables
        $cm = $this->cm;
        $course = $this->course;
        $resource = $this->resource;

        $querystring = '';
        $pagetitle = strip_tags($course->shortname.': '.format_string($resource->name));

        $formatoptions = new object();
        $formatoptions->noclean = true;

        /// Set up some variables

        $inpopup = optional_param('inpopup', 0, PARAM_BOOL);
		$nlnUrl = 'http://www.nln.ac.uk/preview.asp?mode=noodle&prov=aclProv'.$CFG->orgID.'&loid='.urlencode($resource->reference);
		if(function_exists("build_navigation"))
			{
			$navigation = build_navigation($this->navlinks, $cm);
			}
			else 
			{ $navigation = "$this->navigation ".format_string($resource->name); }
		if ($resource->popup)
			{
			if($inpopup) { redirect($nlnUrl); exit; }
			
			print_header($pagetitle, $course->fullname, $navigation, "", "", true, update_module_button($cm->id, $course->id, $this->strresource), navmenu($course, $cm));
        	echo '<script type="text/javascript">';
        	echo "openpopup('/mod/resource/view.php?inpopup=true&id=".$cm->id."', 'nlnrepo', '".$resource->popup."');\n";
        	echo '</script>';
			
			if (trim(strip_tags($resource->summary)))
				{
				print_simple_box(format_text($resource->summary, FORMAT_MOODLE, $formatoptions), "center");
				}
			
			$link = "<a href=\"$CFG->wwwroot/mod/resource/view.php?inpopup=true&amp;id={$cm->id}\" "
				. "onclick=\"this.target='resource{$resource->id}'; return openpopup('/mod/resource/view.php?inpopup=true&amp;id={$cm->id}', "
				. "'resource{$resource->id}','{$resource->popup}');\">".format_string($resource->name,true)."</a>";
	
			echo '<div class="popupnotice">';
			print_string('popupresource', 'resource');
			echo '<br />';
			print_string('popupresourcelink', 'resource', $link);
			echo '</div>';
			
			print_footer($course);
			
			if(!$inpopup) { exit; }
			} else {
			print_header($pagetitle, $course->fullname, $navigation, "", "", true, update_module_button($cm->id, $course->id, $this->strresource), navmenu($course, $cm));
			if (trim(strip_tags($resource->summary)))
				{
				print_simple_box(format_text($resource->summary, FORMAT_MOODLE, $formatoptions), "center");
				}
			echo '<iframe src="'.$nlnUrl.'&exitBtn=logo" style="width:100%; height: 600px;"></iframe>';
			print_footer($course);
			
			}
        
        /// We can only get here once per resource, so add an entry to the log

        add_to_log($course->id, "resource", "view", "view.php?id={$cm->id}", $resource->id, $cm->id);
    	}

    function setup_preprocessing(&$defaults)
    	{
        if (!isset($defaults['popup']))
        	{
            // use form defaults
	        }
	        else if (!empty($defaults['popup']))
	        	{
            	$defaults['windowpopup'] = 1;
            	if (array_key_exists('popup', $defaults))
            		{
                	$rawoptions = explode(',', $defaults['popup']);
                	foreach ($rawoptions as $rawoption)
                		{
                    	$option = explode('=', trim($rawoption));
                    	$defaults[$option[0]] = $option[1];
                		}
            		}
        		} else {
            	$defaults['windowpopup'] = 0;
            	if (array_key_exists('options', $defaults))
            		{
                	$defaults['framepage'] = ($defaults['options']=='frame');
            		}
        		}
    	}

    function setup_elements(&$mform)
        {
        global $CFG, $RESOURCE_WINDOW_OPTIONS;
		
		// overrides default "required field" behaviour to allow any values. This allows users to go straight to the browse button.
		HTML_QuickForm::registerRule('required', 'regex', '/.?/');

        //$mform->addElement('text', 'reference', get_string('nln_guid', 'resource'), array('readonly'=>'true'));
        $mform->addElement('text', 'reference', get_string('nln_guid', 'resource'));
		$mform->addRule('reference', get_string('nln_required','resource'), 'required', null, 'client');
		
		$searchbutton = $mform->addElement('button', 'browsebutton', get_string('nln_browse', 'resource').'...',array(
			'title'=>get_string('nln_browsedescrip', 'resource'),
			'onclick'=>"return window.open('../mod/resource/type/nln/browse_start.php', 'nlnbrowse', 'menubar=yes,scrollbars=yes,location=yes,resizable=yes,width=790,height=590'); "));
 
        $mform->addElement('header', 'displaysettings', get_string('display', 'resource'));

        $woptions = array(0 => get_string('pagewindow', 'resource'), 1 => get_string('newwindow', 'resource'));
        $mform->addElement('select', 'windowpopup', get_string('display', 'resource'), $woptions);
        $mform->setDefault('windowpopup', !empty($CFG->resource_popup));

        $mform->addElement('checkbox', 'framepage', get_string('keepnavigationvisible', 'resource'));

        //$mform->setHelpButton('framepage', array('frameifpossible', get_string('keepnavigationvisible', 'resource'), 'resource'));
        $mform->setDefault('framepage', 0);
        $mform->disabledIf('framepage', 'windowpopup', 'eq', 1);
        $mform->setAdvanced('framepage');

        foreach ($RESOURCE_WINDOW_OPTIONS as $option)
        	{
            if ($option == 'height' or $option == 'width')
            	{
                $mform->addElement('text', $option, get_string('new'.$option, 'resource'), array('size'=>'4'));
                $mform->setDefault($option, $CFG->{'resource_popup'.$option});
                $mform->disabledIf($option, 'windowpopup', 'eq', 0);
            	} else {
                $mform->addElement('checkbox', $option, get_string('new'.$option, 'resource'));
                $mform->setDefault($option, $CFG->{'resource_popup'.$option});
                $mform->disabledIf($option, 'windowpopup', 'eq', 0);
            	}
            $mform->setAdvanced($option);
        	}
        	
        }
	
	}

?>
