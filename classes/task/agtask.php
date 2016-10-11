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

class agtask extends \core\task\scheduled_task {
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

		mtrace('my plugin is working 2');
		
		/** 
		$query = 'SELECT UNIX_TIMESTAMP(NOW()) as unix_date, NOW() as date_now FROM DUAL ';	
		$result = $DB->get_recordset_sql($query);
		//** 
		foreach ($result  as  $rs) { 
			$unix = $rs->unix_date;
			$date_now = $rs->date_now;
		}	
		//*/
		//echo $unix;
		//echo $date_now;
		
		//$texto = " A data atual é: " . $date_now;
		
		//** 
		//$touser = new \stdClass();
		//$touser->mailformat = 0;
		//$touser->id = 2; 
		//$touser->email = $DB->get_field('user', 'email', array('id' => 2));  
		//email_to_user($touser, "alguem", "teste subj", $texto, "msg teste 2", '', '', true);
		
		
		$sql = "SELECT asgn.messageid, asgn.id, asgn.assignid FROM {block_alerts_generator_assig} AS asgn 
							INNER JOIN {assign} AS a ON asgn.assignid = a.id 
							WHERE (a.duedate - asgn.alerttime) <= UNIX_TIMESTAMP(NOW()) AND asgn.sent = 0";
		
		$result = $DB->get_recordset_sql($sql);
		
		foreach ($result  as  $rs) { 
			
			//$records = get_records_select('block_alerts_generator_msg', 'fromid,subject,message,courseid');
			//$DB->get_records_select($table, id=$rs->messageid ,null,'', 'fromid,subject,message,courseid') 
			$record = $DB->get_record('block_alerts_generator_msg', array('id' => $rs->messageid));
			
			//$context = \get_context_instance(\CONTEXT_COURSE, $record->courseid);
			$context = \context_course::instance($record->courseid);
			//$students = \get_role_users(5 , $context);
			
			//mtrace($context->id );
			
			//$stdok = $DB->get_records_select('assign_submission', 'assignment = 3', null, $sort='', 'userid'); 
			
			$query_std = "SELECT u.id FROM {role_assignments} AS a, {user} AS u 
					WHERE contextid = ". $context->id . " AND roleid = 5 AND a.userid=u.id 
					AND u.deleted = 0 AND u.suspended = 0 AND a.userid NOT IN( SELECT userid FROM {assign_submission} 
					WHERE assignment = ".$rs->assignid. " )";
			
			$students = $DB->get_recordset_sql($query_std);
			
			//foreach ($students  as  $std) { 
			
			//email_to_user($touser, "alguem ", $record->subject, $record->message, "msg teste 2", '', '', true);
					
			$fromuser = new \stdClass();
			$fromuser = $DB->get_record('user', array('id' => $record->fromid));  
							
			foreach ($students   as  $std) { 
				//mtrace($std->id);
				$touser = new \stdClass();
				//$touser->mailformat = 0;
				//$touser->id = $std->id; 
				//$touser->email = $DB->get_field('user', 'email', array('id' => $std->id));  
				$touser = $DB->get_record('user', array('id' => $std->id)); 
					
				email_to_user($touser, $fromuser, $record->subject, $record->message, $record->message, '', '', true);
				
				$ag_dest = new \stdClass();
				$ag_dest->messageid = $rs->messageid;
				$ag_dest->toid = $std->id;
				$ag_dest->timecreated = time();
				$DB->insert_record('block_alerts_generator_dest', $ag_dest, false);
			}	
			
			$students->close(); 
			
			$ag_assign = new \stdClass();
			$ag_assign->id = $rs->id;
			$ag_assign->sent = 1;
			$DB->update_record('block_alerts_generator_assig', $ag_assign, $bulk=false); // mark message as sent 
			
		
				 

		}	
		
		$result->close(); // close result get_recordset_sql
		
        }
    
}