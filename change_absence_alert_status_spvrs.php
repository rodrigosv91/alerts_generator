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
//require('lib.php');
global $DB;

$course_id = required_param('course_id', PARAM_INT); 
$action = required_param('action', PARAM_INT);
$absenceid = $SESSION->block_alerts_generator_spvrs->id_abs_spvrs;

$result = -1;

if($action == 1){
	
	$record_absence = new stdClass();
	$record_absence->id = $absenceid;
	$record_absence->alertstatus = 1;
	$result = $DB->update_record('block_alerts_generator_abs_s', $record_absence, $bulk=false);

}

if($action == 2){
	
	$record_absence = new stdClass();
	$record_absence->id = $absenceid;
	$record_absence->alertstatus = 0;
	$result = $DB->update_record('block_alerts_generator_abs_s', $record_absence, $bulk=false);
}

header('Content-type: application/json');
$mensagem = array( 'result' => $result);
echo json_encode($mensagem);

?>

