<?php
session_start();
if (isset($_SESSION['password'])){
header("location:products.php");
}
   class MyDB extends SQLite3 {
      function __construct() {
         $this->open('products.db');
      }
   }
   $db = new MyDB();
   if(!$db) {
      echo $db->lastErrorMsg();
   } else {
      echo "Opened database successfully\n";
   }

   $tableExists = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='LOGININFO'");

   if(!$tableExists) {
      $sql =<<<EOF
      CREATE TABLE LOGININFO
      (ID INT PRIMARY KEY     NOT NULL,
      NAME           TEXT    NOT NULL,
      SURNAME            TEXT,
      EMAIL        CHAR(50) UNIQUE,
      PASSWORD CHAR(255),
      TOKEN  CHAR(50));
;
EOF;

      $ret = $db->exec($sql);
      if(!$ret){
         echo $db->lastErrorMsg();
      } else {
         echo "Table created successfully\n";
      }
   }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internetveikals</title>
       <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
       <link rel="stylesheet" href="css/register.css"
</head>
<body>


<div class="container_main">

<label>Reģistrēšanās</label>
 <form  method="post">


   <label for="name">Vārds:</label>
        <input type="text" id="name" name="name" placeholder="Jūsu vārds" required><br>
        <label for="surname">Uzvārds:</label>
        <input type="text" id="surname" name="surname" placeholder="Jūsu uzvārds" required><br>

                <label for="surname">E-pasts:</label>
        <input type="email" id="email" name="email" placeholder="Jūsu e-pasts" required><br>

   
        <label for="password">Parole:</label>
        <input type="password" id="password" name="password" placeholder="Parole" required><br>

        <button type="submit" name="submit">Reģistrēties</button>


</form>

<label>Esi jau pierakstījies? Ej uz <a href="login.php">login</a></label>
<?php 





 if (isset($_POST["submit"])) {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    echo $name;

   $hashed_password = password_hash($password, PASSWORD_DEFAULT);



        $count = $db->querySingle("SELECT COUNT(*) as count FROM LOGININFO");
    $count++;





        $insertStmt = $db->prepare("    
    INSERT OR IGNORE INTO LOGININFO (ID,NAME,SURNAME,EMAIL,PASSWORD)
    VALUES (:id, :name, :surname, :email, :pw);");
        $insertStmt->bindValue(':id', $count, SQLITE3_INTEGER);
        $insertStmt->bindValue(':name', $name, SQLITE3_TEXT);
        $insertStmt->bindValue(':surname', $surname, SQLITE3_TEXT);
         $insertStmt->bindValue(':email', $email, SQLITE3_TEXT);
                $insertStmt->bindValue(':pw', $hashed_password, SQLITE3_TEXT);
        $insertStmt->execute();



   $ret = $db->exec($sql);
   if ($db->changes()>0) {
      echo "Reģistrācija veiksmīga";
      header("location: login.php");
   }
   else {
      echo "E-pasts jau tiek izmantots";
   }



   }
   $db->close();
   
 





?>



</div>


   
</body>
</html> 