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


class block_alerts_generator extends block_list {

	
    function init() {
        $this->title = get_string('pluginname', 'block_alerts_generator');
    }
	
    function get_content() {
     
		global $COURSE, $DB,  $CFG;;
		
		
		//$context = get_context_instance( CONTEXT_COURSE, $COURSE->id ); 
		
		//$query = 'select u.id as id, firstname, lastname from mdl_role_assignments as a, mdl_user as u where contextid=' . $context->id . ' and roleid=5 and a.userid=u.id;';
							
		//$rs = $DB->get_recordset_sql( $query );
		
		$this->content = new stdClass();
		
		

		//$this->content->items[] = html_writer::div($r->firstname . " " . $r->lastname);
		//$this->content->items[] = html_writer::tag('a', $r->firstname . " " . $r->lastname, [href=>$CFG->wwwroot . '/user/profile.php?id=' . $r->id  ]);
		//$this->content->items[] = html_writer::tag('a', get_string('expire_task_alert', 'block_alerts_generator') , [href=>$CFG->wwwroot . '/blocks/alerts_generator/expire_task_alert.php?id=' . $context->id ]);
			
		//$url= html_writer::tag('a', get_string('expire_task_alert', 'block_alerts_generator') , [href=>$CFG->wwwroot . '/blocks/alerts_generator/expire_task_alert.php?id=' . $context->id ]);
		$url= $CFG->wwwroot . '/blocks/alerts_generator/expire_task_alert.php?id=' . $COURSE->id;
		$this->content->items[] = html_writer::link($url, get_string('expire_task_alert', 'block_alerts_generator'), array('target' => '_blank'));
				
		//$this->content->items[] = html_writer::div('anonymous');
		//$this->content->items[] = html_writer::tag('a', 'School', [href=>'School.php']);
		//$this->content->items[] = html_writer::tag('c', 'Teacher', [href=>'Teacher.php']);
		//$this->content->items[] = html_writer::tag('c', 'Pupils', [href=>'Pupils.php']);
		
		//$this->content->icons  = array();
		//$this->content->footer = '';

        return $this->content;
    }

    public function get_aria_role() {
        return 'navigation';
    }
}
