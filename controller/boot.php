<?php
//key 
$frase="not have bad way";

// Define encode to UTF-8
header('Content-type: text/html; charset="utf-8"',true);

// header mitigations
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=7776000');
ini_set('session.cookie_httponly',1);
ini_set('session.cookie_secure', 1);  

//if not debug
error_reporting(0);
ini_set('display_errors', 0);
//if use debug
//error_reporting(E_ALL);


// include  classes
  require "../helper/class.GhostPage.php";
  require "../helper/class.crud.php";
  require "../helper/class.form.php";
  require "../helper/class.paginate.php";
  require "../helper/class.Bcrypt.php";
  require "../helper/nocsrf.php";
  require "../helper/secure_validation.php";

//get  page
$url=$_GET['page'];

// vars start
$pag=NULL;
$janela="";
$la="";
$content[0]="";
$content[1]="";

//Start crud
$crud = new crud();

//views load
$page = new GhostPage();
$page->templatefile = "../view/AuthAdmin.html";
$page->varnamelist = "titulo,conteudo";

// item per pagination in table
$items=22; 

session_start();

// load auth match condition
include "../helper/auth_match.php";
//load file functions
include "../helper/file_ops.php";
//load func validate
include "../helper/validate_ops.php";

?>
