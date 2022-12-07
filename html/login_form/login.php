<?php




session_start();
 
// Check if the user is already logged in, if yes then redirect him to servers
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: ../serversv2.php");
    exit;
}

$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = test_input($_POST["name"]);
	//possible to do sql injection here.
	//$sql = "SELECT password_salt FROM guacamole_user WHERE entity_id IN (SELECT entity_id FROM guacamole_entity WHERE name = '$name')";
	//$query = $mysqli->query($sql); //Busca el salt del usuario
    if(empty($name)){
        $msg =  "Username field cannot be empty";
    }else{
        try {
            //remove the $dbh = part for syntax completion
            $dbh = require __DIR__."/../../includes/configs/guacDbConfig.php";

            //Looked into this effectively gets all entity_id's with the same name and then kinda joins them so
            //even if there is a group with the same name it shouldn't have an entry in guacamole_user
            $stmt = $dbh->prepare(
                'SELECT password_salt FROM guacamole_user WHERE entity_id IN (SELECT entity_id FROM guacamole_entity WHERE name = :name)');
            $stmt->execute(['name' => $name]);





            if ($stmt->rowCount() == 0) { //Si el usuario introducido no existe
                $msg = "Username and password do not match";
            } else {
                //$salt = $query->fetch_row()[0];
                $salt = $stmt->fetch()[0];

                $password = test_input($_POST["password"]);
                //$salt = $mysqli->real_escape_string($salt); //Escape el string para meterlo en una sentencia sql de forma segura

                $stmt->closeCursor();

                //$sql = "SELECT UNHEX(SHA2(CONCAT('$password', HEX('$salt')), 256))"; //Codifica la contraseña: Salt + SHA-256
                $sql = "SELECT UNHEX(SHA2(CONCAT(:password, HEX(:salt)), 256))"; //Codifica la contraseña: Salt + SHA-256
                //$query = $mysqli->query($sql);
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue("password", $password);
                $stmt->bindValue("salt", $salt);
                /*
                if ($query === FALSE) { //Error al codificar la cadena
                    echo "Could not successfully run query ($sql) from DB: " . $mysqli->error;
                    exit;
                }
                */
                if($stmt->execute() === FALSE){
                    echo "an error has occurred 8999";
                }
                //$password = $query->fetch_row()[0];
                $password = $stmt->fetch()[0];
                $stmt->closeCursor();

                if ($name == '' || $password == '') {
                    $msg = "You must enter all fields";
                } else {
                    //$sql = "SELECT * FROM guacamole_user WHERE password_hash = '$password' AND entity_id IN (SELECT entity_id FROM guacamole_entity WHERE name = '$name')";
                    $sql = "SELECT * FROM guacamole_user WHERE password_hash = :password AND entity_id IN (SELECT entity_id FROM guacamole_entity WHERE name = :name)";
                    //$query = $mysqli->query($sql); //Comprueba si coinciden el usuario y contraseña
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue("password", $password);
                    $stmt->bindValue("name", $name);
                    $stmt->execute();

                    /*
                    if ($query === FALSE) { //Error
                        echo "Could not successfully run query ($sql) from DB: " . $mysqli->error;
                        exit;
                    }
                    */


                    //if ($query->num_rows > 0) { //Contraseña y usuario coinciden
                    if($stmt->rowCount() > 0){
                        $_SESSION["entity_id"] = $stmt->fetch()["entity_id"];
                        $stmt->closeCursor();
                        $sql = "SELECT * FROM guacamole_system_permission 
                                INNER JOIN guacamole_entity on guacamole_system_permission.entity_id = guacamole_entity.entity_id 
                                WHERE guacamole_entity.name = :name and guacamole_system_permission.permission = 'ADMINISTER'";
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindValue("name", $name);
                        $stmt->execute();
                        $admin = FALSE;
                        if($stmt->rowCount()>0){
                            $admin = TRUE;
                        }
                        $stmt->closeCursor();
                        session_start();
                        // Store data in session variables
                        $_SESSION["loggedin"] = true;
                        //$_SESSION["id"] = $id;
                        //using the username is a bit tricky, because in guacamole_entity name isn't a primary key
                        //so there can be duplicate names, however guacamole tries to make sure there is only one name
                        //of each type so there could be user guacadmin and group guacadmin
                        $_SESSION["username"] = $name;
                        $_SESSION["slots"] = 3;
                        $_SESSION["ons"] = 0;
                        $_SESSION["admin"] = $admin;
                        //$mysqli->close();

                        header('Location: ../serversv2.php'); //Envia a la siguiente web
                        exit;
                    }
                    $stmt->closeCursor();
                    $msg = "Username and password do not match";
                }
            }
        }catch(PDOException $pdoErr){
            //TODO: change this
            echo $pdoErr;
        }
    }

	//$mysqli->close();
}

function test_input($data): string
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html" />
	<title>Login</title>
	<meta name="description" content="Login page"/>
	<meta name="keywords" content="login"/>
	<meta charset="UTF-8">
	<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
	<h1>Login</h1>
	<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" >
		<table class="form" border="0">
			<tr>
			<td></td>
				<td style="color:red;">
				<?php echo $msg; ?></td>
			</tr> 
			<tr>
				<th><label for="name"><strong>Username:</strong></label></th>
				<td><input class="inp-text" name="name" id="name" type="text" size="30" /></td>
			</tr>
			<tr>
				<th><label for="name"><strong>Password:</strong></label></th>
				<td><input class="inp-text" name="password" id="password" type="password" size="30" /></td>
			</tr>
			<tr>
			<td></td>
				<td class="submit-button-right">
				<input class="send_btn" type="submit" value="Submit" alt="Submit" title="Submit" />
				
				<input class="send_btn" type="reset" value="Reset" alt="Reset" title="Reset" /></td>
			</tr>
		</table>
	</form>
</body>
</html>
