<?php
// search queue task in DB and execute each four seconds
  require "../helper/class.crud.php";

  
 
  echo "[ Nozes's Task Executor ]\n";


  while(1) {

 	$crud = new crud();
 	$res = $crud->rawSelect('SELECT * FROM tasktool WHERE status = "Queue"');

	foreach($res as $r) {

   		$pid = pcntl_fork();

    		if (-1 == $pid) {
        		echo "Couldn't fork!\n";
    		} elseif ($pid === 0) {
			$pidnum=posix_getpid();
			$command=$r['command'];
			$cmd=str_replace("&#39;","'",$command);
			print "[ Task :\n $cmd \n ]\n";
                	$id=$r['id'];
 			$crud->dbUpdate('tasktool', 'status', "running", 'id', $id);
 			$crud->dbUpdate('tasktool', 'pid', $pidnum, 'id', $id);
 			$content=shell_exec($cmd);
			sleep(5);
 			$crud->dbUpdate('tasktool', 'result', htmlentities($content), 'id', $id);
 			$crud->dbUpdate('tasktool', 'status', "finish", 'id', $id);
			exit(0);
		}
	}
	sleep(5);
  }
?>
