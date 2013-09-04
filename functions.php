<?php
#-- Bring in the Framework
require( 'lib/Bootstrap.php' );


#-- Register the Widget Areas
mvc_register_sidebar('Home Featured','This is directly below the nav' );
mvc_register_sidebar('Home Middle', 'This is the middle of the homepage' );
mvc_register_sidebar('Home Lower Left', 'Left Side of the Home Below the Middle' );
mvc_register_sidebar('Home Lower Right', 'Right Side of the Home Below the Middle' );