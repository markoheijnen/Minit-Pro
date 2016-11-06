<?php
/*
Plugin Name: Minit PRO
Plugin URI: https://github.com/markoheijnen/Minit-Pro
GitHub URI: https://github.com/markoheijnen/Minit-Pro
Description: Add additional functionality to the Minit plugin of Kaspars Dambis.
Version: 1.0.0
Author: Marko Heijnen
Author URI: https://markoheijnen.com
*/

include dirname( __FILE__ ) . 'inc/minitpro.php';
include dirname( __FILE__ ) . 'inc/gz.php';

add_action( 'plugins_loaded', array( 'Minit_Pro', 'instance' ), 20 );
