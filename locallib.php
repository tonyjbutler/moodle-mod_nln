<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Private nln module utility functions
 *
 * @package    mod
 * @subpackage nln
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/nln/lib.php");

/**
 * Return full url with NLN Learning Object ID
 *
 * This function does not include any XSS protection.
 *
 * @param string $nln
 * @return string url with & encoded as &amp;
 */
function nln_get_full_url($nln) {

    $loid = rawurlencode($nln->loid);

    $fullurl = 'http://www.nln.ac.uk/preview.asp?mode=noodle&loid='.$loid;

    // encode all & to &amp; entity
    $fullurl = str_replace('&', '&amp;', $fullurl);

    return $fullurl;
}

/**
 * Print nln header.
 * @param object $nln
 * @param object $cm
 * @param object $course
 * @return void
 */
function nln_print_header($nln, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$nln->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($nln);
    echo $OUTPUT->header();
}

/**
 * Print nln heading.
 * @param object $nln
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function nln_print_heading($nln, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($nln->displayoptions) ? array() : unserialize($nln->displayoptions);

    if ($ignoresettings or !empty($options['printheading'])) {
        echo $OUTPUT->heading(format_string($nln->name), 2, 'main', 'nlnheading');
    }
}

/**
 * Print nln introduction.
 * @param object $nln
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function nln_print_intro($nln, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($nln->displayoptions) ? array() : unserialize($nln->displayoptions);
    if ($ignoresettings or !empty($options['printintro'])) {
        if (trim(strip_tags($nln->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'nlnintro');
            echo format_module_intro('nln', $nln, $cm->id);
            echo $OUTPUT->box_end();
        }
    }
}

/**
 * Display nln frames.
 * @param object $nln
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function nln_display_frame($nln, $cm, $course) {
    global $PAGE, $OUTPUT, $CFG;

    $frame = optional_param('frameset', 'main', PARAM_ALPHA);

    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametop');
        nln_print_header($nln, $cm, $course);
        nln_print_heading($nln, $cm, $course);
        nln_print_intro($nln, $cm, $course);
        echo $OUTPUT->footer();
        die;

    } else {
        $config = get_config('nln');
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        $exteurl = nln_get_full_url($nln);
        $navurl = "$CFG->wwwroot/mod/nln/view.php?id=$cm->id&amp;frameset=top";
        $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
        $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
        $title = strip_tags($courseshortname.': '.format_string($nln->name));
        $framesize = $config->framesize;
        $modulename = s(get_string('modulename','nln'));
        $dir = get_string('thisdirection', 'langconfig');

        $extframe = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html dir="$dir">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>$title</title>
  </head>
  <frameset rows="$framesize,*">
    <frame src="$navurl" title="$modulename"/>
    <frame src="$exteurl" title="$modulename"/>
  </frameset>
</html>
EOF;

        @header('Content-Type: text/html; charset=utf-8');
        echo $extframe;
        die;
    }
}

/**
 * Print nln info and link.
 * @param object $nln
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function nln_print_workaround($nln, $cm, $course) {
    global $OUTPUT;

    nln_print_header($nln, $cm, $course);
    nln_print_heading($nln, $cm, $course, true);
    nln_print_intro($nln, $cm, $course, true);

    $fullurl = nln_get_full_url($nln);

    $display = nln_get_final_display_type($nln);
    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $jsfullurl = addslashes_js($fullurl);
        $options = empty($nln->displayoptions) ? array() : unserialize($nln->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $extra = "onclick=\"window.open('$jsfullurl', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $extra = "onclick=\"this.target='_blank';\"";

    } else {
        $extra = '';
    }

    echo '<div class="nlnworkaround">';
    print_string('clicktoopen', 'nln', "<a href=\"$fullurl\" $extra>$fullurl</a>");
    echo '</div>';

    echo $OUTPUT->footer();
    die;
}

/**
 * Display embedded nln object.
 * @param object $nln
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function nln_display_embed($nln, $cm, $course) {
    global $CFG, $PAGE, $OUTPUT;

    $fullurl  = nln_get_full_url($nln);
    $title    = $nln->name;

    $link = html_writer::tag('a', $fullurl, array('href'=>str_replace('&amp;', '&', $fullurl)));
    $clicktoopen = get_string('clicktoopen', 'nln', $link);

    $extension = resourcelib_get_extension($url->externalurl);

    if (in_array($mimetype, array('image/gif','image/jpeg','image/png'))) {  // It's an image
        $code = resourcelib_embed_image($fullurl, $title);

    } else if ($mimetype == 'audio/mp3') {
        // MP3 audio file
        $code = resourcelib_embed_mp3($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'video/x-flv' or $extension === 'f4v') {
        // Flash video file
        $code = resourcelib_embed_flashvideo($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'application/x-shockwave-flash') {
        // Flash file
        $code = resourcelib_embed_flash($fullurl, $title, $clicktoopen);

    } else if (substr($mimetype, 0, 10) == 'video/x-ms') {
        // Windows Media Player file
        $code = resourcelib_embed_mediaplayer($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'video/quicktime') {
        // Quicktime file
        $code = resourcelib_embed_quicktime($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'video/mpeg') {
        // Mpeg file
        $code = resourcelib_embed_mpeg($fullurl, $title, $clicktoopen);

    } else if ($mimetype == 'audio/x-pn-realaudio-plugin') {
        // RealMedia file
        $code = resourcelib_embed_real($fullurl, $title, $clicktoopen);

    } else {
        // anything else - just try object tag enlarged as much as possible
        $code = resourcelib_embed_general($fullurl, $title, $clicktoopen, $mimetype);
    }

    url_print_header($url, $cm, $course);
    url_print_heading($url, $cm, $course);

    echo $code;

    url_print_intro($url, $cm, $course);

    echo $OUTPUT->footer();
    die;
}

/**
 * Decide the best display format.
 * @param object $url
 * @return int display type constant
 */
function url_get_final_display_type($url) {
    global $CFG;

    if ($url->display != RESOURCELIB_DISPLAY_AUTO) {
        return $url->display;
    }

    // detect links to local moodle pages
    if (strpos($url->externalurl, $CFG->wwwroot) === 0) {
        if (strpos($url->externalurl, 'file.php') === false and strpos($url->externalurl, '.php') !== false ) {
            // most probably our moodle page with navigation
            return RESOURCELIB_DISPLAY_OPEN;
        }
    }

    static $download = array('application/zip', 'application/x-tar', 'application/g-zip',     // binary formats
                             'application/pdf', 'text/html');  // these are known to cause trouble for external links, sorry
    static $embed    = array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml',         // images
                             'application/x-shockwave-flash', 'video/x-flv', 'video/x-ms-wm', // video formats
                             'video/quicktime', 'video/mpeg', 'video/mp4',
                             'audio/mp3', 'audio/x-realaudio-plugin', 'x-realaudio-plugin',   // audio formats,
                            );

    $mimetype = resourcelib_guess_url_mimetype($url->externalurl);

    if (in_array($mimetype, $download)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    }
    if (in_array($mimetype, $embed)) {
        return RESOURCELIB_DISPLAY_EMBED;
    }

    // let the browser deal with it somehow
    return RESOURCELIB_DISPLAY_OPEN;
}

/**
 * Get the parameters that may be appended to URL
 * @param object $config url module config options
 * @return array array describing opt groups
 */
function url_get_variable_options($config) {
    global $CFG;

    $options = array();
    $options[''] = array('' => get_string('chooseavariable', 'url'));

    $options[get_string('course')] = array(
        'courseid'        => 'id',
        'coursefullname'  => get_string('fullnamecourse'),
        'courseshortname' => get_string('shortnamecourse'),
        'courseidnumber'  => get_string('idnumbercourse'),
        'coursesummary'   => get_string('summary'),
        'courseformat'    => get_string('format'),
    );

    $options[get_string('modulename', 'url')] = array(
        'urlinstance'     => 'id',
        'urlcmid'         => 'cmid',
        'urlname'         => get_string('name'),
        'urlidnumber'     => get_string('idnumbermod'),
    );

    $options[get_string('miscellaneous')] = array(
        'sitename'        => get_string('fullsitename'),
        'serverurl'       => get_string('serverurl', 'url'),
        'currenttime'     => get_string('time'),
        'lang'            => get_string('language'),
    );
    if (!empty($config->secretphrase)) {
        $options[get_string('miscellaneous')]['encryptedcode'] = get_string('encryptedcode');
    }

    $options[get_string('user')] = array(
        'userid'          => 'id',
        'userusername'    => get_string('username'),
        'useridnumber'    => get_string('idnumber'),
        'userfirstname'   => get_string('firstname'),
        'userlastname'    => get_string('lastname'),
        'userfullname'    => get_string('fullnameuser'),
        'useremail'       => get_string('email'),
        'usericq'         => get_string('icqnumber'),
        'userphone1'      => get_string('phone').' 1',
        'userphone2'      => get_string('phone2').' 2',
        'userinstitution' => get_string('institution'),
        'userdepartment'  => get_string('department'),
        'useraddress'     => get_string('address'),
        'usercity'        => get_string('city'),
        'usertimezone'    => get_string('timezone'),
        'userurl'         => get_string('webpage'),
    );

    if ($config->rolesinparams) {
        $roles = get_all_roles();
        $roleoptions = array();
        foreach ($roles as $role) {
            $roleoptions['course'.$role->shortname] = get_string('yourwordforx', '', $role->name);
        }
        $options[get_string('roles')] = $roleoptions;
    }

    return $options;
}

/**
 * Get the parameter values that may be appended to URL
 * @param object $url module instance
 * @param object $cm
 * @param object $course
 * @param object $config module config options
 * @return array of parameter values
 */
function url_get_variable_values($url, $cm, $course, $config) {
    global $USER, $CFG;

    $site = get_site();

    $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);

    $values = array (
        'courseid'        => $course->id,
        'coursefullname'  => format_string($course->fullname),
        'courseshortname' => format_string($course->shortname, true, array('context' => $coursecontext)),
        'courseidnumber'  => $course->idnumber,
        'coursesummary'   => $course->summary,
        'courseformat'    => $course->format,
        'lang'            => current_language(),
        'sitename'        => format_string($site->fullname),
        'serverurl'       => $CFG->wwwroot,
        'currenttime'     => time(),
        'urlinstance'     => $url->id,
        'urlcmid'         => $cm->id,
        'urlname'         => format_string($url->name),
        'urlidnumber'     => $cm->idnumber,
    );

    if (isloggedin()) {
        $values['userid']          = $USER->id;
        $values['userusername']    = $USER->username;
        $values['useridnumber']    = $USER->idnumber;
        $values['userfirstname']   = $USER->firstname;
        $values['userlastname']    = $USER->lastname;
        $values['userfullname']    = fullname($USER);
        $values['useremail']       = $USER->email;
        $values['usericq']         = $USER->icq;
        $values['userphone1']      = $USER->phone1;
        $values['userphone2']      = $USER->phone2;
        $values['userinstitution'] = $USER->institution;
        $values['userdepartment']  = $USER->department;
        $values['useraddress']     = $USER->address;
        $values['usercity']        = $USER->city;
        $values['usertimezone']    = get_user_timezone_offset();
        $values['userurl']         = $USER->url;
    }

    // weak imitation of Single-Sign-On, for backwards compatibility only
    // NOTE: login hack is not included in 2.0 any more, new contrib auth plugin
    //       needs to be createed if somebody needs the old functionality!
    if (!empty($config->secretphrase)) {
        $values['encryptedcode'] = url_get_encrypted_parameter($url, $config);
    }

    //hmm, this is pretty fragile and slow, why do we need it here??
    if ($config->rolesinparams) {
        $roles = get_all_roles();
        $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
        $roles = role_fix_names($roles, $coursecontext, ROLENAME_ALIAS);
        foreach ($roles as $role) {
            $values['course'.$role->shortname] = $role->localname;
        }
    }

    return $values;
}

/**
 * BC internal function
 * @param object $url
 * @param object $config
 * @return string
 */
function url_get_encrypted_parameter($url, $config) {
    global $CFG;

    if (file_exists("$CFG->dirroot/local/externserverfile.php")) {
        require_once("$CFG->dirroot/local/externserverfile.php");
        if (function_exists('extern_server_file')) {
            return extern_server_file($url, $config);
        }
    }
    return md5(getremoteaddr().$config->secretphrase);
}

/**
 * Optimised mimetype detection from general URL
 * @param $fullurl
 * @return string mimetype
 */
function url_guess_icon($fullurl) {
    global $CFG;
    require_once("$CFG->libdir/filelib.php");

    if (substr_count($fullurl, '/') < 3 or substr($fullurl, -1) === '/') {
        // most probably default directory - index.php, index.html, etc.
        return 'f/web';
    }

    $icon = mimeinfo('icon', $fullurl);
    $icon = 'f/'.str_replace(array('.gif', '.png'), '', $icon);

    if ($icon === 'f/html' or $icon === 'f/unknown') {
        $icon = 'f/web';
    }

    return $icon;
}
