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
$hm_time = required_param('hm_time', PARAM_TEXT); 
$subject = required_param('subject', PARAM_TEXT);
$messagetext = required_param('texto', PARAM_TEXT);
$customized = required_param('customized', PARAM_INT);

$input_date_str = required_param('input_date', PARAM_TEXT);

sscanf($hm_time, "%d:%d:", $hours, $minutes);

$input_date = strtotime( substr($input_date_str, 0, 33));

$alert_date =  ( $input_date  ) + ( $hours * 3600 + $minutes * 60 );


/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);

$messageid  = -1;
$ag_sch_alert = -1; 

$recordmsg = new stdClass();
$recordmsg->fromid = $USER->id;
$recordmsg->subject = $subject;
$recordmsg->message = $messagetext;
$recordmsg->courseid = $course_id;

$recordmsg->customized = $customized;

//$recordmsg->timecreated = time();
$messageid = $DB->insert_record('block_alerts_generator_msg', $recordmsg, true);


$record_sch_alert = new stdClass();
$record_sch_alert->messageid = $messageid;
$record_sch_alert->alertdate = $alert_date;
$record_sch_alert->sent = 0;
$ag_sch_alert = $DB->insert_record('block_alerts_generator_sch_a', $record_sch_alert, true);


header('Content-type: application/json');
$mensagem = array('ag_sch_alert' => $ag_sch_alert, 'msg_id' => $messageid );
echo json_encode($mensagem);

?>