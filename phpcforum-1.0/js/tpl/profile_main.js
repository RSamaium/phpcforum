$(function() {
	$.datepicker.setDefaults( $.datepicker.regional[ "" ] );
	$( ".date" ).datepicker( $.datepicker.regional[ "{LANG_DATEPICKER}" ] );
	$( ".date" ).datepicker( "option", "changeMonth", true );	
	$( ".date" ).datepicker( "option", "changeYear", true );	
	$( ".date" ).datepicker( "option", "yearRange", '1900:+nn' );	
	$( ".date" ).datepicker( "setDate" , '{USER_BIRTHDAY}' );	
});