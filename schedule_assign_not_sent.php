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
global $DB, $COURSE, $USER;

$course_id = required_param('course_id', PARAM_INT); 
$assign_id = required_param('assign_id', PARAM_INT);
$subject = required_param('subject', PARAM_TEXT);
$messagetext = required_param('texto', PARAM_TEXT);

$customized = required_param('customized', PARAM_INT);

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);


$asgn_count = $DB->count_records('block_alerts_generator_ans', array('assignid' => $assign_id, 'sent' => 0));

/* */
if($asgn_count==0){
	$recordmsg = new stdClass();
	$recordmsg->fromid = $USER->id;
	$recordmsg->subject = $subject;
	$recordmsg->message = $messagetext;
	$recordmsg->courseid = $course_id;
	
	$recordmsg->customized = $customized;
	
	$messageid = $DB->insert_record('block_alerts_generator_msg', $recordmsg, true);


	$record_asgn_not_sent = new stdClass();
	$record_asgn_not_sent->assignid = $assign_id;
	$record_asgn_not_sent->messageid = $messageid;
	$record_asgn_not_sent->sent = 0;

	$id_ans = $DB->insert_record('block_alerts_generator_ans', $record_asgn_not_sent, true);
}

header('Content-type: application/json');
$mensagem = array('asgn_count' => $asgn_count);

//$mensagem =  $asgn_count;
//$mensagem = "ok";
echo json_encode($mensagem);

?>

 