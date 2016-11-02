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
global $DB, $USER;

$course_id = required_param('course_id', PARAM_INT); 
$days = required_param('days', PARAM_INT);

$begin_date_str = required_param('from_date', PARAM_TEXT);
$end_date_str = required_param('to_date', PARAM_TEXT);

$absence_time =  $days*86400;


if($end_date_str != null){
	//$end_dateTime = DateTime::createFromFormat("D M d Y H:i:s T", substr($end_date_str, 0,33));
	//$end_dateTime = DateTime::createFromFormat("D M d Y H:i:s T", substr($end_date_str, 0, strpos($end_date_str, 'GMT')+8));	
	//$end_date = $end_dateTime->getTimestamp();
	
	$end_date = strtotime( substr($end_date_str, 0, 33));
}else{
	$end_date = null;
}

if($begin_date_str != null){
	//$begin_dateTime = DateTime::createFromFormat("D M d Y H:i:s T", substr($begin_date_str, 0, strpos($begin_date_str, 'GMT')+8));	
	//$begin_date = $begin_dateTime->getTimestamp();
	
	$begin_date = strtotime( substr($begin_date_str, 0, 33));
}else{
	$begin_date = null;
}
	

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);

//$msg = $DB->get_record('block_alerts_generator_msg', array('courseid' => $course_id));
//$abs_count = $DB->count_records('block_alerts_generator_msg', array('messageid' => $msg->id));



$sql = "SELECT COUNT(*) FROM {block_alerts_generator_msg} msg 
			WHERE msg.courseid = :courseid 
				AND msg.id IN ( 
					SELECT abs.messageid FROM {block_alerts_generator_abs_s} abs)";
		
$abs_count = $DB->count_records_sql($sql, array('courseid' => $course_id));

if($abs_count==0){
	$recordmsg = new stdClass();
	$recordmsg->fromid = $USER->id;
	$recordmsg->subject = "";
	$recordmsg->message = "";
	$recordmsg->courseid = $course_id;
	$messageid = $DB->insert_record('block_alerts_generator_msg', $recordmsg, true);

	$record_absence = new stdClass();
	$record_absence->messageid = $messageid;
	$record_absence->absencetime = $absence_time;
	$record_absence->begin_date = $begin_date;
	$record_absence->end_date = $end_date;
	$record_absence->alertstatus = 1; 
	$db_result = $DB->insert_record('block_alerts_generator_abs_s', $record_absence, true);
}


header('Content-type: application/json');
$mensagem = array('db_result' => $db_result, 'abs_count' => $abs_count ,'msg_id' => $messageid);
echo json_encode($mensagem);

?>