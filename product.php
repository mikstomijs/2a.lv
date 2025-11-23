<?php
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

   if(!$tableExists) {
      $sql =<<<EOF
      CREATE TABLE PRODUCTS
      (ID INT PRIMARY KEY     NOT NULL,
      NAME           TEXT    NOT NULL,
      DESCRIPTION            TEXT,
      PRICE        CHAR(50));
      
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
    <title>Document</title>
</head>
<body>
        <div class="navbar">
<a href="index.php"><button>Internetveikals</button></a>


        </div>
<?php
$id = $_GET['id'];
$res = $db->query("SELECT * FROM PRODUCTS WHERE ID  = $id");
$row = $res->fetchArray(SQLITE3_ASSOC);



$name = htmlspecialchars($row['NAME']);
$price = htmlspecialchars($row['PRICE']);
$description = htmlspecialchars($row['DESCRIPTION']);




echo "<div class='container_product'>";
echo $name . "<br>";
echo $price . "<br>";
echo $description . "<br>";
echo "<form method='POST'><button type='submit' id='$id' name='cart'>Add to cart</button></form>";
echo "</div>";


if (isset($_POST['cart'])) {
  
    session_start();
  $user_id = $_SESSION['user_id'];
  $product_id = $db->escapeString($id);
  $sql =<<<EOF
    INSERT INTO cart_items (user_id,product_id,quantity)
   VALUES ('$user_id','$product_id',1);
EOF;
  $ret = $db->exec($sql);
  if (!$ret) {
    echo $db->lastErrorMsg();
  }
}

?>


</body>
</html>