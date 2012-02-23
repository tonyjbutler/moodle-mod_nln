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
 * NLN module admin settings and defaults
 *
 * @package    mod
 * @subpackage nln
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
                                                           RESOURCELIB_DISPLAY_EMBED,
                                                           RESOURCELIB_DISPLAY_FRAME,
                                                           RESOURCELIB_DISPLAY_OPEN,
                                                           RESOURCELIB_DISPLAY_NEW,
                                                           RESOURCELIB_DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_AUTO,
                                   RESOURCELIB_DISPLAY_EMBED,
                                   RESOURCELIB_DISPLAY_OPEN,
                                   RESOURCELIB_DISPLAY_POPUP,
                                  );

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configtext('nln/framesize',
        get_string('framesize', 'nln'), get_string('configframesize', 'nln'), 130, PARAM_INT));
    $settings->add(new admin_setting_configcheckbox('nln/requiremodintro',
        get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), 1));
    $settings->add(new admin_setting_configmultiselect('nln/displayoptions',
        get_string('displayoptions', 'nln'), get_string('configdisplayoptions', 'nln'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('nlnmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox_with_advanced('nln/printheading',
        get_string('printheading', 'nln'), get_string('printheadingexplain', 'nln'),
        array('value'=>0, 'adv'=>false)));
    $settings->add(new admin_setting_configcheckbox_with_advanced('nln/printintro',
        get_string('printintro', 'nln'), get_string('printintroexplain', 'nln'),
        array('value'=>1, 'adv'=>false)));
    $settings->add(new admin_setting_configselect_with_advanced('nln/display',
        get_string('displayselect', 'nln'), get_string('displayselectexplain', 'nln'),
        array('value'=>RESOURCELIB_DISPLAY_AUTO, 'adv'=>false), $displayoptions));
    $settings->add(new admin_setting_configtext_with_advanced('nln/popupwidth',
        get_string('popupwidth', 'nln'), get_string('popupwidthexplain', 'nln'),
        array('value'=>620, 'adv'=>true), PARAM_INT, 7));
    $settings->add(new admin_setting_configtext_with_advanced('nln/popupheight',
        get_string('popupheight', 'nln'), get_string('popupheightexplain', 'nln'),
        array('value'=>450, 'adv'=>true), PARAM_INT, 7));
}
