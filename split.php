#!/usr/bin/php
<?php

require "/usr/share/php-getid3/getid3.php";
$input_name = trim($_SERVER['argv'][1]);
$output_name = trim($_SERVER['argv'][2]);

$getID3 = new getID3;
$fileData = $getID3->analyze($input_name);

$count = ceil($fileData["playtime_seconds"] / 600);
$command_str = "mencoder -ss %s -endpos 00:10:00 -oac lavc -ovc lavc -of avi -forceidx {$input_name} -o {$output_name}_%02d.avi";
$child = 0;
for($i = 0;$i < $count;$i++){
	$pid = pcntl_fork();
	if($pid == -1){
		die("failed to fork.\n");
	}elseif($pid){
		$child++;
		continue;
	}else{
		$start_time = strftime("%T",$i*600-3600*8);
		$command = sprintf($command_str,$start_time,$i+1)."\n";
	//	echo $command;
		shell_exec($command);
		exit();
	}
}

echo "Total $child process running \n";
while(1){
	$status = null;
	if($child > 0){
		$pid = pcntl_wait($status);
		echo "$pid is success end.\n";
		$child--;
	}
}

?>