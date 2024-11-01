<?php
//if uninstall is not called from wordpress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

//delete options from option table
delete_option ('wikitip_options');

//remove any additional options or custom tables from DB
