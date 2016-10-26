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

function block_alerts_generator_activate_absence_alert($absenceid) {

    $record_absence = new stdClass();
	$record_absence->id = $absenceid;
	$record_absence->alertstatus = 1;
	$result = $DB->update_record('block_alerts_generator_abs', $record_absence, $bulk=false);
	
    return $result;
}

function block_alerts_generator_deactivate_absence_alert($absenceid) {

    $record_absence = new stdClass();
	$record_absence->id = $absenceid;
	$record_absence->alertstatus = 0;
	$result = $DB->update_record('block_alerts_generator_abs', $record_absence, $bulk=false);
	
    return $result;
}

function block_alerts_generator_get_teachers($course) {
    $teachers = array();
    $context = \context_course::instance($course);
    $allteachers = get_enrolled_users($context, 'block/alerts_generator:viewpages', 0,
                    'u.id, u.firstname, u.lastname, u.email, u.suspended, u.deleted', 'firstname, lastname');
    foreach ($allteachers as $teacher) {
        if ($teacher->suspended == 0 && $teacher->deleted == 0) {
            $teachers[] = $teacher;
        }
    }
    return($teachers);
}




