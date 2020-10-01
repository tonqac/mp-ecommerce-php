<?php
	$txt = "\n".date("Y-m-d H:i:s"). "\n". print_r($_REQUEST,true);

	file_put_contents('results.txt', $txt, FILE_APPEND | LOCK_EX);
?>