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
$course_id = required_param('id', PARAM_INT);
global $DB, $COURSE, $USER;

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);
 

//Search not overdue assigns, and not already scheduled	
$query = 'SELECT 	id, 
					name, 
					duedate 
					FROM {assign} 
					WHERE course = ' . $course_id . ' 
					AND duedate > UNIX_TIMESTAMP(NOW())  
					AND id  NOT IN(
						SELECT distinct(assignid) 
							FROM {block_alerts_generator_ans_s}
							WHERE sent = 0 
					) ORDER BY name'; 		
$result = $DB->get_recordset_sql( $query );



?>

<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo get_string('assign_not_sent_spvrs_alert', 'block_alerts_generator');?></title>
		<link rel="stylesheet" href="externalref/jquery-ui-1.12.1/jquery-ui.css">
		<script src="externalref/jquery-1.12.4.js"></script> 
		<script src="externalref/jquery-ui-1.12.1/jquery-ui.js"></script>
	</head>
    <body>	

	<?php 
		$PAGE->set_url('/assign_not_sent_spvrs.php');
		$PAGE->set_heading($COURSE->fullname);
		echo $OUTPUT->header(); 
		
	?>	
		<style>	
			body {}
			
			input.text, textarea.text {} 
				
			.ui-widget { /* font-size: 12px; */} 
						
			label{
			 display : inline;
			 padding-right : 4px;
			}
			
			.container_body_ag{
				text-align:center;
				width: 750px;
				margin: auto;
				margin-top: 1em; /*
				height: 200px;				
				padding: 20px 20px 0px 20px;				
				border: 2px solid;
				border-radius: 25px;  */		
			}
			
			.no_results{				
				margin: auto;	
				margin-top: 4em; 
				margin-bottom: 2em; 
			}
			.form_anss{			
				margin-top: 2em; 
			}
			.footer_page_link{
				margin-top: 3em; 
				margin-bottom: 2em;				
			}
		</style>
		
	
		<script type="text/javascript">
		$(document).ready(function(){
			
			var course_id  = <?php echo json_encode($course_id);?>; 
			
			$( ".saveAlert" ).click(function( event ) {
							
				var anss_assign_id = $('.form_anss').find(".anss_assign_id").val();			
				
				var url = 'schedule_assign_not_sent_spvrs.php';
				
				//alert(anss_assign_id);
				
				var posting = $.post( url, { course_id: course_id, anss_assign_id: anss_assign_id } );
																													
				posting.done(function( data ) {
												
					if(data.asgn_count!=0){												
						alert("<?php echo get_string('alert_already_scheduled', 'block_alerts_generator');?>");
					}else{
						if(data.asgn_count==0 && data.id_anss > 0){																					
							alert("<?php echo get_string('scheduled_alert', 'block_alerts_generator');?>");
								
							location.reload();													
						}else {													
							alert("<?php echo get_string('not_scheduled_alert', 'block_alerts_generator');?>");
						}
					}
				});		
					
			});
						
			//$( ".selectmenu" ).selectmenu({width: 660});

			//$( ".button" ).button();
			
		});
		</script>
		
		<div class="container_body_ag" > <!-- style="text-align:center;" -->
		
		<h2><?php echo get_string('assign_not_sent_spvrs_alert', 'block_alerts_generator');?></h2>
		
		<?php if($result->valid()): ?>
		
		
		<div class="form_anss">		 
			<label>Notificar à responsaveis do curso alunos que não enviarem a tarefa:</label>
			<select class="anss_assign_id" class="selectmenu">
				<?php foreach ($result  as  $rs) :?>
					<option value="<?php echo $rs->id; ?>"><?php echo  $rs->name; ?></option>				
				<?php endforeach; ?>
			</select> 
			<button class="saveAlert" type="button" >Criar Alerta</button> 
		</div>	
		<div class="footer_page_link">
			<p><a href="show_assign_not_sent_spvrs.php?id=<?php echo $course_id;?>" class="button">Editar/Excluir Alertas Cadastrados</a></p>
		</div>
		<?php else:  ?>
	
		<div class="no_results"><p>Não há tarefas disponiveis</p></div>
		
		<div class="footer_page_link">
			<p><a href="show_assign_not_sent_spvrs.php?id=<?php echo $course_id;?>" class="button">Editar/Excluir Alertas Cadastrados</a></p>
		</div>		
		
		<?php endif;  ?>
		
		</div>
		<?php 
		$result->close();
		echo $OUTPUT->footer();
				
		/*
		$text_msg = "mesage";
		$url = $CFG->wwwroot . '/user/profile.php?id' . 2;
				
		$user = new stdClass();
		$user = $DB->get_record('user', array('id' => 2)); 
		$fullname = fullname($user, true);		
		$profilelink = '<strong>'. \html_writer::link($url, $fullname, array('target' => '_blank')) . '</strong>';
		$text_msg = $text_msg . "<br />" . $profilelink ;
		echo($text_msg);
		*/
		?>
		
	</body>
</html>