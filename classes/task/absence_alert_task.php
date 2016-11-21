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



namespace block_alerts_generator\task;

class absence_alert_task extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('alertgeneratortask', 'block_alerts_generator');
    }
    /**
     * Run task.
     */
    public function execute() {
        global $CFG, $DB;

		mtrace('My absence_alert_task is working');	
		
		$sql = "SELECT 	abs.absencetime, 
						abs.messageid,
						(UNIX_TIMESTAMP(NOW())- abs.absencetime) AS limit_date,
						msg.fromid, 
						msg.subject, 
						msg.message, 
						msg.courseid, 
						msg.customized,
						crs.fullname
						FROM {block_alerts_generator_abs} AS abs 
						INNER JOIN {block_alerts_generator_msg} AS msg ON abs.messageid = msg.id
						INNER JOIN {course} crs ON crs.id = msg.courseid 
						WHERE (UNIX_TIMESTAMP(NOW())) > abs.begin_date AND (UNIX_TIMESTAMP(NOW())) <= abs.end_date
						OR (UNIX_TIMESTAMP(NOW())) > abs.begin_date AND abs.end_date IS NULL 
                        OR abs.begin_date IS NULL AND (UNIX_TIMESTAMP(NOW())) <= abs.end_date 
                        OR abs.begin_date IS NULL AND abs.begin_date IS NULL";
		
		$result = $DB->get_recordset_sql($sql);
		
		//echo '<pre>'; print_r($result); echo '</pre>';
		
		foreach ($result  as  $rs) { 
			$context = \context_course::instance($rs->courseid);
			//mtrace('limit date: ' .$rs->limit_date );
			 
			$students_notAbsSql = "SELECT 	u.id
											FROM {role_assignments} AS ra
											INNER JOIN {user} AS u ON ra.userid = u.id				
											AND ra.contextid = :contextid 
											AND ra.roleid = 5 
											AND u.deleted = 0 
											AND u.suspended = 0 							
											AND u.id IN(
												SELECT absu.userid 
													FROM {block_alerts_generator_abs_u} AS absu 
													WHERE absu.courseid = :courseid
											)							
											INNER JOIN {user_lastaccess} AS ula ON ula.userid = u.id  
											AND  ula.courseid = :courseidx 								
											WHERE ula.timeaccess > :limit_date ";
							
			$students_notAbsSqlparams = array('courseid' => $rs->courseid, 'courseidx' => $rs->courseid, 'contextid' => $context->id, 'limit_date' => $rs->limit_date );		
			$users_notAbs = $DB ->get_recordset_sql($students_notAbsSql, $students_notAbsSqlparams); 

			//mtrace('sql 2: ' .$students_notAbsSql );
			
			foreach ($users_notAbs  as  $una) {					
				$DB->delete_records('block_alerts_generator_abs_u', array('userid' => $una->id, 'courseid' => $rs->courseid ));					
			}
			
			$users_notAbs->close();
				
			$students_absSql = "SELECT 	u.id, 
										u.firstname, 
										u.lastname, 
										COALESCE(ula.timeaccess,0) AS timeaccess 
										FROM {role_assignments} AS ra
										INNER JOIN {user} AS u ON ra.userid = u.id				
										AND ra.contextid = :contextid 
										AND ra.roleid = 5 
										AND u.deleted = 0 
										AND u.suspended = 0 								
										AND u.id NOT IN(
											SELECT absu.userid 
												FROM {block_alerts_generator_abs_u} AS absu 
												WHERE absu.courseid = :courseid
										)							
										LEFT JOIN {user_lastaccess} AS ula ON ula.userid = u.id  
										AND  ula.courseid = :courseidx								
										WHERE ula.timeaccess <= :limit_date OR ula.timeaccess IS NULL";	
			
			$students_absSqlparams = array('courseid' => $rs->courseid, 'courseidx' => $rs->courseid, 'contextid' => $context->id, 'limit_date' => $rs->limit_date );			
			$students_abs = $DB ->get_recordset_sql($students_absSql, $students_absSqlparams); 
			
			$fromuser = new \stdClass();
			$fromuser = $DB->get_record('user', array('id' => $rs->fromid));
			
			foreach ($students_abs   as  $std) { 
				//mtrace("student id: ". $std->id);
			
				$touser = new \stdClass();
				$touser = $DB->get_record('user', array('id' => $std->id)); 					
				
				$message = 	$rs->message;
				
				if( ($rs->customized) > 0){
					$message = str_replace("{user_first_name}", $touser->firstname, $message);					
				}	
				email_to_user($touser, $fromuser, $rs->subject, $message, $message, '', '', true);
				
				//email_to_user($touser, $fromuser, $rs->subject, $rs->message, $rs->message, '', '', true);
				
				$ag_abs_u = new \stdClass();
				$ag_abs_u->userid = $std->id;
				$ag_abs_u->courseid = $rs->courseid;
				$ag_abs_u->lastaccess = $std->timeaccess;
				$DB->insert_record('block_alerts_generator_abs_u', $ag_abs_u, false);
				
				$ag_dest = new \stdClass();
				$ag_dest->messageid = $rs->messageid;
				$ag_dest->toid = $std->id;
				$ag_dest->timecreated = time();
				$DB->insert_record('block_alerts_generator_dest', $ag_dest, false);
			}	
			
			$students_abs->close();
									
		}			
		$result->close();	
    } 
}