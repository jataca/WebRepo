<?php

/*
Written by Chris Jean for iThemes.com
Version 2.0.0

Version History
	1.0.0 - 2009-12-02
		Release ready
	1.0.1 - 2009-12-31
		Made _get_layout_modules properly merge $modules array
			before returning results
		Added layout_modules_loaded action at end of _load_modules
	1.0.2 - 2009-12-31
		Renamed layout_modules_loaded to more appropriate
			builder_layout_modules_loaded
	1.0.3 - 2010-01-05
		Changed get_layout_modules to builder_get_layout_modules
		Changed register_layout_module to builder_register_layout_modules
	2.0.0 - 2010-01-07
		Changed filter builder_get_layout_modules to builder_get_modules
		Changed filter builder_register_layout_modules to builder_register_modules
		Changed filter builder_layout_modules_loaded to builder_modules_loaded
*/


if ( ! class_exists( 'BuilderModules' ) ) {
	class BuilderModules {
		var $_modules;
		
		
		function BuilderModules() {
			add_filter( 'builder_get_modules', array( &$this, '_get_layout_modules' ), 0 );
			
			$this->_load_modules();
		}
		
		function register_module( &$module ) {
			$this->_modules[$module->_var] =& $module;
		}
		
		function _get_layout_modules( $modules ) {
			$modules = array_merge( $modules, $this->_modules );
			
			return $modules;
		}
		
		function _load_modules() {
			$dir = dirname( __FILE__ ) . '/modules';
			
			ITUtility::require_file_once( "$dir/class.module.php" );
			
			if ( $readdir = opendir( $dir ) ) {
				while ( false !== ( $module = readdir( $readdir ) ) )
					if ( is_file( "$dir/$module/module.php" ) )
						ITUtility::include_file( "$dir/$module/module.php" );
			}
			
			do_action( 'builder_modules_loaded' );
		}
	}
	
	$GLOBALS['builder_modules'] =& new BuilderModules();
	
	do_action( 'builder_register_modules' );
}


?>
