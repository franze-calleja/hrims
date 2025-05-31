<?php
  include("includes/database.php");

  $username = "argon";
  $password = "supa";
  $hash = password_hash($password, PASSWORD_DEFAULT);

  $sql = "INSERT INTO users (user, password) VALUES ('$username', '$hash')";

  try{

    mysqli_query($conn, $sql);
    echo "user registered";
  }
  catch(mysqli_sql_exception){
    echo "could not resgiter user";
  }

  