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

class scheduled_alert_task extends \core\task\scheduled_task {
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

		mtrace('My scheduled_alert_task is working ');			
							
		$sql = "	SELECT 	sch_a.messageid, 
						sch_a.id		
						FROM {block_alerts_generator_sch_a} AS sch_a 					
						WHERE (sch_a.alertdate) <= UNIX_TIMESTAMP(NOW()) 
						AND sch_a.sent = 0";
		
		$result = $DB->get_recordset_sql($sql);
		
		foreach ($result  as  $rs) { 
			
			$record_msg = $DB->get_record('block_alerts_generator_msg', array('id' => $rs->messageid));

			$context = \context_course::instance($record_msg->courseid);
			
			$query_std = 	"SELECT u.id 
									FROM {role_assignments} AS a
                                    INNER JOIN {user} AS u 
									WHERE a.contextid = ". $context->id . "
									AND roleid = 5 
									AND a.userid = u.id 
									AND u.deleted = 0 
									AND u.suspended = 0";
			
			$students = $DB->get_recordset_sql($query_std);
								
			$fromuser = new \stdClass();
			$fromuser = $DB->get_record('user', array('id' => $record_msg->fromid));  
							
			foreach ($students   as  $std) { 
				$touser = new \stdClass();
				//$touser->mailformat = 0;
				//$touser->id = $std->id; 
				//$touser->email = $DB->get_field('user', 'email', array('id' => $std->id));  
				$touser = $DB->get_record('user', array('id' => $std->id, 'deleted' => 0), '*', MUST_EXIST); 
					
				$message = 	$record_msg->message;
				
				
				if( ($record_msg->customized) > 0){
					$message = str_replace("{user_first_name}", $touser->firstname, $message);					
				}
				email_to_user($touser, $fromuser, $record_msg->subject, $message, $message, '', '', true);
				
				
				
				$ag_dest = new \stdClass();
				$ag_dest->messageid = $rs->messageid;
				$ag_dest->toid = $std->id;
				$ag_dest->timecreated = time();
				$DB->insert_record('block_alerts_generator_dest', $ag_dest, false);
			}	
			
			$students->close(); 
			
			$ag_scheduled_alert = new \stdClass();
			$ag_scheduled_alert->id = $rs->id;
			$ag_scheduled_alert->sent = 1;
			$DB->update_record('block_alerts_generator_sch_a', $ag_scheduled_alert, $bulk=false); // mark message as sent 
			
		}	
		
		$result->close(); // close result get_recordset_sql
		
    }
    
}
