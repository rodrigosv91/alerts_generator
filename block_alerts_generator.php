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
     
		global $CFG, $COURSE;
		$course = $this->page->course;

		/* Access control */
		$context = context_course::instance($course->id);
		$canview = has_capability('block/alerts_generator:viewpages', $context); // do not show the block if user is not allowed to viewpages		
		if (!$canview) {
            return;
        }
        if ($this->content !== null) {
            return $this->content;
        }
			
		$this->content = new stdClass();
		
		$this->content->items[] = '<h3>' . get_string('alert_for_students', 'block_alerts_generator') . '</h3>';
		
		//$this->content->items[] = html_writer::div($r->firstname . " " . $r->lastname);
		//$this->content->items[] = html_writer::tag('a', $r->firstname . " " . $r->lastname, [href=>$CFG->wwwroot . '/user/profile.php?id=' . $r->id  ]);
		//$this->content->items[] = html_writer::tag('a', get_string('expire_task_alert', 'block_alerts_generator') , [href=>$CFG->wwwroot . '/blocks/alerts_generator/expire_task_alert.php?id=' . $context->id ]);
			
		//$url= html_writer::tag('a', get_string('expire_task_alert', 'block_alerts_generator') , [href=>$CFG->wwwroot . '/blocks/alerts_generator/expire_task_alert.php?id=' . $context->id ]);
		
		
		$url= $CFG->wwwroot . '/blocks/alerts_generator/expire_task_alert.php?id=' . $course->id;
		$this->content->items[] = html_writer::link($url, get_string('expire_task_alert_title', 'block_alerts_generator'), array('target' => '_blank'));
		
		$url= $CFG->wwwroot . '/blocks/alerts_generator/show_expire_alerts.php?id=' . $course->id;
		$this->content->items[] = html_writer::link($url, get_string('expire_task_alert_title_2', 'block_alerts_generator'), array('target' => '_blank'));
				
		
		$this->content->items[] = "";
		$url= $CFG->wwwroot . '/blocks/alerts_generator/assign_not_sent.php?id=' . $course->id;
		$this->content->items[] = html_writer::link($url, get_string('assign_not_sent_title', 'block_alerts_generator'), array('target' => '_blank'));
		
		$url= $CFG->wwwroot . '/blocks/alerts_generator/show_assign_not_sent.php?id=' . $course->id;
		$this->content->items[] = html_writer::link($url, get_string('assign_not_sent_title_2', 'block_alerts_generator'), array('target' => '_blank'));
					
		$this->content->items[] = "";
		$url= $CFG->wwwroot . '/blocks/alerts_generator/absence_alert.php?id=' . $course->id;
		$this->content->items[] = html_writer::link($url, get_string('absence_alert_title', 'block_alerts_generator'), array('target' => '_blank'));
		
		$this->content->items[] = "";
		$url= $CFG->wwwroot . '/blocks/alerts_generator/scheduled_alert.php?id=' . $course->id;
		$this->content->items[] = html_writer::link($url, get_string('scheduled_alert_title_2', 'block_alerts_generator'), array('target' => '_blank'));
		
		$url= $CFG->wwwroot . '/blocks/alerts_generator/show_scheduled_alerts.php?id=' . $course->id;
		$this->content->items[] = html_writer::link($url, get_string('scheduled_alert_title_3', 'block_alerts_generator'), array('target' => '_blank'));	
	
	
	
		$this->content->items[] =  '<h3>' . get_string('alert_for_teachers', 'block_alerts_generator') . '</h3>';
			
		$url= $CFG->wwwroot . '/blocks/alerts_generator/assign_not_sent_spvrs.php?id=' . $course->id;
		$this->content->items[] = html_writer::link($url, get_string('assign_not_sent_title', 'block_alerts_generator'), array('target' => '_blank'));	
		
		$url= $CFG->wwwroot . '/blocks/alerts_generator/show_assign_not_sent_spvrs.php?id=' . $course->id;
		$this->content->items[] = html_writer::link($url, get_string('assign_not_sent_title_2', 'block_alerts_generator'), array('target' => '_blank'));			
		
		$this->content->items[] = "";
		$url= $CFG->wwwroot . '/blocks/alerts_generator/absence_alert_spvrs.php?id=' . $course->id;
		$this->content->items[] = html_writer::link($url, get_string('absence_alert_title', 'block_alerts_generator'), array('target' => '_blank', )); 
		
			
		//$this->content->items[] = html_writer::div('anonymous');
		//$this->content->items[] = html_writer::tag('a', 'School', [href=>'School.php']);
		//$this->content->items[] = html_writer::tag('c', 'Teacher', [href=>'Teacher.php']);
		//$this->content->items[] = html_writer::tag('c', 'Pupils', [href=>'Pupils.php']);
		
		$this->content->icons  = array();
		$this->content->footer = '';

        return $this->content;
    }

    public function get_aria_role() {
        return 'navigation';
    }
}
