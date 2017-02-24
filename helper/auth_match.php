<?php



if($url!="auth" && $url != "login") {
 
 if($_SESSION['userronin']!=NULL) {
  $pdo2 = new crud(); 
  $pdo2->conn();

  $stmt = $pdo2->db->prepare("select * FROM userronin WHERE login = ? ");
  $stmt->bindParam(1, $_SESSION['userronin'] , PDO::PARAM_STR );    
  $stmt->execute();
  $res=$stmt->fetchAll(PDO::FETCH_ASSOC);
  $secret=$_SESSION['passronin'];
 // $bcrypt_hash=password_hash($secret, PASSWORD_BCRYPT,$options_crypto); 

       foreach($res as $r) {
// fix it
          if(strstr($r['pass'],$secret)) { 
           print "<img src=\"../view/images/alerta.png\">
            <h1>ERROR at auth  session</h1> 
            <meta HTTP-EQUIV='refresh' CONTENT='2; URL=../view/login.php'>"; 
           exit;
	  }
	  
	}
  
 } else {

       print "<img src=\"../view/images/alerta.png\">
         <h1>ERROR! at auth session 2</h1> 
         <meta HTTP-EQUIV='refresh' CONTENT='4; URL=../view/login.php'>"; 
	exit;
    
 }

} 
?>



