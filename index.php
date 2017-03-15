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
 * View all BigBlueButton instances in this course.
 *
 * @author    Fred Dixon  (ffdixon [at] blindsidenetworks [dt] com)
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 * @copyright 2010-2017 Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once dirname(dirname(dirname(__FILE__))).'/config.php';
require_once dirname(__FILE__).'/locallib.php';

$id = required_param('id', PARAM_INT); // Course Module ID, or
$a = optional_param('a', 0, PARAM_INT); // bigbluebuttonbn instance ID
$g = optional_param('g', 0, PARAM_INT); // group instance ID

if (!$id) {
    print_error('You must specify a course_module ID or an instance ID');
}
$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_login($course, true);

$context = context_course::instance($course->id);

/// Print the header
$PAGE->set_url('/mod/bigbluebuttonbn/index.php', array('id' => $id));
$PAGE->set_title(get_string('modulename', 'bigbluebuttonbn'));
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(false);
$PAGE->set_pagelayout('incourse');

//$navigation = build_navigation(array('name' => get_string('modulename', 'bigbluebuttonbn'), 'link' => '', 'type' => 'activity'));
$PAGE->navbar->add(get_string('modulename', 'bigbluebuttonbn'), "index.php?id=$course->id");

/// Get all the appropriate data
if (!$bigbluebuttonbns = get_all_instances_in_course('bigbluebuttonbn', $course)) {
    notice('There are no instances of bigbluebuttonbn', "../../course/view.php?id=$course->id");
}

/// Print the list of instances
$timenow = time();
$strweek = get_string('week');
$strtopic = get_string('topic');
$heading_name = get_string('index_heading_name', 'bigbluebuttonbn');
$heading_group = get_string('index_heading_group', 'bigbluebuttonbn');
$heading_users = get_string('index_heading_users', 'bigbluebuttonbn');
$heading_viewer = get_string('index_heading_viewer', 'bigbluebuttonbn');
$heading_moderator = get_string('index_heading_moderator', 'bigbluebuttonbn');
$heading_actions = get_string('index_heading_actions', 'bigbluebuttonbn');
$heading_recording = get_string('index_heading_recording', 'bigbluebuttonbn');

$table = new html_table();

$table->head = array($strweek, $heading_name, $heading_group, $heading_users, $heading_viewer, $heading_moderator, $heading_recording, $heading_actions);
$table->align = array('center', 'left', 'center', 'center', 'center', 'center', 'center');

$logoutURL = $CFG->wwwroot;

$submit = optional_param('submit', '', PARAM_TEXT);
if ($submit === 'end') {

    // A request to end the meeting

    $bigbluebuttonbn = $DB->get_record('bigbluebuttonbn', array('id' => $a), '*', MUST_EXIST);
    if (!$bigbluebuttonbn) {
        print_error("BigBlueButton ID $a is incorrect");
    }
    $course = $DB->get_record('course', array('id' => $bigbluebuttonbn->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('bigbluebuttonbn', $bigbluebuttonbn->id, $course->id, false, MUST_EXIST);

    //User roles
    if ($bigbluebuttonbn->participants == null || $bigbluebuttonbn->participants == '' || $bigbluebuttonbn->participants == '[]') {
        //The room that is being used comes from a previous version
        $moderator = has_capability('mod/bigbluebuttonbn:moderate', $context);
    } else {
        $moderator = bigbluebuttonbn_is_moderator($USER->id, get_user_roles($context, $USER->id, true), $bigbluebuttonbn->participants);
    }
    $administrator = has_capability('moodle/category:manage', $context);

    if ($moderator || $administrator) {
        bigbluebuttonbn_event_log(BIGBLUEBUTTON_EVENT_MEETING_ENDED, $bigbluebuttonbn, $cm);

        echo get_string('index_ending', 'bigbluebuttonbn');

        $meetingID = $bigbluebuttonbn->meetingid.'-'.$course->id.'-'.$bigbluebuttonbn->id;
        if ($g != '0') {
            $meetingID .= '['.$g.']';
        }
        $getArray = bigbluebuttonbn_wrap_xml_load_file(bigbluebuttonbn_getEndMeetingURL($meetingID, $bigbluebuttonbn->moderatorpass));
        redirect('index.php?id='.$id);
    }
}

foreach ($bigbluebuttonbns as $bigbluebuttonbn) {
    if ($bigbluebuttonbn->visible) {
        $cm = get_coursemodule_from_id('bigbluebuttonbn', $bigbluebuttonbn->coursemodule, 0, false, MUST_EXIST);

        //User roles
        if ($bigbluebuttonbn->participants == null || $bigbluebuttonbn->participants == '' || $bigbluebuttonbn->participants == '[]') {
            //The room that is being used comes from a previous version
            $moderator = has_capability('mod/bigbluebuttonbn:moderate', $context);
        } else {
            $moderator = bigbluebuttonbn_is_moderator($USER->id, get_user_roles($context, $USER->id, true), $bigbluebuttonbn->participants);
        }
        $administrator = has_capability('moodle/category:manage', $context);

        $can_moderate = ($administrator || $moderator);

        //Add a the data for the bigbluebuttonbn instance
        $groupObj = (groups_get_activity_groupmode($cm) > 0) ? (object) array('id' => 0, 'name' => get_string('allparticipants')) : null;
        $table->data[] = bigbluebuttonbn_index_display_room($can_moderate, $course, $bigbluebuttonbn, $groupObj);

        //Add a the data for the groups belonging to the bigbluebuttonbn instance, if any
        $groups = groups_get_activity_allowed_groups($cm);
        foreach ($groups as $group) {
            $table->data[] = bigbluebuttonbn_index_display_room($can_moderate, $course, $bigbluebuttonbn, $group);
        }
    }
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('index_heading', 'bigbluebuttonbn'));
echo html_writer::table($table);

echo $OUTPUT->footer();

/// Functions
function bigbluebuttonbn_index_display_room($moderator, $course, $bigbluebuttonbn, $groupObj = null)
{
    $meetingID = $bigbluebuttonbn->meetingid.'-'.$course->id.'-'.$bigbluebuttonbn->id;
    $paramGroup = '';
    $groupName = '';

    if ($groupObj) {
        $meetingID .= '['.$groupObj->id.']';
        $paramGroup = '&group='.$groupObj->id;
        $groupName = $groupObj->name;
    }

    $meetingInfo = bigbluebuttonbn_get_meeting_info_array($meetingID);

    if (!$meetingInfo) {
        // The server was unreachable
        print_error(get_string('index_error_unable_display', 'bigbluebuttonbn'));

        return;
    }

    if (isset($meetingInfo['messageKey']) && $meetingInfo['messageKey'] == 'checksumError') {
        // There was an error returned
        print_error(get_string('index_error_checksum', 'bigbluebuttonbn'));

        return;
    }

    // Output Users in the meeting
    $joinURL = '<a href="view.php?id='.$bigbluebuttonbn->coursemodule.$paramGroup.'">'.format_string($bigbluebuttonbn->name).'</a>';
    $group = $groupName;
    $users = '';
    $viewerList = '';
    $moderatorList = '';
    $recording = '';
    $actions = '';

    // The meeting info was returned
    if ($meetingInfo['running'] == 'true') {
        $users = bigbluebuttonbn_index_display_room_users($meetingInfo);
        $viewerList = bigbluebuttonbn_index_display_room_users_attendee_list($meetingInfo, 'VIEWER');
        $moderatorList = bigbluebuttonbn_index_display_room_users_attendee_list($meetingInfo, 'MODERATOR');
        $recording = bigbluebuttonbn_index_display_room_recordings($meetingInfo);
        $actions = bigbluebuttonbn_index_display_room_actions($moderator, $course, $bigbluebuttonbn, $groupObj);
    }

    return array($bigbluebuttonbn->section, $joinURL, $group, $users, $viewerList, $moderatorList, $recording, $actions);
}

function bigbluebuttonbn_index_display_room_users($meetingInfo)
{
    $users = '';

    if (count($meetingInfo['attendees']) && count($meetingInfo['attendees']->attendee)) {
        $users = count($meetingInfo['attendees']->attendee);
    }

    return $users;
}

function bigbluebuttonbn_index_display_room_users_attendee_list($meetingInfo, $role)
{
    $attendeeList = '';

    if (count($meetingInfo['attendees']) && count($meetingInfo['attendees']->attendee)) {
        $attendee_count = 0;
        foreach ($meetingInfo['attendees']->attendee as $attendee) {
            if ($attendee->role == $role) {
                $attendeeList .= ($attendee_count++ > 0 ? ', ' : '').$attendee->fullName;
            }
        }
    }

    return $attendeeList;
}

function bigbluebuttonbn_index_display_room_recordings($meetingInfo)
{
    $recording = '';

    if (isset($meetingInfo['recording']) && $meetingInfo['recording'] === 'true') { // if it has been set when meeting created, set the variable on/off
        $recording = get_string('index_enabled', 'bigbluebuttonbn');
    }

    return $recording;
}

function bigbluebuttonbn_index_display_room_actions($moderator, $course, $bigbluebuttonbn, $groupObj = null)
{
    $actions = '';

    if ($moderator) {
        $actions .= '<form name="form1" method="post" action="">'.'/n';
        $actions .= '  <INPUT type="hidden" name="id" value="'.$course->id.'">'.'/n';
        $actions .= '  <INPUT type="hidden" name="a" value="'.$bigbluebuttonbn->id.'">'.'/n';
        if ($groupObj != null) {
            $actions .= '  <INPUT type="hidden" name="g" value="'.$groupObj->id.'">'.'/n';
        }
        $actions .= '  <INPUT type="submit" name="submit" value="end" onclick="return confirm(\''.get_string('index_confirm_end', 'bigbluebuttonbn').'\')">'.'/n';
        $actions .= '</form>'.'/n';
    }

    return $actions;
}
