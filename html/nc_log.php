<?php

function test_backtrace()
{
    ob_start();
    debug_print_backtrace();
    test_log( ob_get_contents() );
    ob_end_clean();
}

function test_printr($val)
{
    ob_start();
    print_r($val);
    test_log( ob_get_contents() );
    ob_end_clean();
}


function test_time()
{
    test_log( date("Y-m-d H:i:s") );
}

function test_url()
{
    $protocol = ( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on') ) ? 'https' : 'http';
    $url = $protocol. '://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] ;
    test_log( "URL: ".$url );
}

function test_request_uri()
{
    test_log( "REQUEST_URI: ".$_SERVER["REQUEST_URI"] );
}

function test_request_method()
{
    test_log( "REQUEST_METHOD: ".$_SERVER["REQUEST_METHOD"] );
}

function test_log( $data )
{
	if ( is_array($data) ) {
		file_put_contents(BASE_DIR."/webapp/logs/debug_log.txt", print_r($data,true)."\n", FILE_APPEND);
	}
	else {
	    file_put_contents(BASE_DIR."/webapp/logs/debug_log.txt", $data."\n", FILE_APPEND);
	}
}
?>