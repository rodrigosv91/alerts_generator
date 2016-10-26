<?php
require('../../config.php');

$course_id = required_param('id', PARAM_INT);
require_login( $course_id );

$PAGE->set_url('/test.php');

class somedumbclass {
public static function test() {
global $SESSION;
$SESSION->myvar = 'test';
    }
}

echo $OUTPUT->header();

$PAGE->set_pagelayout('print');

if (isset($SESSION->myvar)) {
echo 'It works!';
} else {
    somedumbclass::test();
	echo 'not works!';
}


//unset($SESSION->myvar);

echo $OUTPUT->footer();

?>