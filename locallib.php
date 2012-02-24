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
 * Private NLN module utility functions
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
        echo $OUTPUT->heading(format_string($nln->name), 2, 'main', 'resourceheading');
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
            echo $OUTPUT->box_start('mod_introbox', 'resourceintro');
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
        $width  = empty($options['popupwidth'])  ? 850 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 540 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $extra = "onclick=\"window.open('$jsfullurl', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $extra = "onclick=\"this.target='_blank';\"";

    } else {
        $extra = '';
    }

    echo '<div class="resourceworkaround">';
    print_string('clicktoopen', 'nln', "<a href=\"$fullurl\" $extra>$nln->name</a>");
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

    $mimetype = 'text/html';
    $fullurl  = nln_get_full_url($nln);
    $title    = $nln->name;

    $link = html_writer::tag('a', $title, array('href'=>str_replace('&amp;', '&', $fullurl)));
    $clicktoopen = get_string('clicktoopen', 'nln', $link);

    $code = resourcelib_embed_general($fullurl, $title, $clicktoopen, $mimetype);

    nln_print_header($nln, $cm, $course);
    nln_print_heading($nln, $cm, $course);

    echo $code;

    nln_print_intro($nln, $cm, $course);

    echo $OUTPUT->footer();
    die;
}

/**
 * Decide the best display format.
 * @param object $nln
 * @return int display type constant
 */
function nln_get_final_display_type($nln) {
    global $CFG;

    if ($nln->display != RESOURCELIB_DISPLAY_AUTO) {
        return $nln->display;
    }

    return RESOURCELIB_DISPLAY_POPUP;
}
