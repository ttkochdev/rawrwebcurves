<?php
// Send back where they came from
header( "Location: " . $_SERVER['HTTP_REFERER'] );
?>
