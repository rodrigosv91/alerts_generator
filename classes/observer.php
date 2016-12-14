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
 * Event observer.
 *
 * @package    block_recent_activity
 * @copyright  2014 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_alerts_generator_observer {

    /**
	 * Course delete event observer.
     * Delete all data related to the course from block_alerts_generator tables.
     *
     * @param \core\event\base $event
     */	 
    public static function course_deleted(\core\event\base $event) {
        global $DB;
        
		$courseid = $event->courseid;       
			
		$sql = 	"SELECT msg.id
						FROM {block_alerts_generator_msg} AS msg																	
						WHERE msg.courseid = :course_id ";
							
		$params = array('course_id' => $courseid );		
		$result = $DB ->get_recordset_sql($sql, $params);
		
		foreach ($result  as  $rs) {					
				$DB->delete_records('block_alerts_generator_abs', array('messageid' => $rs->id ));	
				$DB->delete_records('block_alerts_generator_abs_s', array('messageid' => $rs->id ));	
				$DB->delete_records('block_alerts_generator_ans', array('messageid' => $rs->id ));
				$DB->delete_records('block_alerts_generator_ans_s', array('messageid' => $rs->id ));
				$DB->delete_records('block_alerts_generator_assig', array('messageid' => $rs->id ));	
				$DB->delete_records('block_alerts_generator_dest', array('messageid' => $rs->id ));
				$DB->delete_records('block_alerts_generator_sch_a', array('messageid' => $rs->id ));			
		}				
			
		$DB->delete_records('block_alerts_generator_abs_u', array('courseid' => $courseid));
		$DB->delete_records('block_alerts_generator_abs_z', array('courseid' => $courseid));
		$DB->delete_records('block_alerts_generator_msg', array('courseid' => $courseid));
			
    }
}
