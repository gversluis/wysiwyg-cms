#!/usr/bin/php
<?php
	$hash = password_hash($argv[1], PASSWORD_DEFAULT);
	$check = password_verify($argv[1], $hash);
?>
<?=$argv[1]."\n"?>
<?=$hash."\n"?>
<?=$check."\n"?>

