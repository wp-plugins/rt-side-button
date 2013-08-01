<?php

// If uninstall was not called from WordPress, then exit
if( !defined( 'WP_UNINSTALL_PLUGIN') )
	exit ();


// Delete all plugin related fields from the options table
delete_option( 'royal_sidebutton_plugin_options' );

