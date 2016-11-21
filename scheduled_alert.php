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
global $DB, $SESSION, $COURSE;

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);
	


//print_r($result);

//print_r($SESSION);

?>

<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo get_string('scheduled_alert_title', 'block_alerts_generator');?></title>
		<link rel="stylesheet" href="externalref/jquery-ui-1.12.1/jquery-ui.css">
		<script src="externalref/jquery-1.12.4.js"></script> 
		<script src="externalref/jquery-ui-1.12.1/jquery-ui.js"></script>
		
		<script src="externalref/jquery.datetimepicker.full.min.js"></script>
								
	</head>
    <body>	 
		<?php 		
			$url = $CFG->wwwroot . '/blocks/alerts_generator/scheduled_alert.php?id=' . $course_id;
			$PAGE->set_url($url);
			$PAGE->set_heading($COURSE->fullname);
			echo $OUTPUT->header(); 		
		?>	
				
		<style>				
			body {/*
				color: #333333;
				margin: 1em 0;
				font-family: "Trebuchet MS", Tahoma, Verdana, Arial, sans-serif; 				 
				 margin:auto; */
			}

			input.text, textarea.text { /* font-family: Arial;  */}
				
			.ui-widget { /* */ font-size: 12px; } 
					
						
			input[type="radio"] + label, .labelStatus{
				display: inline-block;
				padding: 0.4em 1em;
			}
			
			.container_body_ag{
				text-align:center;
				width: 1000px;			
				margin: auto; 
				margin-top: 1em; /*
				height: 230px;				
				padding: 20px 20px 0px 20px;				
				border: 2px solid;
				border-radius: 25px;   */		
			}
			
			.no_results{				
				margin: auto;	
				margin-top: 4em; 
			}
			
			.sched_alert_form{
				margin-top: 2em; 
			}
			
			.deletAlert{
				margin-top: 0em; 
			}
			
			.footer_page_link{
				margin-top: 3em; 
				margin-bottom: 2em;
			}
			
			.execution_date_alert{
				/* */ width: 28em; 			
				margin:auto;
				margin-top: 1em;
				margin-bottom: 2em; 
						
			}		
			
			.ui-datepicker-trigger { margin-left:0.2em; width: 1.5em; }
			/*
			.overflow {
				height: 200px;
			}
			*/
			
			.ui-selectmenu-open{
				max-height: 250px;
				overflow-y: scroll;
			}	/* */
	
			
		</style>
		
	
		<script type="text/javascript">
		$(document).ready(function(){
						
			$( ".dialog_link" ).click(function( event ) {							
				$( ".dialog" ).dialog( "open" );
				event.preventDefault();			
			});
			
			$( ".dialog" ).dialog({
				autoOpen: false,
				width: 450,
				modal: true,
				buttons: [
					{
						text: '<?php echo get_string('register', 'block_alerts_generator');?>',
						click: function() {
						
							var course_id  = <?php echo json_encode($course_id);?>; 							
							var input_date = $(".input_date").datepicker('getDate') ; 
							
							var $form = $( this ),							
							subjectval = $form.find( "input[name='subject']" ).val(),
							textoval = $form.find( "textarea[name='texto']" ).val(),							
							hm_time = $(".sched_alert_form").find( "select[name='hm_time']" ).val(),							
							customized = $form.find( "input[name='check_custom_message']" ).is(":checked")== true ? 1 : 0,							
							url = $(".sched_alert_form").find("form[name='usrform']").attr( "action" ); 				
							
							if($.trim(input_date) == '' || $.trim(input_date) == null){		
								alert('<?php echo get_string('invalid_date', 'block_alerts_generator');?>'); 
							}
							else{								
								var str_hm_time = hm_time;
								var str_hm_time_splited = str_hm_time.split(':',2);
								var str_input_date = new Date(input_date);
									
								str_input_date.setHours(str_hm_time_splited[0]);
								str_input_date.setMinutes(str_hm_time_splited[1]);
								
								if(str_input_date < new Date()){
									alert('<?php echo get_string('invalid_date_2', 'block_alerts_generator');?>'); 
								}else{	
								
									if( ($.trim(textoval) == '') && ($.trim(subjectval) == '') ){						
										alert('<?php echo get_string('empty_subject_message', 'block_alerts_generator');?>'); 									
									}else{						
										if($.trim(textoval) == ''){								
											alert('<?php echo get_string('empty_message', 'block_alerts_generator');?>'); 
										}	
										else{
											if($.trim(subjectval) == ''){ 
												alert('<?php echo get_string('empty_subject', 'block_alerts_generator');?>'); 																
											}else{			
																			
												var posting = $.post( url, { course_id: course_id, customized: customized, //id_msg: id_msg, id_abs: id_abs,
																					hm_time: hm_time, subject: subjectval, texto: textoval, //} );
																					input_date : input_date } );
																							
												posting.done(function( data ) {
			
													if( data.ag_sch_alert > 0 && data.msg_id > 0 ){																					
														//alert("<?php echo get_string('scheduled_alert', 'block_alerts_generator');?>");
														$( ".dialog" ).dialog("close");
														
														if(confirm('<?php echo get_string('scheduled_alert_3', 'block_alerts_generator');?>')){
															window.location.href = <?php echo  json_encode($CFG->wwwroot) ;?> + "/blocks/alerts_generator/show_scheduled_alerts.php?id=" + <?php echo  json_encode($course_id) ;?>  ;
														}else{
															location.reload(); 
														}
														//$( '.dialog_confirm_redirect' ).dialog( "open" );															
													} else {
														alert("<?php echo get_string('not_scheduled_alert', 'block_alerts_generator');?>");
														$( ".dialog" ).dialog("close");														
														//$( '.dialog_confirm_not_scheduled_alert' ).dialog( "open" );												
													}
												});		
											}									
										}
									}
								}
							}//end else
						}
					},
					{
						text: '<?php echo get_string('cancel', 'block_alerts_generator');?>',
						click: function() {
							$( this ).dialog( "close" );
						}
					}
				]
			});								

			$( ".button" ).button();
			
			$( ".input_radio" ).checkboxradio({icon: false	});
			
			//datepicker validation
			var min_date2 = new Date();
			min_date2.setHours(00);
			min_date2.setMinutes(00);
			min_date2.setSeconds(00);
			min_date2.setMilliseconds(00);
							
			$( ".input_date" )
			.attr("placeholder", "dd/mm/yyyy")
			.datepicker({
				dateFormat: "dd/mm/yy",
				//defaultDate: "+1w",
				changeMonth: true,
				changeYear: true,
				//numberOfMonths: 1
				minDate: min_date2,
				//maxDate: "10Y",	
				showOn: "both",
				buttonImage: "images/calendar.gif",
				buttonImageOnly: true,
				buttonText: "Select date"
			}).on( "change", function() {
				if(  getDate( this ) < min_date2  ){				
					$( this ).datepicker('setDate', null) ;
					//alert("Data inválida");
				}				
				//from.datepicker('setDate', from.datepicker('getDate'));				  
			});
					
			var dateFormat = "dd/mm/yy";

			function getDate( element ) {
				var date;
				try {
					date = $.datepicker.parseDate( dateFormat, element.value );
				} catch( error ) {
				date = null;
				}		 
				return date;
			}			
			
			$(".ui-datepicker").draggable() ;
			
			$( ".dialog_custom_message_help" ).dialog({
				autoOpen: false,
				width: 300,
				modal: true,
				resizable: false,
				buttons: [
					{
						text: "Ok",
						click: function() {
							$( this ).dialog( "close" );
						}
					}
				]
			});
			
			$( ".helpButton" ).click(function( event ) {
				$( ".dialog_custom_message_help" ).dialog( "open" );
				event.preventDefault();
			});
			
			$( ".selectmenu" ).selectmenu({width: 160});
			//$( ".select_hm_time" ).selectmenu({ width : $( ".select_hm_time" ).attr("width")})
			
			$( ".select_hm_time" ).selectmenu( "option", "width", 100 );
			
			//$( ".select_hm_time" ).selectmenu( "option", "width", 120 )
			//.selectmenu( "menuWidget" )
			//.addClass( "overflow" );
			
			
		});
		</script>
		
		<div class="container_body_ag">
		
			<h2><?php echo get_string('scheduled_alert_title', 'block_alerts_generator');?></h2>
			
			<div id="" class="sched_alert_form">
						
				<form action="schedule_scheduled_alert.php" method="post" name="usrform">		
						
					<p> 
						Enviar em   
						<input type="text" class="input_date"  name="input_date" style="width: 100px; height:18px; margin: 0;">
					
						às
						<select class="selectmenu select_hm_time" name="hm_time"  >
							<?php 
							for($hours=0; $hours<24; $hours++) // the interval for hours is '1'
								for($mins=0; $mins<60; $mins+=60) // the interval for mins is '60'
									echo '<option value=' .str_pad($hours,2,'0',STR_PAD_LEFT).':'.str_pad($mins,2,'0',STR_PAD_LEFT).'>'
										.str_pad($hours,2,'0',STR_PAD_LEFT).':'
											.str_pad($mins,2,'0',STR_PAD_LEFT).'</option>';
							?>
						</select>
						
						<a href="#" id="" class=" button dialog_link" style="margin-left:0em;"><span class="ui-icon ui-icon-newwin"></span>Mensagem</a> 
						para alunos.
					</p>								
					<!--	-->	
					
					<div id="" class="dialog">
						<p class="subject_paragraph"><?php echo get_string('subject', 'block_alerts_generator');?>:
						<input class="input_subject text ui-widget-content ui-corner-all  " type='text' name='subject' form="usrform" ></p>
						<textarea class="text_message text ui-widget-content ui-corner-all " rows="5" cols="60" name="texto" form="usrform"></textarea>
						
						<fieldset>
							<legend></legend>
							<input type="checkbox" name="check_custom_message" id="check_custom_message" class="check_custom_message" />
							<label for="check_custom_message" class="" >Usar nome personalizado na mensagem. </label>
							<button class=" button helpButton" style="masrgin:0px; "></span>Ajuda</a>
						</fieldset>
					</div>
				</form> 
			</div>
			
			<div class="dialog_custom_message_help" style="text-align:center">
				<?php echo get_string('custom_checkbox_help_message', 'block_alerts_generator');?> 
				</br></br> 
				<?php echo get_string('custom_checkbox_help_example', 'block_alerts_generator');?>   
			</div>
			
			<div class="footer_page_link">
				<p><a href="show_scheduled_alerts.php?id=<?php echo $course_id;?>" class="">Editar/Excluir Alertas Agendados</a></p>
			</div>
		
		
		</div>
		<?php 		 
		echo $OUTPUT->footer();
		?>	
	</body>
</html>