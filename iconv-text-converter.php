#!/usr/bin/php
<?php

/*
/* icon-column-converter.php $file_source [$file_dest] 
* try to convert each column of a file from _ICONV_FROM_ to _ICONV_TO_.
* 
* Sometimes... it not know why... ( because coders :D ) some scripts or apps dumps files with mixed codification, 
* one column with utf8, another with iso-8859-1... and process this data is a madness.
*
* This simple script try to convert each column in order to get a normalized output, all columns with the same codification.
*
* It's easy, if I have a row with 4 columns, I get column by column, then I try to convert the ColumnX to our _ICONV_TO_, if fails , I left the column as is, if not, is converted.
*
*
* License : GNU/ GPL v3 
* Author : Jorge Senín
* website : http://www.senin.org
* email : jorge@senin.org
*/

define ( '_COLUMN_DELIMITER_' , ';');
define ( '_ICONV_FROM_','utf-8');
define ( '_ICONV_TO_','ISO-8859-1//TRANSLIT');
define ( '_END_OF_LINE_',"\n");

function iconv_error_handler($errno, $errstr){
// only thow an exception if warning had catched
	throw new ErrorException($errstr, 0, $errno); 

}

// process each line 
function process( $line = null ) {

	if ( is_null($line) )	
		return false ;

	$output = array();
	$res = "";

	foreach ( split( _COLUMN_DELIMITER_ , $line )  as $row ){

		set_error_handler('iconv_error_handler', E_NOTICE); 
	
		try {
			$res = iconv( _ICONV_FROM_, _ICONV_TO_, $row );
		}catch ( Errorexception $e ){
			// if couldn't be converted , then left it as is
			$res = $row;
		}

		$output [] = $res ;
	}

	// build the line processed
	return join( _COLUMN_DELIMITER_, $output ) ;
}


function dump ( $file = "dump.txt" , $output ){



	if ( $f = fopen ( $file , 'w' )) {

		foreach ( $output as $line )
			fputs( $f , $line . _END_OF_LINE_ );
		fclose( $f );
	}
	else {
		print "no puedo escribir en la salida : $file";
	}
	
}




// source file 
$src = null ;
if ( isset( $argv[1] ) ) {
	$src = $argv[1];
}

if ( ! $src ){
	die ( "Params missing. Usage\n icon-column-converter.php source_file [dest_file]\n\n") ;
}


// dest file 
$dst = $src. '.dump.txt' ;
if ( isset( $argv[2] ) ){
	$dst = $argv[2];
}



$output = array();

if ( $f = fopen ( $src , 'r' ) ) {
		
	while ( $line = fgets( $f ) ) 	
	
	// removes last character ( \n )
	$output[] = process( chop($line) );		

	fclose ( $f );	
	
	dump ( $dst ,  $output );
}
else {
	print "Fatal: Can't open file $src\n";
}


