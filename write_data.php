<?php

// this path should point to your configuration file.
include('database_config.php');

$data_array = json_decode(file_get_contents('php://input'), true);

try {
  $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  // First stage is to get all column names from the table and store
  // them in $col_names array.
  $stmt = $conn->prepare("SHOW COLUMNS FROM `$table`");
  $stmt->execute();
  $col_names = array();
  while($row = $stmt->fetchColumn()) {
    $col_names[] = $row;
  }
  // Second stage is to create prepared SQL statement using the column
  // names as a guide to what values might be in the JSON.
  // If a value is missing from a particular trial, then NULL is inserted
  $sql = "INSERT INTO $table VALUES(";
  for($i = 0; $i < count($col_names); $i++){
    $name = $col_names[$i];
    $sql .= ":$name";
    if($i != count($col_names)-1){
      $sql .= ", ";
    }
  }
  $sql .= ");";
  $insertstmt = $conn->prepare($sql);
  for($i=0; $i < count($data_array); $i++){
    for($j = 0; $j < count($col_names); $j++){
      $colname = $col_names[$j];
      if(!isset($data_array[$i][$colname])){
        $insertstmt->bindValue(":$colname", null, PDO::PARAM_NULL);
      } else {
        $insertstmt->bindValue(":$colname", $data_array[$i][$colname]);
      }
    }
    $insertstmt->execute();
  }
  echo '{"success": true}';

// Send error message to the server log if error connecting to the database
if (!mysqli_connect("localhost","bad_user","bad_password","my_db")) {
    error_log("Failed to connect to database!", 0);
}

// Send email to administrator if we run out of FOO
if (!($foo = allocate_new_foo())) {
    error_log("Oh no! We are out of FOOs!", 1, "admin@example.com");
}

  
} catch(PDOException $e) {
  echo '{"success": false, "message": ' . $e->getMessage();
}
$conn = null;
?>