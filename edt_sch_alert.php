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

$hm_time = required_param('hm_time', PARAM_TEXT); 
$msg_id = required_param('msg_id', PARAM_INT);
$ag_sched_alert_id = required_param('ag_sched_alert_id', PARAM_INT);
$subject = required_param('subject', PARAM_TEXT);
$messagetext = required_param('texto', PARAM_TEXT);
$course_id = required_param('course_id', PARAM_INT);

$customized = required_param('customized', PARAM_INT);

$input_date_str = required_param('input_date', PARAM_TEXT);

sscanf($hm_time, "%d:%d:", $hours, $minutes);

$input_date = strtotime( substr($input_date_str, 0, 33));

$alert_date =  ( $input_date  ) + ( $hours * 3600 + $minutes * 60 );

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);

$updated_ag_msg = new stdClass();
$updated_ag_msg->id = $msg_id;
$updated_ag_msg->subject = $subject;
$updated_ag_msg->message = $messagetext;

$updated_ag_msg->customized = $customized;

$DB->update_record('block_alerts_generator_msg', $updated_ag_msg, $bulk=false);


$updated_ag_sch_alert = new stdClass();
$updated_ag_sch_alert->id = $ag_sched_alert_id;
$updated_ag_sch_alert->messageid = $msg_id;
$updated_ag_sch_alert->alertdate = $alert_date; 

$DB->update_record('block_alerts_generator_sch_a', $updated_ag_sch_alert, $bulk=false);


$mensagem = "Alerta Atualizado";
echo json_encode($mensagem);

?>