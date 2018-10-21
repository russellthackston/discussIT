<?php
	
spl_autoload_register(function ($class_name) {
    include "../src/include/classes/" . $class_name . '.php';
});

?>