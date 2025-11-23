<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
   $_SESSION['name'] = "viesi";
}


   class MyDB extends SQLite3 {
      function __construct() {
         $this->open('products.db');
      }
   }
   $db = new MyDB();
   if(!$db) {
      echo $db->lastErrorMsg();
   }

   $tableExists = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='PRODUCTS'");
   $tableExists2 = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='CART'");
   if(!$tableExists) {
      $sql =<<<EOF
      CREATE TABLE PRODUCTS
      (ID INT PRIMARY KEY     NOT NULL,
      NAME           TEXT    NOT NULL,
      DESCRIPTION            TEXT,
      PRICE       DECIMAL(10,2),
      IMAGE_URL TEXT,
      CATEGORY TEXT);
      
EOF;

      $ret = $db->exec($sql);
      if(!$ret){
         echo $db->lastErrorMsg();
      } 
   }


   if (!$tableExists2) {
   $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS cart_items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  product_id INTEGER NOT NULL,
  quantity INTEGER NOT NULL DEFAULT 1,
  added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(user_id, product_id)
);
SQL
);
   }




$res = $db->query("SELECT * FROM LOGININFO");




if(isset($_COOKIE['user_login'])) {

while($row = $res->fetchArray(SQLITE3_ASSOC) ) {
if ($_COOKIE['user_login'] == $row['TOKEN']){
$_SESSION['loggedin'] = true;
$_SESSION['name']  = $row['NAME'];


break;
}
}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <title>Document</title>
</head>
<body>
    
    <div class="navbar">
<p>Internetveikals</p>

   <p>Sveicināts, <?php if(isset($_SESSION['loggedin'])) {echo $_SESSION['name'] . "!";}else {echo "viesi" . "!";} 
   ; ?></p>


<?php
if (!isset($_SESSION['loggedin'])) {
   echo "<button id='btnRegister'>Reģistrēties</button>";
   echo "<button id='btnLogin'>Ienākt</button>";
} else {
   echo "<button class='cart_button' onclick=cart() >Iepirkumu grozs</button>";
}
?>
<div class="cart" id="cart" style='display: none'>
<?php
if (isset($_SESSION['user_id'])) {$totalCount = 0;
$user_id = $_SESSION['user_id'];;
$sql = <<<EOF
      SELECT * FROM cart_items WHERE user_id = $user_id
      EOF;
$res = $db->query($sql);

while($row = $res->fetchArray(SQLITE3_ASSOC)) {
   $product = $row['product_id'];
   $qty = $row['quantity']; 
   $res2 = $db->query("SELECT * FROM PRODUCTS WHERE ID = $product");
while($row2 = $res2->fetchArray(SQLITE3_ASSOC) ) {
    $name = htmlspecialchars($row2['NAME']);
    $price = $row2['PRICE'] * $qty;

    echo $name . " " . $price . " " . $qty;
    $totalCount += $qty;
  
}
}
echo "Kopējais priekšmetu skaits: " . $totalCount; }

?>



</div>

<input type="text" placeholder="Meklēt">

</div>



<form method="post" style="display:inline;">

<?php 
if (isset($_SESSION['loggedin']))
echo '<button type="submit" name="logout">Logout</button>'

?>

</form>
<?php

if(isset($_POST['logout'])) {
   setcookie('user_login', '', time() - 3600, '/');
   unset($_COOKIE['user_login']);
   session_unset();
   session_destroy();
   header("location: index.php");
   echo '<script>window.location.href = window.location.pathname;</script>';
}

?>


</div>

<div class="container_main">
    <div class="container_filter">
<?php


   
$res = $db->query("SELECT DISTINCT CATEGORY FROM PRODUCTS");

while($row = $res->fetchArray(SQLITE3_ASSOC) ) {
$category = htmlspecialchars($row['CATEGORY']);
$categoryTrim = str_replace('_', ' ', $category);
echo $categoryTrim .  "<input type='checkbox' name='filter' id=$category value=$category>". "<br>"  ;

}



?>
</div>
    <div class="container_products">
<?php 



$res = $db->query("SELECT * FROM PRODUCTS");
while($row = $res->fetchArray(SQLITE3_ASSOC) ) {
    $id = $row['ID'];
    $name = htmlspecialchars($row['NAME']);
    $price = htmlspecialchars($row['PRICE']);
    $category = htmlspecialchars($row['CATEGORY']);

    echo '<a class="product-card" href="product.php?id=' . urlencode($id) . '" id="'. $category . '" name="product" value="' . $category . '">';
    echo '  <div class="card-inner">';
    echo "    <h3>$name</h3>";
    echo "    <div class='price'>$price</div>";
    echo '  </div>';
    echo '</a>';
}

?>
</div>


</div>
<script type="text/javascript">

    document.getElementById("btnRegister").onclick = function () {
    
    location.href = "register.php";
    };
     document.getElementById("btnLogin").onclick = function () {
        location.href = "login.php";
    };


</script>
<script src="filter.js"></script>
</body>
</html>