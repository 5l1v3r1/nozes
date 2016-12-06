<?php

require "boot.php";

switch ($url) {

//////////////// CRUD Templates
	case "ActionRmTask":
	test_csrf();
        $taskrm=htmlentities($_POST['TaskRm']);
        $pidrm=escapeshellarg($_POST['PidRm']);
	posix_kill($pidrm, 9); // kill process
        $res = $crud->dbDelete('tasktool', 'id', $taskrm );
        $page->conteudo='<br><br><p class="message message-success message-closable">Task removed!</p><br<br>';
        $page->titulo="Task removed";
        print $page->display_page();
	sleep(2);
	header("location:javascript://history.go(-1)");
        break;
   
	case "ActionViewTask":
	test_csrf();
        $host=htmlentities($_POST['Host']);
        $command=htmlentities($_POST['Command']);
        $date=htmlentities($_POST['Date']);
        $status=htmlentities($_POST['Status']);        
        $pid=htmlentities($_POST['Pid']);
        $result=htmlentities($_POST['Result']);
        $name=htmlentities($_POST['Name']);
        $task.='<p class="message message-success message-closable"><pre><b>Host: </b>'.$host.'<br><b>Date:</b>'.$date.'<br>';		     
        $task.='<br><b>Status:</b> '.$status.'<br><b>Name:</b>'.$name.'<br><b>Command: </b>'.$command.'<br><b>Result:</b> '.$result;
        $task.='<br></pre></p><br<br>';
	$page->conteudo=$task;
        $page->titulo="Task view";
        print $page->display_page();
        break;

// form to add task in table
	case "AddTask":
        $form = new form();
	$token = NoCSRF::generate( 'csrf_token' );
        $values = array(
		  ':hidden'=>'csrf_token:'.$token,
                  'name:text'=>'nameadd:Tool name', 
		  'host:text'=>'hostadd:host',
		  'port:text'=>'portadd:port',
		  'logfile:text'=>'logadd:log of output',
                  'date:text'=>'dateadd:'.gmdate("Y-m-d h:i"),                         
                );
        $action="ControlTask.php?page=ActionAddTask";
 	$res = $crud->rawSelect('SELECT * FROM cmdtemplate ORDER BY id DESC'); 
      	$cont=0;
        $attacks=array();

      	foreach($res as $r) {
               $attacks[] =  $r['name'];
	}

        $la.=$form->StartForm($action);
        $la.=$form->SimpleForm($values);
        $la.=$form->SelectForm("TemplateSelect","addtemplate",$attacks);
        $la.=$form->ExitForm("submit");
        $page->titulo="Start Task";
        $page->conteudo=$la;
        print $page->display_page();
        break;

// get input of task and execute and put register in table
	case "ActionAddTask":
     	test_csrf(); 
     	$nameadd=sanitize($_POST['nameadd']); 
     	$dateadd=$_POST['dateadd'];
     	$hostadd=sanitize(escapeshellarg($_POST['hostadd'])); 
     	$portadd=sanitize(escapeshellarg($_POST['portadd']));
     	$logadd=sanitize(escapeshellarg($_POST['logadd']));  
     	$addtemplate=sanitize(htmlentities($_POST['addtemplate']));
     	$res = $crud->dbSelect("cmdtemplate","name",$addtemplate); 
     	$cont=0;
     	$attack="";

      	foreach($res as $r) {
        	$cmd_raw =  $r['command'];
		break;
	}

     	$newstr = str_replace("\$port", $portadd, $cmd_raw);
     	$newstr2 = str_replace("\$host", $hostadd, $newstr);
     	$newstr3 = str_replace("\$log", $logadd, $newstr2); // resut with "aha"
/*	$descriptorspec = [
	    0 => ['pipe', 'r'],
	    1 => ['pipe', 'w'],
	    2 => ['pipe', 'w']
	];
// so i think other way to solve this, something like RabbitMQ with scheduler in tasks...
	$proc_local="/usr/bin/php ".getcwd()."/Executor.php";   
	$proc = proc_open("nohup $proc_local", $descriptorspec, $pipes);
	$proc_details = proc_get_status($proc);
	$pid = $proc_details['pid'];
*/
     	$values = array(
	 array(
          'name'=>"$nameadd",
          'host'=>"$hostadd",
          'port'=>"$portadd", 
          'logfile'=>"$logadd",
          'pid'=>"0",
          'status'=>"Queue",
          'date'=>"$dateadd", 
          'result'=>"Wait . . .",
          'command'=>"$newstr3",
         )
        );
     	$crud->dbInsert('tasktool', $values);
     	$page->titulo="Data insert at Task table";
     	$page->conteudo='<br><br> <p class="message message-success message-closable">Task inserted ok!</p><br<br>';
     	print $page->display_page();
     	break;

// table of lists of tasks
	case "ListarTask":
      	$content[0].='<table id="dataTable" class="data" cellpadding="0" cellspacing="0">';
      	$tabela = array(
                  "id", 
                  "Name", 
                  "Date",
                  "Host",
                  "Status",
                  "PID",
		  "Log",
		  "View ",
                  "Remove"                             
                );
      	$form=new form();
      	$table=$form->TypeTable($tabela);
      	$content[1].=$table;
      	$res = $crud->rawSelect('SELECT * FROM tasktool ORDER BY id DESC'); 
      	$cont=0;
      	$token = NoCSRF::generate( 'csrf_token' );
      	foreach($res as $r) {
                $tabela = array( 
                  $r['id'],
                  $r['name'],
                  $r['date'],
                  $r['host'],
                  $r['status'],
                  $r['pid'],
                  $r['logfile'],
                  "<form  method=\"post\" action=\"ControlTask.php?page=ActionViewTask\">
		  <input type=\"hidden\" name=\"csrf_token\" value=\"".$token."\" />
                  <input type=\"hidden\" name=\"Pid\" value=\"".$r['pid']."\" />
                  <input type=\"hidden\" name=\"Name\" value=\"".$r['name']."\" />
                  <input type=\"hidden\" name=\"Date\" value=\"".$r['date']."\" />
                  <input type=\"hidden\" name=\"Host\" value=\"".$r['host']."\" />
                  <input type=\"hidden\" name=\"Status\" value=\"".$r['status']."\" />
                  <input type=\"hidden\" name=\"Command\" value=\"".$r['command']."\" />
                  <input type=\"hidden\" name=\"Result\" value=\"".htmlentities($r['result'])."\" />
                  <input type=\"image\" name=\"pimagem\" id=\"pimagem\" border=\"\" 
                  src=\"../view/imagens/procurar.png\" alt=\"\" value=\"valor\" />
                  </form>",
                  "<form  method=\"post\" action=\"ControlTask.php?page=ActionRmTask\">
		  <input type=\"hidden\" name=\"csrf_token\" value=\"".$token."\" />
                  <input type=\"hidden\" name=\"TaskRm\" value=\"".$r['id']."\" />
		  <input type=\"hidden\" name=\"PidRm\" value=\"".$r['pid']."\" />
                  <input type=\"image\" name=\"pimagem\" id=\"pimagem\" border=\"\" 
                  src=\"../view/imagens/remove.png\" alt=\"\" value=\"valor\" />
                  </form>"
                );
        	$form=new form();
         	$content[].=$form->ElementTable($tabela);
         	$cont+=1;
         	if($cont==19) $content[].="</table>";
         	if($cont==19) {
          		$content[].='<table id="dataTable" class="data" cellpadding="0" cellspacing="0">';
          		$content[].=$table; 
          		$cont=0;
         	}
      	}
	$content[].="</table>";
	$paginacao=new paginate(); 
	$file="ControlTask.php?page=ListTemplates";
	$nameget="list";
	$pag.=$paginacao->pag($content,$items,$file,$nameget);
	$page->conteudo=$pag;
	$page->titulo="List Tasks";
	print $page->display_page();
	break;

	default: 
	$page->titulo="ERRO 404";
	$page->conteudo="<p class=\"message message-error message-closable\">Have error here <b></b></p>";
	print $page->display_page();
	break;
}

?>
