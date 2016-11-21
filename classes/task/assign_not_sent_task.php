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

class assign_not_sent_task extends \core\task\scheduled_task {
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

		mtrace('My assign_not_sent_task is working');	
		
		$sql = "SELECT 	asgn_ns.messageid, 
						asgn_ns.id, 
						asgn_ns.assignid 
						FROM {block_alerts_generator_ans} AS asgn_ns
						INNER JOIN {assign} AS a ON asgn_ns.assignid = a.id 
						WHERE a.duedate <= UNIX_TIMESTAMP(NOW()) 
						AND asgn_ns.sent = 0";
		
		$result = $DB->get_recordset_sql($sql);
		
		//echo '<pre>'; print_r($result); echo '</pre>';
		
		foreach ($result  as  $rs) { 
			
			$record_msg = $DB->get_record('block_alerts_generator_msg', array('id' => $rs->messageid));
			
			//mtrace("assign id: ". $rs->assignid);
			
			$context = \context_course::instance($record_msg->courseid);
			
			//echo ("context ".$context->id);
			
			$query_std = 	"SELECT u.id 
								FROM {role_assignments} AS a, {user} AS u 
								WHERE contextid = ". $context->id . " AND roleid = 5 AND a.userid=u.id 
								AND u.deleted = 0 
								AND u.suspended = 0 
								AND a.userid NOT IN( 
											SELECT userid 
												FROM {assign_submission} 
												WHERE assignment = ".$rs->assignid. " )";
			
			$students = $DB->get_recordset_sql($query_std);
					
			$fromuser = new \stdClass();
			$fromuser = $DB->get_record('user', array('id' => $record_msg->fromid));  
						
			//echo '<pre>'; print_r($students); echo '</pre>';
							
			foreach ($students   as  $std) { 

				//mtrace("student id: ". $std->id);
			
				$touser = new \stdClass();
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
			
			$ag_assign_not_sent = new \stdClass();
			$ag_assign_not_sent->id = $rs->id;
			$ag_assign_not_sent->sent = 1;
			$DB->update_record('block_alerts_generator_ans', $ag_assign_not_sent, $bulk=false); // mark message as sent 
		}	
		
		$result->close();	
    } 
}