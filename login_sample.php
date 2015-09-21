<?php

// ühenduse loomiseks kasuta MYSQL
require_once("../config.php");
$database = "if15_skmw";
$mysqli = new mysqli($servername, $username, $password, $database);

//check connection_aborted
if($mysqli->connect_error){
	die("connect error ".mysqli_connect_error());
	//kui database näiteks muuta "if15_skmw" mingi typo, siis viskab access denied ja
	//ühendust pole
}

  // muuutujad errorite jaoks
	$email_error = "";
	$password_error = "";
	$create_email_error = "";
	$create_password_error = "";

  // muutujad väärtuste jaoks
	$email = "";
	$password = "";
	$create_email = "";
	$create_password = "";

//nupuvajutuse kuulamine
	if($_SERVER["REQUEST_METHOD"] == "POST") {

    // *********************
    // **** LOGI SISSE *****
    // *********************
		if(isset($_POST["login"])){

			if ( empty($_POST["email"]) ) {
				$email_error = "See väli on kohustuslik";
			}else{
        // puhastame muutuja võimalikest üleliigsetest sümbolitest
				$email = cleanInput($_POST["email"]);
			}

			if ( empty($_POST["password"]) ) {
				$password_error = "See väli on kohustuslik";
			}else{
				$password = cleanInput($_POST["password"]);
			}

      // Kui oleme siia jõudnud, võime kasutaja sisse logida
			if($password_error == "" && $email_error == ""){
				echo "Võib sisse logida! Kasutajanimi on ".$email." ja parool on ".$password;
			}

		} // login if end

    // *********************
    // ** LOO KASUTAJA *****
    // *********************
    if(isset($_POST["create"])){

			if ( empty($_POST["create_email"]) ) {
				$create_email_error = "See väli on kohustuslik";
			}else{
				$create_email = cleanInput($_POST["create_email"]);
			}

			if ( empty($_POST["create_password"]) ) {
				$create_password_error = "See väli on kohustuslik";
			} else {
				if(strlen($_POST["create_password"]) < 8) {
					$create_password_error = "Peab olema vähemalt 8 tähemärki pikk!";
				}else{
					$create_password = cleanInput($_POST["create_password"]);
				}
			}

			if(	$create_email_error == "" && $create_password_error == ""){
				//see sha512 on võetud algoritmidest php.com.
				echo hash("sha512", $create_password);
				echo "Võib kasutajat luua! Kasutajanimi on ".$create_email." ja parool on ".$create_password;
      
				//tekitan parooli räsi muutujasse "hash"
				$hash = hash("sha512", $create_password);
				
				//salvestan andmebaasi stmt - statement. sulgudesse tuleb mysqli lause.
				//küsimärgid values. sisesta tabelisse väärtused.
				$stmt = $mysqli->prepare("INSERT INTO user_sample (email, password) VALUES (?,?)");
				
				//paneme muutujad küsimärkide asemel. "ss" ehk stringid on parool ja email. iga muutuja kohta 1 täht "s"
				$stmt->bind_param("ss", $create_email, $hash);
				
				//viib täide sisestuse
				$stmt->execute();
				
				//sulge
				$stmt->close();
				
				//putty mysql: select * from user_sample;
	  }

    } // create if end

	}

  // funktsioon, mis eemaldab kõikvõimaliku üleliigse tekstist
  //andmebaasi lisamiseks on see puhastus vajalik (kui andmed mis sisestatud, sobivad)
  function cleanInput($data) {
	  //eemaldab üleliigsed tühikud ja muud sümbolid (tab, enter)
  	$data = trim($data);
	// eemaldab backslashid "\"
  	$data = stripslashes($data);
	//muudab sümbolid masinkeelde. nad on tekstikujul.
  	$data = htmlspecialchars($data);
  	return $data;
  }

  //sulgeme MYSQL ühenduse.
  $mysqli->close();
  
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
</head>
<body>

  <h2>Log in</h2>
  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" >
  	<input name="email" type="email" placeholder="E-post" value="<?php echo $email; ?>"> <?php echo $email_error; ?><br><br>
  	<input name="password" type="password" placeholder="Parool" value="<?php echo $password; ?>"> <?php echo $password_error; ?><br><br>
  	<input type="submit" name="login" value="Log in">
  </form>

  <h2>Create user</h2>
  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" >
  	<input name="create_email" type="email" placeholder="E-post" value="<?php echo $create_email; ?>"> <?php echo $create_email_error; ?><br><br>
  	<input name="create_password" type="password" placeholder="Parool"> <?php echo $create_password_error; ?> <br><br>
  	<input type="submit" name="create" value="Create user">
  </form>
<body>
<html>
