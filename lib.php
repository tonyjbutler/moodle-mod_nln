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
 * Mandatory public API of NLN module
 *
 * @package    mod
 * @subpackage nln
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in NLN module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function nln_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function nln_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function nln_reset_userdata($data) {
    return array();
}

/**
 * List of view style log actions
 * @return array
 */
function nln_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List of update style log actions
 * @return array
 */
function nln_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add nln instance.
 * @param object $data
 * @param object $mform
 * @return int new nln instance id
 */
function nln_add_instance($data, $mform) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/nln/locallib.php');

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printheading'] = (int)!empty($data->printheading);
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);

    $data->timemodified = time();
    $data->id = $DB->insert_record('nln', $data);

    return $data->id;
}

/**
 * Update nln instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function nln_update_instance($data, $mform) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/nln/locallib.php');

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printheading'] = (int)!empty($data->printheading);
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);

    $data->timemodified = time();
    $data->id           = $data->instance;

    $DB->update_record('nln', $data);

    return true;
}

/**
 * Delete nln instance.
 * @param int $id
 * @return bool true
 */
function nln_delete_instance($id) {
    global $DB;

    if (!$nln = $DB->get_record('nln', array('id'=>$id))) {
        return false;
    }

    // note: all context files are deleted automatically

    $DB->delete_records('nln', array('id'=>$nln->id));

    return true;
}

/**
 * Return use outline
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $nln
 * @return object|null
 */
function nln_user_outline($course, $user, $mod, $nln) {
    global $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'nln',
                                              'action'=>'view', 'info'=>$nln->id), 'time ASC')) {

        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new stdClass();
        $result->info = get_string('numviews', '', $numviews);
        $result->time = $lastlog->time;

        return $result;
    }
    return NULL;
}

/**
 * Return use complete
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $nln
 */
function nln_user_complete($course, $user, $mod, $nln) {
    global $CFG, $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'nln',
                                              'action'=>'view', 'info'=>$nln->id), 'time ASC')) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strmostrecently = get_string('mostrecently');
        $strnumviews = get_string('numviews', '', $numviews);

        echo "$strnumviews - $strmostrecently ".userdate($lastlog->time);

    } else {
        print_string('neverseen', 'nln');
    }
}

/**
 * Returns the users with data in one nln
 *
 * @todo: deprecated - to be deleted in 2.2
 *
 * @param int $nlnid
 * @return bool false
 */
function nln_get_participants($nlnid) {
    return false;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return object info
 */
function nln_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/nln/locallib.php");

    if (!$nln = $DB->get_record('nln', array('id'=>$coursemodule->instance),
            'id, name, display, displayoptions, loid, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $nln->name;

    $display = nln_get_final_display_type($nln);

    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $fullurl = "$CFG->wwwroot/mod/nln/view.php?id=$coursemodule->id&amp;redirect=1";
        $options = empty($nln->displayoptions) ? array() : unserialize($nln->displayoptions);
        $width  = empty($options['popupwidth'])  ? 840 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 530 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $info->onclick = "window.open('$fullurl', '', '$wh'); return false;";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $fullurl = "$CFG->wwwroot/mod/nln/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->onclick = "window.open('$fullurl'); return false;";

    }

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('nln', $nln, $coursemodule->id, false);
    }

    return $info;
}

/**
 * This function extends the global navigation for the site.
 * It is important to note that you should not rely on PAGE objects within this
 * body of code as there is no guarantee that during an AJAX request they are
 * available
 *
 * @param navigation_node $navigation The nln node within the global navigation
 * @param stdClass $course The course object returned from the DB
 * @param stdClass $module The module object returned from the DB
 * @param stdClass $cm The course module instance returned from the DB
 */
function nln_extend_navigation($navigation, $course, $module, $cm) {
    /**
     * This is currently just a stub so that it can be easily expanded upon.
     * When expanding just remove this comment and the line below and then add
     * you content.
     */
    $navigation->nodetype = navigation_node::NODETYPE_LEAF;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function nln_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-nln-*'=>get_string('page-mod-nln-x', 'nln'));
    return $module_pagetype;
}

/**
 * Export NLN resource contents
 *
 * @return array of file content
 */
function nln_export_contents($cm, $baseurl) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/nln/locallib.php");
    $contents = array();
    $context = context_module::instance($cm->id);

    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $nln = $DB->get_record('nln', array('id'=>$cm->instance), '*', MUST_EXIST);

    $fullurl = str_replace('&amp;', '&', nln_get_full_url($nln));
    $isurl = clean_param($fullurl, PARAM_URL);
    if (empty($isurl)) {
        return null;
    }

    $nln = array();
    $nln['type'] = 'nln';
    $nln['filename']     = $nln->name;
    $nln['filepath']     = null;
    $nln['filesize']     = 0;
    $nln['fileurl']      = $fullurl;
    $nln['timecreated']  = null;
    $nln['timemodified'] = $nln->timemodified;
    $nln['sortorder']    = null;
    $nln['userid']       = null;
    $nln['author']       = null;
    $nln['license']      = null;
    $contents[] = $nln;

    return $contents;
}
