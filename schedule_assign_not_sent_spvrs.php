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
$anss_assign_id = required_param('anss_assign_id', PARAM_INT);

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);

$id_anss = -1;

$asgn_count = $DB->count_records('block_alerts_generator_ans_s', array('assignid' => $anss_assign_id, 'sent' => 0));

/* */
if($asgn_count==0){


	$recordmsg = new stdClass();
	$recordmsg->fromid = $USER->id;
	$recordmsg->subject = "";
	$recordmsg->message = "";
	$recordmsg->courseid = $course_id;
	$messageid = $DB->insert_record('block_alerts_generator_msg', $recordmsg, true);

	$record_asgn_not_sent = new stdClass();
	$record_asgn_not_sent->assignid = $anss_assign_id;
	$record_asgn_not_sent->messageid = $messageid;
	$record_asgn_not_sent->sent = 0;

	$id_anss = $DB->insert_record('block_alerts_generator_ans_s', $record_asgn_not_sent, true);
}

header('Content-type: application/json');
$mensagem = array('asgn_count' => $asgn_count, 'id_anss' => $id_anss );

//$mensagem =  $asgn_count;
//$mensagem = "ok";
echo json_encode($mensagem);

?>

 