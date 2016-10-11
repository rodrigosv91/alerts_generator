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

$days = required_param('days', PARAM_INT); 
$hm_time = required_param('hm_time', PARAM_TEXT); 
$msg_id = required_param('msg_id', PARAM_INT);
$ag_assignid = required_param('ag_assignid', PARAM_INT);
$subject = required_param('subject', PARAM_TEXT);
$messagetext = required_param('texto', PARAM_TEXT);
$course_id = required_param('course_id', PARAM_INT);

sscanf($hm_time, "%d:%d:", $hours, $minutes);
$alert_time =  $days*86400 + $hours * 3600 + $minutes * 60 ;

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);

$updated_ag_msg = new stdClass();
$updated_ag_msg->id = $msg_id;
$updated_ag_msg->subject = $subject;
$updated_ag_msg->message = $messagetext;
$DB->update_record('block_alerts_generator_msg', $updated_ag_msg, $bulk=false);


$updated_ag_assign = new stdClass();
$updated_ag_assign->id = $ag_assignid;
$updated_ag_assign->messageid = $msg_id;
$updated_ag_assign->alerttime = $alert_time; 

$DB->update_record('block_alerts_generator_assig', $updated_ag_assign, $bulk=false);


$mensagem = "Alerta Atualizado";
echo json_encode($mensagem);

?>