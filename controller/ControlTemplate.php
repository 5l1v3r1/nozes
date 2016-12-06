<?php

require "boot.php";

switch ($url) {

//////////////// CRUD Templates
   case "ActionRmTemplate":
	test_csrf();
        $userm=htmlentities($_POST['TemplateRm']);
        $res = $crud->dbDelete('cmdtemplate', 'id', $userm );
        $page->conteudo='<br><br><p class="message message-success message-closable">CMD template removed!</p><br<br>';
        $page->titulo="User removed";
        print $page->display_page();
        break;

   case "ActionViewTemplate":
	test_csrf();
        $cmd=htmlentities($_POST['CMD']);
        $page->conteudo='<br><br><p class="message message-success message-closable"><pre>'.$cmd.'</pre></p><br<br>';
        $page->titulo="Template CMD view";
        print $page->display_page();
        break;

  case "AddTemplate":
        $form = new form();
	$token = NoCSRF::generate( 'csrf_token' );
        $values = array(
		  ':hidden'=>'csrf_token:'.$token,
                  'name:text'=>'nameadd:Tool name', 
                  'date:text'=>'dateadd:'.gmdate("Y-m-d h:i"),                         
                );
        $action="ControlTemplate.php?page=ActionAddTemplate";
        $la.=$form->StartForm($action);
        $la.=$form->SimpleForm($values);
        $la.=$form->TextForm("CMD: ","cmdadd","command of tool here: \n You can use \$host, \$port, \$log MACROs to get input of forms\n");
        $la.=$form->ExitForm("submit");
        $page->titulo="Add Template of tool";
        $page->conteudo=$la;
        print $page->display_page();
      	break;

   case "ActionAddTemplate":
     	test_csrf();
     	$nameadd=htmlentities($_POST['nameadd']); 
     	if(!$nameadd) { print "need name"; exit; }
     	$dateadd=htmlentities($_POST['dateadd']); 
     	$cmdadd=$_POST['cmdadd']; if(!$cmdadd) { print "need a command"; exit; }
     
     	$values = array(
                 array(
                  'name'=> sanitize($nameadd), 
                  'date'=> sanitize($dateadd), 
                  'command'=> sanitizecmd($cmdadd), 
                 )
                );
     	$crud->dbInsert('cmdtemplate', $values);
     	$page->titulo="Data insert at Template table";
     	$page->conteudo='<br><br> <p class="message message-success message-closable">Added template ok!</p><br<br>';
     	print $page->display_page();
     	break;

 case "ViewEditTemplate":
        test_csrf();
        $templateedit=$_POST['TemplateEdit'];
        $stmt = $pdo2->db->prepare("select * FROM cmdtemplate WHERE id = ?  ");
        $stmt->bindValue(1, $templateedit, PDO::PARAM_STR);  
        $stmt->execute();  
        $res=$stmt->fetchAll(PDO::FETCH_ASSOC);
	$token = NoCSRF::generate( 'csrf_token' );

        foreach($res as $r) {

        	$form = new form();
         	$values = array(
                  'name:text'=>'nameedit:'.htmlentities($r['name']), 
		  'token:hidden'=>'csrf_token:'.$token,
		  'id:hidden'=>'idedit:'.$templateedit,
                  'date:text'=>'dateedit:'.$r['date'],                            
                );
         	$action="ControlTemplate.php?page=ActionEditTemplate";
         	$la.=$form->StartForm($action);
         	$la.=$form->SimpleForm($values);
	 	$la.=$form->TextForm("CMD: ","cmdedit",$r['command']);
         	$la.=$form->ExitForm("submit");
         	$page->titulo="Edit Template";
         	$page->conteudo=$la;
         	print $page->display_page();
        }
        break;

   case "ActionEditTemplate":
	test_csrf();
        $name=sanitize(htmlentities($_POST['nameedit']));
        $date=sanitize(htmlentities($_POST['dateedit']));
        $cmd=sanitizecmd($_POST['cmdedit']);
        $id=sanitize(htmlentities($_POST['idedit']));
        $crud->dbUpdate('cmdtemplate', 'name', $name, 'id', $id);
        $crud->dbUpdate('cmdtemplate', 'date', $date, 'id', $id);
        $crud->dbUpdate('cmdtemplate', 'command', $cmd, 'id', $id);
        $page->titulo="Data edit of template";
        $page->conteudo='<br><br>
                      <p class="message message-success message-closable">Template edited OK !$</p><br<br>';
        print $page->display_page();
        break;


    case "ListTemplates":
      	$content[0].='<table id="dataTable" class="data" cellpadding="0" cellspacing="0">';
      	$tabela = array(
          "id", 
          "Name", 
          "Date",
          "Edit",
          "Remove"                             
        );
      	$form=new form();
      	$table=$form->TypeTable($tabela);
      	$content[1].=$table;
      	$res = $crud->rawSelect('SELECT * FROM cmdtemplate ORDER BY id DESC'); 
      	$cont=0;
      	$token = NoCSRF::generate( 'csrf_token' );

      	foreach($res as $r) {
                $tabela = array( 
                  $r['id'],
                  $r['name'],
                  $r['date'],
                  "<form  method=\"post\" action=\"ControlTemplate.php?page=ViewEditTemplate\">
		  <input type=\"hidden\" name=\"csrf_token\" value=\"".$token."\" />
                  <input type=\"hidden\" name=\"TemplateEdit\" value=\"".$r['id']."\" />
                  <input type=\"image\" name=\"pimagem\" id=\"pimagem\" border=\"\" 
                  src=\"../view/imagens/edit.gif\" alt=\"\" value=\"valor\" />
                  </form>",
                  "<form  method=\"post\" action=\"ControlTemplate.php?page=ActionRmTemplate\">
		  <input type=\"hidden\" name=\"csrf_token\" value=\"".$token."\" />
                  <input type=\"hidden\" name=\"TemplateRm\" value=\"".$r['id']."\" />
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
      	$file="ControlTemplate.php?page=ListTemplates";
      	$nameget="list";
      	$pag.=$paginacao->pag($content,$items,$file,$nameget);
      	$page->conteudo=$pag;
      	$page->titulo="List Templates of Nozes";
      	print $page->display_page();
    	break;

    default: 
      	$page->titulo="ERRO 404";
      	$page->conteudo="<p class=\"message message-error message-closable\">Have error here <b></b></p>";
      	print $page->display_page();
    	break;
}



//ob_flush();

?>
