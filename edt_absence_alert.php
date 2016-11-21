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

require('../../config.php');
global $DB;

//$messageid = required_param('id_msg', PARAM_INT); 
//$absenceid = required_param('id_abs', PARAM_INT); 

$messageid = $SESSION->block_alerts_generator->id_msg_abs; 
$absenceid = $SESSION->block_alerts_generator->id_abs;

$course_id = required_param('course_id', PARAM_INT); 
$days = required_param('days', PARAM_INT);
$subject = required_param('subject', PARAM_TEXT);
$messagetext = required_param('texto', PARAM_TEXT);

$begin_date_str = required_param('from_date', PARAM_TEXT);
$end_date_str = required_param('to_date', PARAM_TEXT);

$customized = required_param('customized', PARAM_INT);

$absence_time =  $days*86400;

if($end_date_str != null){
	$end_date = strtotime( substr($end_date_str, 0, 33));
}else{
	$end_date = null;
}

if($begin_date_str != null){
	$begin_date = strtotime( substr($begin_date_str, 0, 33));
}else{
	$begin_date = null;
}

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);

//$abs_count = -1;
$sql = "SELECT COUNT(*) FROM {block_alerts_generator_msg} msg 
			WHERE msg.courseid = :courseid 
				AND msg.id IN ( 
					SELECT abs.messageid FROM {block_alerts_generator_abs} abs)";
		
$abs_count = $DB->count_records_sql($sql, array('courseid' => $course_id));

//echo ("<script>console.log( 'Errinho: ' );</script>");

if($abs_count>0){

/* */
$recordmsg = new stdClass();
$recordmsg->id = $messageid;
$recordmsg->fromid = $USER->id;
$recordmsg->subject = $subject;
$recordmsg->message = $messagetext;
$recordmsg->courseid = $course_id;

$recordmsg->customized = $customized;

$DB->update_record('block_alerts_generator_msg', $recordmsg, $bulk=false);

$record_absence = new stdClass();
$record_absence->id = $absenceid;
$record_absence->messageid = $messageid;
$record_absence->absencetime = $absence_time;
$record_absence->begin_date = $begin_date;
$record_absence->end_date = $end_date;	

$db_result = $DB->update_record('block_alerts_generator_abs', $record_absence, $bulk=false);

}
header('Content-type: application/json');

$mensagem = array( 'db_result' => $db_result, 'abs_count' => $abs_count, 'msg_id' => $messageid, 'abs_id' => $absenceid );
echo json_encode($mensagem);

?>