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
 * NLN module main user interface
 *
 * @package    mod
 * @subpackage nln
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/nln/locallib.php");
require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // NLN instance id
$redirect = optional_param('redirect', 0, PARAM_BOOL);

if ($u) {  // Two ways to specify the module
    $nln = $DB->get_record('nln', array('id'=>$u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('nln', $nln->id, $nln->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('nln', $id, 0, false, MUST_EXIST);
    $nln = $DB->get_record('nln', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/nln:view', $context);

add_to_log($course->id, 'nln', 'view', 'view.php?id='.$cm->id, $nln->id, $cm->id);

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/nln/view.php', array('id' => $cm->id));

// Make sure NLN Learning Object ID exists before generating output
$loid = trim($nln->loid);
if (empty($loid)) {
    nln_print_header($nln, $cm, $course);
    nln_print_heading($nln, $cm, $course);
    nln_print_intro($nln, $cm, $course);
    notice(get_string('invalidstorednln', 'mod_nln'), new moodle_url('/course/view.php', array('id'=>$cm->course)));
    die;
}
unset($loid);

$displaytype = nln_get_final_display_type($nln);
if ($displaytype == RESOURCELIB_DISPLAY_OPEN) {
    // For 'open' links, we always redirect to the content - except if the user
    // just chose 'save and display' from the form then that would be confusing
    if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'modedit.php') === false) {
        $redirect = true;
    }
}

if ($redirect) {
    // coming from course page or nln index page,
    // the redirection is needed for completion tracking and logging
    $fullurl = nln_get_full_url($nln);
    redirect(str_replace('&amp;', '&', $fullurl));
}

switch ($displaytype) {
    case RESOURCELIB_DISPLAY_EMBED:
        nln_display_embed($nln, $cm, $course);
        break;
    case RESOURCELIB_DISPLAY_FRAME:
        nln_display_frame($nln, $cm, $course);
        break;
    default:
        nln_print_workaround($nln, $cm, $course);
        break;
}
