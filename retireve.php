<?php

include("includes/database.php");

// $sql = "SELECT * FROM users WHERE user = 'franze'";

$sql = "SELECT * FROM users";

$result = mysqli_query($conn, $sql ); 

// retrieve 1 row fron a table

// if(mysqli_num_rows($result) > 0){

//   $row = mysqli_fetch_assoc($result);
//   echo $row['id'] . "<br>";
//   echo $row['user'] . "<br>";
//   echo $row['reg_date'] . "<br>";
// }
// else{
//   echo "0 results";
// }

// retrieve multiple row from a table
if(mysqli_num_rows($result) > 0){
  while($row = mysqli_fetch_assoc($result)){
    echo $row['id'] . "<br>";
    echo $row['user'] . "<br>";
    echo $row['reg_date'] . "<br>";
  }

}
else{
  echo "0 results";
}

mysqli_close($conn);