<?php
include("includes/database.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register User</title>
</head>
<body>
  
  <form action="register_user.php" method="POST">
    <h2>Welcome to EUC HRIMS</h2>

    <label for="id">ID:</label>
    <input type="number" name="ID">
    <br>

    Username:
    <input type="text" name="username">
    <br>

    password:
    <input type="password" name="password">
    <br>

    <input type="submit" name="submit" value="register">

  </form>
</body>
</html>

<?php

  if($_SERVER["REQUEST_METHOD"] == "POST"){
    $ID = filter_input(INPUT_POST, "id", FILTER_SANITIZE_SPECIAL_CHARS);
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);
 
    if(empty($username)){
      echo "please enter a username";
    }
    elseif(empty($password)){
      echo "please enter a password";
    }
    else{
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $sql = "INSERT INTO users (user, password) VALUES ('$username', '$hash')";


      try{
        mysqli_query($conn, $sql);
      echo "you are now registered!";
      }
      catch(mysqli_sql_exception){
        echo "username is taken";
      }
    }
  }

  mysqli_close($conn)

?>