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

class assign_not_sent_supervisors_task extends \core\task\scheduled_task {
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
		require('lib.php');
        global $CFG, $DB;

		mtrace('My assign not sent spvrs is working');	
		
		$sql = "SELECT asgn_ns.messageid, 
						asgn_ns.id, 
						asgn_ns.assignid,
						a.name,
                        msg.fromid,
						msg.courseid
						FROM {block_alerts_generator_ans_s} AS asgn_ns
						INNER JOIN {assign} AS a ON asgn_ns.assignid = a.id                  
						AND a.duedate <= UNIX_TIMESTAMP(NOW()) 
						AND asgn_ns.sent = 0
                        INNER JOIN {block_alerts_generator_msg} AS msg ON asgn_ns.messageid = msg.id";
		
		$result = $DB->get_recordset_sql($sql);
		
		//echo '<pre>'; print_r($result); echo '</pre>';
		
		foreach ($result  as   $rs) { 
					
			//mtrace("assign id: ". $rs->assignid);
			
			//echo nl2br("Hello, world!\n Hello, world!");
			
			$context = \context_course::instance($rs->courseid);
			
			//echo ("context ".$context->id);
				
			$query_std = "SELECT 	u.id, 
									u.firstname,
									u.lastname
									FROM {role_assignments} AS ra 
                                    INNER JOIN {user} AS u ON ra.userid = u.id   
									AND ra.roleid = 5 
									AND ra.contextid = ". $context->id . "
									AND u.deleted = 0 AND u.suspended = 0 
									AND u.id NOT IN( 
												SELECT userid 
													FROM {assign_submission} 
													WHERE assignment = " .$rs->assignid. " )";
			
			$students = $DB->get_recordset_sql($query_std);
								
			//echo '<pre>'; print_r($students); echo '</pre>';
			
			$subject_msg = "";
			$text_msg = "";

			$i = 0;
			
			//if($students_abs->valid()){ $i = 1;}
						
			foreach ($students   as   $std) { 
				
				$fullname = $std->firstname . " " . 	$std->lastname;		
					
				//$url = $CFG->wwwroot . '/user/profile.php?id=' . $std->id;								
				//$profilelink = '<strong>'. \html_writer::link($url, $fullname, array('target' => '_blank')) . '</strong>';
				//$text_msg = $text_msg . "<br />" . $profilelink ;
							
				//$text_msg = " " .  $text_msg . " " . $fullname ;
				
				if($i==0){	
					$text_msg = $text_msg . " " . $fullname ;	
					
				}					
				else{ 
					$text_msg =  $text_msg . ", " . $fullname ;	
				}
				
				$i++;
			}
			$text_msg = $text_msg . ".";
			
			$students->close(); 											
			
			if($i){	
				
				$subject_msg = get_string('anss_subject', 'block_alerts_generator') . $rs->name ;
				
				if($i == 1){
					$text_msg = get_string('anss_text_msg_1', 'block_alerts_generator') . $rs->name . ": " . $text_msg;
				}
				else{
					$text_msg = get_string('anss_text_msg_2', 'block_alerts_generator') . $rs->name . ": " . $text_msg;
				}
				
				$text_msg = nl2br($text_msg);
				
				$fromuser = new \stdClass();
				$fromuser = $DB->get_record('user', array('id' => $rs->fromid));
	
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
			
			//update message
			$record_ag_msg = new \stdClass();
			$record_ag_msg->id = $rs->messageid;			
			$record_ag_msg->subject = $subject_msg;
			$record_ag_msg->message = $text_msg;		
			$DB->update_record('block_alerts_generator_msg', $record_ag_msg, $bulk=false);
						
			//update ag_ans_s
			$ag_assign_not_sent_sup = new \stdClass();
			$ag_assign_not_sent_sup->id = $rs->id;
			$ag_assign_not_sent_sup->messageid = $rs->messageid;
			$ag_assign_not_sent_sup->sent = 1;
			$DB->update_record('block_alerts_generator_ans_s', $ag_assign_not_sent_sup, $bulk=false); // mark message as sent 
		}	
		
		$result->close();	
    } 
}