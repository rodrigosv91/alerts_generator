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

//$absenceid = $SESSION->block_alerts_generator->id_abs; 
$course_id = $SESSION->block_alerts_generator->course_id_abs;

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);

$abs_count = $DB->count_records('block_alerts_generator_abs_u', array('courseid' => $course_id));

if($abs_count > 0 ){
	$DB->delete_records('block_alerts_generator_abs_u', array('courseid' => $course_id));
	
	$abs_count = $DB->count_records('block_alerts_generator_abs_u', array('courseid' => $course_id));
}
else{
	if($abs_count == 0){
			//nothing to delete
	}else{
		$abs_count = -1;
	}
}

header('Content-type: application/json');

$mensagem = array( 'abs_count' => $abs_count); //, 'msg_id' => $messageid, 'abs_id' => $absenceid
echo json_encode($mensagem);

?>