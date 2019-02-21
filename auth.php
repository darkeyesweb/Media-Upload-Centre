<?php
@session_start();
//error_reporting(0);
 include 'logger.php';
 
 $l = new Logger();
 
 
$userfiledir = "I:/xampp/AuthFiles/DiscordApp/users";
 
 function getLogins() {
 	$userfiledir = "I:/xampp/AuthFiles/DiscordApp/users";
 	$loginraw = file_get_contents($userfiledir, "utf8");
 	$loginlines = explode("\n", $loginraw);
 	$logins = array();
 	forEach($loginlines as $k => $v) {
 		$args = explode("::", $v);
 		 //print_r( $args);
 		$logins[strtolower($args[0])] = array("pass" => $args[1], "id" => $args[2]);
 	}
 	return $logins;
 }
 
 if (!empty($_GET)) {
 	if ($_GET["function"] == "check") {
 		if (isset($_SESSION["username"])) {
 			die("1");
 		} else {
 			die("0");
 		}
 	} elseif ($_GET["function"] == "login") {
    $u = strtolower($_GET["username"]);
    $p = $_GET["password"];
    
    $logins = getLogins();
    
    if (isset($logins[strtolower($u)])) {
      if ($logins[$u]["pass"] == $p) {
        $_SESSION['username'] = $u;
        $_SESSION["userID"] = $logins[$u]["id"];
        $l->newLogin($u);
        die('login');
      } else {
        die('badpass');
      }
    } else {
      die('badusername');
    }
  }
 }