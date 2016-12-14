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
	
$query = 'SELECT 	abs.id as absid,
					abs.messageid, 
					abs.absencetime,
					
					abs.begin_date, 
					abs.end_date, 
					msg.id as msgid, 
					msg.fromid, 
					msg.courseid
					FROM {block_alerts_generator_abs_s} abs 
					INNER JOIN {block_alerts_generator_msg} msg 
					ON abs.messageid = msg.id 
					AND msg.courseid = :courseid ' ; 	
					
$result = $DB->get_recordset_sql( $query , array('courseid' => $course_id), 0, 1);

$abs_users_count = $DB->count_records('block_alerts_generator_abs_z', array('courseid' => $course_id));

$str = "Sun Oct 30 2016 00:00:00 GMT-0200 (Hora oficial do Brasil)";
//echo (substr($str, 0, strpos($str, 'GMT')+8));
//$data = DateTime::createFromFormat("D M d Y H:i:s T", substr($str, 0, strpos($str, 'GMT')+8));//33
//echo (" data ". $data->format('D M d Y H:i:s T') ); 
//echo ( " timestamp " . $data->getTimestamp() );

//echo ( " str2 " . strtotime( '1991') );


//print_r($result);

//print_r($SESSION);

?>

<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo get_string('absence_alert_spvrs', 'block_alerts_generator');?></title>
		<link rel="stylesheet" href="externalref/jquery-ui-1.12.1/jquery-ui.css">
		<script src="externalref/jquery-1.12.4.js"></script> 
		<script src="externalref/jquery-ui-1.12.1/jquery-ui.js"></script>
	</head>
    <body>	  <!-- style="text-align:center;" -->
		
		<!--  <div class="container_abs_alert">  -->
		<?php 
			
			$url = $CFG->wwwroot . '/blocks/alerts_generator/absence_alert_spvrs.php?id=' . $course_id;
			$PAGE->set_url($url);
			$PAGE->set_heading($COURSE->fullname);
			echo $OUTPUT->header(); 
		
		?>	
				
		<style>
			.warning{      
				border: 1px solid red;
			} 
			
			body {/*
				color: #333333;
				margin: 1em 0;
				font-family: "Trebuchet MS", Tahoma, Verdana, Arial, sans-serif; 				 
				 margin:auto; */
			}

			input.text, textarea.text { font-family: Arial;  }
				
			.ui-widget { /* font-size: 12px; */ } 
			

						
			input[type="radio"] + label, .labelStatus{
				display: inline-block;
				padding: 0.4em 1em;
			}
			
			.container_body_ag{
				text-align:center;
				width: 1000px;				
				margin: auto; 
				margin-top: 1em;  /*
				height: 250px;
				padding: 20px 20px 0px 20px;				
				border: 2px solid;
				border-radius: 25px;  */		
			}
			
			.no_results{				
				margin: auto;	
				margin-top: 4em; 
			}
			
			.abs_form{
				margin-top: 2em; 
			}
			
			.deletAlert{
				margin-top: 1em; 
			
			}
			
			.status_alert{
				/* */ width: 14em; 			
				margin:auto;
				margin-top: 1em;
				margin-bottom: 2em; 	
				
				display: none;
				visibility: hidden;
			}
			
			.execution_date_alert{
				/* */ width: 28em; 			
				margin:auto;
				margin-top: 1em;
				margin-bottom: 2em; 
				
				
				
			}		
			
			.ui-datepicker-trigger { margin-left:0.2em; width: 1.5em; }
	
		</style>
		
	
		<script type="text/javascript">
		$(document).ready(function(){
			
			$( ".abs_form" ).find("input[name='from_date']").val( null);
			$( ".abs_form" ).find("input[name='to_date']").val( null );
			
			if(<?php echo( ($result->valid())==1 ? 1 : 0) ;?>){
				var abs_absencetime = $('.prop_alert').find("input:hidden[name='abs_absencetime']").val();
				var days = Math.floor(abs_absencetime/86400);
				$( ".abs_form" ).find("input[name='days']").val(days);
				
				var abs_begin_date = $('.prop_alert').find("input:hidden[name='abs_begin_date']").val();
				var abs_end_date = $('.prop_alert').find("input:hidden[name='abs_end_date']").val();
				
				
				if( abs_begin_date ){
					abs_begin_date = getformatedDate(abs_begin_date);
				}
				if( abs_end_date ){
					abs_end_date = getformatedDate(abs_end_date);
				}
				
				$( ".abs_form" ).find("input[name='from_date']").val( abs_begin_date);
				$( ".abs_form" ).find("input[name='to_date']").val( abs_end_date );
				//$( ".input_to_date" ).datepicker('setDate', abs_begin_date);			
			}
			
			$( ".saveAlert" ).click(function( event ) {
							
				var from_date = $(".input_from_date").datepicker('getDate') ;
				var to_date = $(".input_to_date").datepicker('getDate') ; 				 
				
				//var from_date2 = $('.input_from_date').datepicker({ dateFormat: 'dd-mm-yy' }).val();
				//var to_date2 = $('.input_to_date').datepicker({ dateFormat: 'dd-mm-yy' }).val();
				
				if($.trim(to_date) != '' && $.trim(to_date) != null){								
					to_date.setHours(23);
					to_date.setMinutes(59);
					to_date.setSeconds(59);					
				}
				
				//alert(to_date);

				
				var course_id  = <?php echo json_encode($course_id);?>; 
				days = $(".abs_form").find( "input[name='days']" ).val();
 
				url = $(".abs_form").find("form[name='usrform']").attr( "action" );		
				
				var posting = $.post( url, { course_id: course_id, days: days, from_date : from_date, to_date: to_date  } );
																					
				posting.done(function( data ) {
											
					if(data.abs_count == 0 && data.db_result > 0 ){													
						alert("<?php echo get_string('scheduled_alert', 'block_alerts_generator');?>");
						location.reload();	
					}else{
						if(data.abs_count > 0 && data.db_result === true){																					
							alert("<?php echo get_string('alert_updated', 'block_alerts_generator');?>");
							location.reload();														
						} else {
							alert("<?php echo get_string('not_scheduled_alert', 'block_alerts_generator');?>");
						}
					}
				});
				
				
		
			});											
			
			$( ".spinner" ).spinner({ //allow valid entries only
				max: 999999999, 
				min: 1, 
				//value:1,
			}).on('input', function () {
				if ($(this).data('onInputPrevented')) return;
				var val = this.value,
					$this = $(this),
					max = $this.spinner('option', 'max'),
					min = $this.spinner('option', 'min');         
				if (!val.match(/^[+-]?[\d]{0,}$/)) val = $(this).data('defaultValue');
				this.value = val > max ? max : val < min ? min : val;
			}).on('keydown', function (e) {
				// we set default value for spinner.
				if (!$(this).data('defaultValue')) $(this).data('defaultValue', this.value);
				// To handle backspace
				$(this).data('onInputPrevented', e.which === 8 ? true : false);
			});	

			$( ".button" ).button();
			

			$( ".input_radio" ).checkboxradio({icon: false	});

			
			$( ".deletAlert" ).click(function( event ) {
				if (confirm('<?php echo get_string('confirmation_message', 'block_alerts_generator');?>')) {			
				var url = 'del_absence_alert_spvrs.php';			
				var posting = $.post( url );
				
				posting.done(function( data ) {
																					
					if(data.abs_count == 0 && data.msg_count == 0){
						alert("<?php echo get_string('alert_deleted', 'block_alerts_generator');?>");					
						location.reload();	
					}else{
						//if(data.abs_count > 0 && data.msg_count > 0) // alert not deleted						
						alert("<?php echo get_string('alert_not_deleted', 'block_alerts_generator');?>");							
					}						
				});	
				}
			});	

			$( ".resetAbsenceUsers" ).click(function( event ){
				if (confirm('<?php echo get_string('confirmation_message', 'block_alerts_generator');?>')) {
					
					var url = 'del_absence_user_spvrs.php';			
					var posting = $.post( url );
					
					posting.done(function( data ) {
																					
						if( data.abs_count == 0 ){
							alert("<?php echo get_string('absence_users_reseted', 'block_alerts_generator');?>");					
							//location.reload();	
						}else{
							//if( data.abs_count > 0 ) // alert not deleted						
							alert("<?php echo get_string('absence_users_not_reseted', 'block_alerts_generator');?>");							
						}						
					});					
				}
			});
			
			$( ".datepicker" )
			.attr("placeholder", "dd-mm-yyyy")
			.change(function(){
					$(this).datepicker('setDate', $(this).datepicker('getDate'));
			  })
			.datepicker({ 	
							dateFormat: "dd/mm/yy",
							minDate: 0, 
							//maxDate: "10Y",
							changeMonth: true,
							changeYear: true,
							
							showOn: "button",
							buttonImage: "images/calendar.gif",
							buttonImageOnly: true,
							buttonText: "Select date"
							
			});
											
			//datepicker validation		 

			var dateFormat = "dd/mm/yy", 
			min_date  = new Date(),
			from = $( ".input_from_date" )
			.attr("placeholder", "dd/mm/yyyy")
			.datepicker({
				dateFormat: "dd/mm/yy",
				//defaultDate: "+1w",
				changeMonth: true,
				changeYear: true,
				//numberOfMonths: 1
				minDate: min_date,
				//maxDate: "10Y",	
				showOn: "both",
				buttonImage: "images/calendar.gif",
				buttonImageOnly: true,
				buttonText: "Select date"
			})
			.on( "change", function() {
				to.datepicker( "option", "minDate", getDate( this ) );
				from.datepicker('setDate', from.datepicker('getDate'));
				  
			}),
			to = $( ".input_to_date" )
			.attr("placeholder", "dd/mm/yyyy")
			.datepicker({
				dateFormat: "dd/mm/yy",
				//defaultDate: "+1w",
				changeMonth: true,
				changeYear: true,
				//numberOfMonths: 1
				minDate: min_date,
				//maxDate: "10Y",
				showOn: "both",
				buttonImage: "images/calendar.gif",
				buttonImageOnly: true,
				buttonText: "Select date"
			})
			.on( "change", function() {
				if( getDate( this ) > from.datepicker("option", "minDate") && getDate( this ) > from.datepicker('getDate') ){
					from.datepicker( "option", "maxDate", getDate( this ) ); 
				}
				to.datepicker('setDate', to.datepicker('getDate'));
			});
		 
			function getDate( element ) {
				var date;
				try {
					date = $.datepicker.parseDate( dateFormat, element.value );
				} catch( error ) {
				date = null;
				}
				return date;
			}		
		
			function getformatedDate( fulltimestamp ) {
				var date = new Date(fulltimestamp * 1000);
				var d  = date.getDate();
				var m = date.getMonth() + 1;
				var y = date.getFullYear();				
				var formatedDate =  (d<10?"0":"") + d + "/" + (m<10?"0":"") + m  + "/" + y;
			 
				return formatedDate;
			}
			
			$(".ui-datepicker").draggable() ;
			
		});
		</script>
		
		<div class="container_body_ag">
		
		<h2><?php echo get_string('absence_alert_spvrs', 'block_alerts_generator');?></h2>
		
		<div class="abs_form">
		
		<?php if($result->valid()): ?>		
		<form action="edt_absence_alert_spvrs.php" method="post" name="usrform" >		
		<?php else:  ?>
		<form action="schedule_absence_alert_spvrs.php" method="post" name="usrform">	 
		<?php endif;  ?>
		
			<p> 
				Notificar à responsaveis do curso quando alunos se ausentarem por:
				<input class="spinner " name="days" value="1" style="width: 60px; height:15px;"  >			
				dia(s) do curso.<!--
				<button class="saveAlert" type="button" style="margin: 0;" ><?php echo( ($result->valid()==1) ?  'Atualizar Alerta' : 'Criar Alerta') ;?></button> 
				-->
	<!--	</p>
			<?php if($result->valid()): ?>			
				<button class="deletAlert" type="button" >Deletar Alerta</button>
			<?php endif; ?>			
	-->
			
	<!--	<p>Date: <input type="text" class="datepicker" style="width: 100px; height:18px; margin: 0;"></p> -->
	 
			<div class="execution_date_alert" >	
				<fieldset>
					<legend>Periodo de execução do alerta: </legend>
					<span style="white-space:nowrap">
					
					<!--<label for="from">From</label>  -->de 
					<input type="text" class="input_from_date" id="from_date" name="from_date" style="width: 100px; height:18px; margin: 0;">
					 </span> 
					<span style="white-space:nowrap">
					<!-- <label for="to">to</label>  --> até
					<input type="text" class="input_to_date"  id="to_date" name="to_date" style="width: 100px; height:18px; margin: 0;">
					 </span> 
					 
				</fieldset>
			</div>
			
			<button class="saveAlert" type="button" style="margin: 0;" ><?php echo( ($result->valid()==1) ?  'Atualizar Alerta' : 'Criar Alerta') ;?></button>
		
			<?php if($result->valid()): ?>			
				<button class="deletAlert" type="button" style="margin: 0;" >Deletar Alerta</button>	
	<!--		<button class="resetAbsenceUsers" type="button" style="margin: 0;" >Reenviar Alertas</button>	-->
			<?php endif; ?>		
			
			<?php if($abs_users_count > 0): ?>	
				<button class="resetAbsenceUsers" type="button" style="margin: 0;" >Reenviar Alertas</button>
			<?php endif; ?>
						
	<!--	-->
		</form> 
		</div>
		
		<?php if($result->valid()): ?>
		
		<?php 
			$SESSION->block_alerts_generator_spvrs = new stdClass;
			$SESSION->block_alerts_generator_spvrs->id_abs_spvrs = 0;
			$SESSION->block_alerts_generator_spvrs->id_msg_abs_spvrs = 0;
			$SESSION->block_alerts_generator_spvrs->course_id_abs_spvrs = 0;
			
		foreach($result as $rs){
			$SESSION->block_alerts_generator_spvrs->id_abs_spvrs = $rs->absid; 
			$SESSION->block_alerts_generator_spvrs->id_msg_abs_spvrs = $rs->msgid; 
			$SESSION->block_alerts_generator_spvrs->course_id_abs_spvrs = $course_id;
		?>
		<div class="prop_alert">			
			<input type="hidden" value="<?php echo $rs->absencetime ;?>" class="abs_absencetime" name="abs_absencetime" />
			<input type="hidden" value="<?php echo $rs->begin_date ;?>" class="abs_begin_date" name="abs_begin_date" />
			<input type="hidden" value="<?php echo $rs->end_date ;?>" class="abs_end_date" name="abs_end_date" />			
		</div>
		
		
				
		<?php 	}  //end foreach 	?>	

		<?php else:  ?>
		
		<?php endif;  ?>
		
		</div>
		<?php 		 
		
		$result->close();
		//unset($SESSION->block_alerts_generator);
		echo $OUTPUT->footer();
		
		
		?>
		
	</body>
</html>

