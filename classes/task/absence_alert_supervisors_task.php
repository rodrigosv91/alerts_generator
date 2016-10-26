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

class absence_alert_supervisors_task extends \core\task\scheduled_task {
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
		//require('lib.php');
        global $CFG, $DB;

		mtrace('My absence_alert_supervisors_task is working');	
		
		$sql = "SELECT 	abs.absencetime, 
						abs.messageid,
						(UNIX_TIMESTAMP(NOW())- abs.absencetime) AS limit_date,
						msg.id AS msg_id, 
						msg.fromid, 
						msg.courseid, 
						crs.fullname AS coursename
						FROM {block_alerts_generator_abs_s} AS abs 
						INNER JOIN {block_alerts_generator_msg} AS msg ON abs.messageid = msg.id
						INNER JOIN {course} crs ON crs.id = msg.courseid 
						AND abs.alertstatus = 1";
		
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
													FROM {block_alerts_generator_abs_z} AS absu 
													WHERE absu.courseid = :courseid
											)							
											INNER JOIN {user_lastaccess} AS ula ON ula.userid = u.id  
											AND  ula.courseid = :courseidx 								
											WHERE ula.timeaccess > :limit_date ";
							
			$students_notAbsSqlparams = array('courseid' => $rs->courseid, 'courseidx' => $rs->courseid, 'contextid' => $context->id, 'limit_date' => $rs->limit_date );		
			$users_notAbs = $DB ->get_recordset_sql($students_notAbsSql, $students_notAbsSqlparams); 

			//mtrace('sql 2: ' .$students_notAbsSql );
			
			foreach ($users_notAbs  as  $una) {					
				$DB->delete_records('block_alerts_generator_abs_z', array('userid' => $una->id, 'courseid' => $rs->courseid ));					
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
												FROM {block_alerts_generator_abs_z} AS absu 
												WHERE absu.courseid = :courseid
										)							
										LEFT JOIN {user_lastaccess} AS ula ON ula.userid = u.id  
										AND  ula.courseid = :courseidx								
										WHERE ula.timeaccess <= :limit_date OR ula.timeaccess IS NULL";	
			
			$students_absSqlparams = array('courseid' => $rs->courseid, 'courseidx' => $rs->courseid, 'contextid' => $context->id, 'limit_date' => $rs->limit_date );			
			$students_abs = $DB ->get_recordset_sql($students_absSql, $students_absSqlparams); 
				
			// update string ->				
			$subject_msg = get_string('abss_subject', 'block_alerts_generator') . $rs->coursename;	//nos ultimos x dias
			$text_msg =  get_string('abss_text_msg', 'block_alerts_generator')  . $rs->coursename . ": ";	//nos ultimos x dias	
			$i = 0;
			
			foreach ($students_abs   as  $std) { 
				//mtrace("student id: ". $std->id);
											
				//$url = $CFG->wwwroot . '/user/profile.php?id=' . $std->id;	
				$fullname = $std->firstname. " " . 	$std->lastname;	
				//$profilelink = '<strong>'. \html_writer::link($url, $fullname, array('target' => '_blank')) . '</strong>';
				//$text_msg = $text_msg . "<br />" . $profilelink ;
				//$text_msg = $text_msg . "\r\n" . $fullname ;
				$text_msg = " " .  $text_msg . " " . $fullname ;
				
				if(end($std)){
					$text_msg = $text_msg . ",";
				}else{
					$text_msg = $text_msg . ".";
				}
				
				//echo nl2br("Hello, world!\n Hello, world!");							
				
				$ag_abs_u = new \stdClass();
				$ag_abs_u->userid = $std->id;
				$ag_abs_u->courseid = $rs->courseid;
				$ag_abs_u->lastaccess = $std->timeaccess;
				$DB->insert_record('block_alerts_generator_abs_z', $ag_abs_u, false);
				
				$i++;
			}			
			
			$text_msg = nl2br($text_msg);
			
			if($i){	
			
			$fromuser = new \stdClass();
			$fromuser = $DB->get_record('user', array('id' => $rs->fromid));
	
			/**  */	
			//get supervisors ans send message
			$allsupervisors = get_enrolled_users($context, 'block/alerts_generator:viewpages', 0,
								'u.id, u.firstname, u.lastname, u.email, u.suspended, u.deleted, u.username', 'firstname, lastname');
								
			foreach ($allsupervisors as $spvrs) {
				if ($spvrs->suspended == 0 && $spvrs->deleted == 0) {
					
					$touser = $DB->get_record('user', array('id' => $spvrs->id));
					//$spvrs->mailformat = 1;
					email_to_user($spvrs, $fromuser, $subject_msg, $text_msg, $text_msg, '', '', true);
					
					$ag_dest = new \stdClass();
					$ag_dest->messageid = $rs->messageid;
					$ag_dest->toid = $std->id;
					$ag_dest->timecreated = time();
					$DB->insert_record('block_alerts_generator_dest', $ag_dest, false);
								
				}
			}	
			
			}	
			$students_abs->close();	
				
			//alunos sem acesso em "curso"
		}			
		$result->close();	
    } 
}