<?php

require "boot.php";

switch ($url) {
    case "auth":
      $form = new form();
      $values = array(
                  'Login:text'=>'user: nick name', 
                  'Senha:password'=>'pass:4321'                              
                ); 
      $la.=$form->StartForm("auth.php?page=login");
      $la.=$form->SimpleForm($values);
      $la.=$form->ExitForm("logar");
      print $la;
      break;

    case "login": 
      test_csrf();	
      $user=sanitize($_POST['user']);
      $pass=sanitize($_POST['pass']);
      $secret=$frase.$pass;
      $_SESSION['userronin']=$user; 
      $gen=new Bcrypt(12);
      $bcrypt_hash=$gen->hash($secret); 
      $pdo2 = new crud(); 
      $pdo2->conn();
      $stmt = $pdo2->db->prepare("select * FROM userronin WHERE login = ?  ");
      $stmt->bindValue(1, $user, PDO::PARAM_STR);  
      $stmt->execute();  
      $res=$stmt->fetchAll();
      $_SESSION['passronin']=$bcrypt_hash; 
          if($gen->verify($bcrypt_hash, $res[0]['pass'])=="false") {
           print "<img src=\"../view/images/alerta.png\">
            <h1>ERROR at auth  05</h1> 
            <meta HTTP-EQUIV='refresh' CONTENT='2; URL=../view/login.php'>"; 
           exit;
	  }

         $janela='    		<div class="portlet portlet-closable x4">	
				<div class="portlet-header">
					<h4>Login manager</h4> 
				</div> <!-- .portlet-header -->		
				<div class="portlet-content">
                                ';
         $var="<p><b>Login:</b>".htmlentities($r['login'])." <br> <b>owner:</b>".htmlentities($r['owner']);
         $bemvindo="Welcome to Nozes tool</p>";
         $values = array('last_ip'=>sanitize($_SERVER['REMOTE_ADDR']) );
         $crud->Update('userronin', $values, 'id', $r['id']);
         $page->conteudo=$janela." <br>".$bemvindo."<meta HTTP-EQUIV='refresh' CONTENT='1; URL=auth.php?page=conta'></div></div>";
         print $page->display_page();
      
      break;

     case "suporte":
      $page->titulo="Suport";
      $suporte="<font color=orange><pre>
			  NOZES Pentest CMD MANAGER
			  Version: 0.1
			  Contact:  coolerlair@gmail.com
                </pre>";
      $page->conteudo="<div style=\"background-color:black;\">".$suporte."</div></div>";
      print $page->display_page();
      break;

     case "conta":
      $stmt = $pdo2->db->prepare("select * FROM userronin WHERE login = ?  ");
      $stmt->bindValue(1, $_SESSION['userronin'], PDO::PARAM_STR);  
      $stmt->execute();  
      $res=$stmt->fetchAll(PDO::FETCH_ASSOC);
      $token = NoCSRF::generate( 'csrf_token' );

       foreach($res as $r) {
         $form = new form();
         $values = array(
                  'login:text'=>'loginedit:'.$r['login'], 
		  'token:hidden'=>'csrf_token:'.$token,
                  'Password:password'=>'passedit:'.$r['pass'],
                  'E-mail:text'=>'mailedit:'.$r['mail'],
		  'id:hidden'=>'idedituser:'.$r['id']                               
                );
         $array = array(
                  "admin", 
                  "user"                              
                );
         $action="auth.php?page=ActionEditUser";
         $la.=$form->StartForm($action);
         $la.=$form->SimpleForm($values);
         $la.=$form->SelectToEdit("owner: ","owneredit",$array,$r['owner']);
         $la.=$form->ExitForm("submit");
       }
      $page->conteudo=$la;
      print $page->display_page();
      break;

     case "logof":
      $page->titulo="Logof";
      $msg='<p class="message message-error message-closable">Do you want exit ?</p>';
      $page->conteudo=$msg."Do you want exit ? <br><a href=\"auth.php?page=logofOK\"><b>YES</b></a>";
      print $page->display_page();
      break;

    case "logofOK":
      $_SESSION=session_destroy(); 
      print "<meta HTTP-EQUIV='refresh' CONTENT='1; URL=../view/login.php'>";
      break;

//////////////////////// CRUD USER
   case "AddUser":
        $form = new form();
	$token = NoCSRF::generate( 'csrf_token' );
        $values = array(
		  ':hidden'=>'csrf_token:'.$token,
                  'login:text'=>'loginadd:your login', 
                  'password:password'=>'passadd:1234',
                  'E-mail:text'=>'mailadd:your e-mail',                               
                );
        $array = array(
                  "admin", 
                  "user"                              
                );
        $action="auth.php?page=ActionAddUser";
        $la.=$form->StartForm($action);
        $la.=$form->SimpleForm($values);
        $la.=$form->SelectForm("owner: ","owneradd",$array);
        $la.=$form->ExitForm("submit");
        $page->titulo="Add User";
        $page->conteudo=$la;
        print $page->display_page();
      break;

   case "ActionAddUser":
     test_csrf();
     $loginadd=htmlentities($_POST['loginadd']); if(!$loginadd) { print "need login"; exit; }
     $mailadd=htmlentities($_POST['mailadd']); 
     $passadd=htmlentities($_POST['passadd']); if(!$passadd) { print "need a password"; exit; }
     $owneradd=htmlentities($_POST['owneradd']);
     $secret=$frase.$passadd;
      $gen=new Bcrypt(12);
      $bcrypt_hashadd=$gen->hash($secret); 
     $values = array(
                 array(
                  'login'=>sanitize($loginadd), 
                  'pass'=>sanitize($bcrypt_hashadd), 
                  'mail'=>sanitize($mailadd), 
                  'owner'=>sanitize($owneradd),
                 )
                );
     $crud->dbInsert('userronin', $values);
     $page->titulo="Data insert";
     $page->conteudo='<br><br><p class="message message-success message-closable">Added user ok  !</p><br<br>';
     print $page->display_page();
     break;

     case "RmUser":
        $form = new form();
	$token = NoCSRF::generate( 'csrf_token' );
        $values = array(
		  ':hidden'=>'csrf_token:'.$token,
                  'remover:text'=>'userm:ID to remove'                            
                );
        $action="auth.php?page=ActionRmUser";
        $la.=$form->StartForm($action);
        $la.=$form->SimpleForm($values);
        $la.=$form->ExitForm("Remove");
        $page->titulo="Remove user";
        $page->conteudo=$la;
        print $page->display_page();
        break;

   case "ActionRmUser":
	test_csrf();
        $userm=sanitize($_POST['userm']);
        $res = $crud->dbDelete('userronin', 'id', $userm );
        $page->conteudo='<br><br> <p class="message message-success message-closable">User removed!</p><br<br>';
        $page->titulo="User removed";
        print $page->display_page();
        break;

    case "EditUser":
        $form = new form();
	$token = NoCSRF::generate( 'csrf_token' );
        $values = array(
		  ':hidden'=>'csrf_token:'.$token,
                  'editar:text'=>'useredit:ID a editar'                            
                );
        $action="auth.php?page=ViewEditUser";
        $la.=$form->StartForm($action);
        $la.=$form->SimpleForm($values);
        $la.=$form->ExitForm("Edit");
        $page->titulo="Edit User";
        $page->conteudo=$la;
        print $page->display_page();
        break;

    case "ViewEditUser":
        test_csrf();
        $useredit=$_POST['useredit'];
        $stmt = $pdo2->db->prepare("select * FROM userronin WHERE id = ?  ");
        $stmt->bindValue(1, $useredit, PDO::PARAM_STR);  
        $stmt->execute();  
        $res=$stmt->fetchAll(PDO::FETCH_ASSOC);
	$token = NoCSRF::generate( 'csrf_token' );
        foreach($res as $r) {
         	$form = new form();
         	$values = array(
                  'login:text'=>'loginedit:'.$r['login'], 
		  'token:hidden'=>'csrf_token:'.$token,
                  'Password:password'=>'passedit:'.$r['pass'],
                  'E-mail:text'=>'mailedit:'.$r['mail'],
		  'id:hidden'=>'idedituser:'.$r['id']                               
                );
         	$array = array(
                  "admin", 
                  "user"                              
                );
         	$action="auth.php?page=ActionEditUser";
         	$la.=$form->StartForm($action);
         	$la.=$form->SimpleForm($values);
         	$la.=$form->SelectToEdit("owner: ","owneredit",$array,$r['owner']);
         	$la.=$form->ExitForm("submit");
         	$page->titulo="Edit User";
         	$page->conteudo=$la;
         	print $page->display_page();
        }
        break;

   case "ActionEditUser":
	test_csrf();
        $idedituser=htmlentities(sanitize($_POST['idedituser']));
        $loginedit=htmlentities(sanitize($_POST['loginedit']));
        $mailedit=htmlentities(sanitize($_POST['mailedit']));
        $passedit=htmlentities(sanitize($_POST['passedit']));
        $owneredit=htmlentities(sanitize($_POST['owneredit']));
        $secret=$frase.$passedit;
        $gen=new Bcrypt(12);
        $bcrypt_hashedit=$gen->hash($secret); 
        $crud->dbUpdate('userronin', 'login', $loginedit, 'id', $idedituser);
        $crud->dbUpdate('userronin', 'pass', $bcrypt_hashedit, 'id', $idedituser);
        $crud->dbUpdate('userronin', 'mail', $mailedit, 'id', $idedituser);
        $crud->dbUpdate('userronin', 'owner', $owneredit, 'id', $idedituser);
        $page->titulo="Data edit of user";
        $page->conteudo='<br><br><p class="message message-success message-closable">User edited OK !</p><br<br>';
        print $page->display_page();
        break;

    case "ListarUser":
      	$content[0].='<table id="dataTable" class="data" cellpadding="0" cellspacing="0">';
      	$tabela = array(
                  "login", 
                  "e-mail", 
                  "pass",
                  "owner",
                  "id", 
                  "remove",
                  "edit"                             
                );
      	$form=new form();
      	$table=$form->TypeTable($tabela);
      	$content[1].=$table;
     	$res = $crud->rawSelect('SELECT * FROM userronin ORDER BY id DESC'); 
     	$cont=0;
      	$token = NoCSRF::generate( 'csrf_token' );

      	foreach($res as $r) {
               $tabela = array( 
                  $r['login'],
                  $r['mail'],
                  "??????????",
                  $r['owner'],
                  $r['id'],
                  "<form  method=\"post\" action=\"auth.php?page=ActionRmUser\">
		  <input type=\"hidden\" name=\"csrf_token\" value=\"".$token."\" />
                  <input type=\"hidden\" name=\"userm\" value=\"".$r['id']."\" />
                  <input type=\"image\" name=\"pimagem\" id=\"pimagem\" border=\"\" 
                  src=\"../view/imagens/remove.png\" alt=\"\" value=\"valor\" />
                  </form>",
                 "<form method=\"post\" action=\"auth.php?page=ViewEditUser\">
		<input type=\"hidden\" name=\"csrf_token\" value=\"".$token."\" />
                 <input type=\"hidden\" name=\"useredit\" value=\"".$r['id']."\" />
                 <input type=\"image\" name=\"pimagem\" id=\"pimagem\" 
                  src=\"../view/imagens/edit.gif\" alt=\"\" value=\"valor\" />
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
      	$file="auth.php?page=ListarUser";
      	$nameget="list";
      	$pag.=$paginacao->pag($content,$items,$file,$nameget);
      	$page->conteudo=$pag;
      	$page->titulo="List users of Nozes";
      	print $page->display_page();
    break;

     default: 
      $page->titulo="ERRO 404";
      $page->conteudo="<p class=\"message message-error message-closable\">Have error here</b></p>";
      print $page->display_page();
      break;
}

?>
