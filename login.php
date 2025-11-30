<?php
session_start();

   class MyDB extends SQLite3 {
      function __construct() {
         $this->open('products.db');
      }
   }
   $db = new MyDB();
   if(!$db) {
      echo $db->lastErrorMsg();
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
EOF;

      $ret = $db->exec($sql);
      if(!$ret){
         echo $db->lastErrorMsg();
      } 
     
    
   }

?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internetveikals</title>
</head>
<body>


<div class="container_main">

<label>Login</label>
 <form  method="post">


   
                <label for="surname">E-pasts:</label>
        <input type="text" id="email" name="email" placeholder="Jūsu e-pasts" required><br>

   
        <label for="password">Parole:</label>
        <input type="password" id="password" name="password" placeholder="Parole" required><br>

        <input type="checkbox" name="rememberme" value="1"> Atcerēties mani

        <button type="submit" name="submit">Login</button>


</form>


<?php 

$loggedIn = false;



 if (isset($_POST["submit"])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
      $stmt = $db->prepare('SELECT * FROM LOGININFO WHERE EMAIl = :email');
      $stmt->bindValue(':email', $email);
          $result = $stmt->execute();
            $row = $result->fetchArray(SQLITE3_ASSOC);
       
if ($row) {
  
        if (password_verify($password, $row['PASSWORD'])) {
            $loggedIn = true;
            $_SESSION['loggedin'] = true;
            $_SESSION['name'] = $row['NAME'];
            $_SESSION['user_id'] = $row['ID'];
          

        } else {
            echo "Nepareizs epasts vai parole";
            $loggedIn = false;
            
        }
    } else {
        echo "Nepareizs epasts vai parole";
    }

 }
   




if ($loggedIn) {
   if (!empty($_POST["rememberme"]))
            {
                $id = $row['ID'];    

                $token = bin2hex(random_bytes(32));
      
                setcookie("user_login", $token, time() +
                                    (86400 * 30));
                $stmt = $db->prepare('UPDATE LOGININFO SET TOKEN = :token WHERE ID = :id');
                $stmt->bindValue(':token', $token, SQLITE3_TEXT);
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                $result = $stmt->execute();
              
                if(!$result){
                   echo $db->lastErrorMsg();
                } 
           

                $_SESSION["cookie"] = $token;
                header("location:index.php");
            }
            else
            {
                if (isset($_COOKIE["user_login"]))
                {
                    setcookie("user_login", "");
                    header("location:index.php");
                }
                else {header("location:index.php");}
              
            }
            
        }
        else
        {
            $message = "Invalid Login Credentials";
        }



   $db->close();
 





?>



</div>


   
</body>
</html> 