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

   $tableExists = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='PRODUCTS'");

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


if(isset($_POST['favorite']) && isset($_GET['id'])) {
     $id = $_GET['id'];

     $user_id = $_SESSION['user_id'];
  $product_id = $db->escapeString($id);

      $checkStmt = $db->prepare("SELECT * FROM FAVORITES WHERE user_id = :uid AND product_id = :pid");
    $checkStmt->bindValue(':uid', $user_id, SQLITE3_INTEGER);
    $checkStmt->bindValue(':pid', $product_id, SQLITE3_INTEGER);
    $checkRes = $checkStmt->execute();
    $existing = $checkRes->fetchArray(SQLITE3_ASSOC);


    if ($existing) {

        $updateStmt = $db->prepare("DELETE FROM FAVORITES WHERE user_id = :uid AND product_id = :pid");
        $updateStmt->bindValue(':uid', $user_id, SQLITE3_INTEGER);
        $updateStmt->bindValue(':pid', $product_id, SQLITE3_INTEGER);
        $updateStmt->execute();
    } else {

        $insertStmt = $db->prepare("INSERT INTO FAVORITES (user_id, product_id) VALUES (:uid, :pid)");
        $insertStmt->bindValue(':uid', $user_id, SQLITE3_INTEGER);
        $insertStmt->bindValue(':pid', $product_id, SQLITE3_INTEGER);
   
        $insertStmt->execute();
    }


      header("Location: product.php?id=$id");
    exit;
}  








if (isset($_POST['cart']) && isset($_GET['id'])) {
  $id = $_GET['id'];

  $user_id = $_SESSION['user_id'];
  $product_id = $db->escapeString($id);
  $qty = $_POST['quantity'];
  

     $checkStmt = $db->prepare("SELECT * FROM cart_items WHERE user_id = :uid AND product_id = :pid");
    $checkStmt->bindValue(':uid', $user_id, SQLITE3_INTEGER);
    $checkStmt->bindValue(':pid', $product_id, SQLITE3_INTEGER);
    $checkRes = $checkStmt->execute();
    $existing = $checkRes->fetchArray(SQLITE3_ASSOC);

    if ($existing) {

        $updateStmt = $db->prepare("UPDATE cart_items SET quantity = quantity + :qty WHERE user_id = :uid AND product_id = :pid");
        $updateStmt->bindValue(':qty', $qty, SQLITE3_INTEGER);
        $updateStmt->bindValue(':uid', $user_id, SQLITE3_INTEGER);
        $updateStmt->bindValue(':pid', $product_id, SQLITE3_INTEGER);
        $updateStmt->execute();
    } else {

        $insertStmt = $db->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (:uid, :pid, :qty)");
        $insertStmt->bindValue(':uid', $user_id, SQLITE3_INTEGER);
        $insertStmt->bindValue(':pid', $product_id, SQLITE3_INTEGER);
        $insertStmt->bindValue(':qty', $qty, SQLITE3_INTEGER);
        $insertStmt->execute();
    }


      header("Location: product.php?id=$id");
    exit;
}  


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/product.css">
      <link rel="icon" type="image/x-icon" href="images/icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
   <!-- Navbar -->
<!-- Navbar -->
<div class="navbar">
   <div class="navbar-left">
<p><a href="index.php">2a.lv</a></p>
<p>Welcome, <?php if(isset($_SESSION['loggedin'])) {echo $_SESSION['name'] . "!";}else {echo "guest" . "!";} ?></p>
</div>






<div class="navbar-center">
<!-- Meklēšanas josla -->
<form method="get" action="search.php" class="form" ><input type="text" name="search" required placeholder="Search anything" class="search_input" >
<button type="submit" class="search_button" id="navbar_button">Search!</button></form>
<!-- Meklēšanas josla beigas -->
</div>

<div class="navbar-right">


   <!-- Iepirkumu grozs  -->






<?php
if ($_SESSION['loggedin'] === false) {
   echo "<button id='btnRegister' id='navbar_button'>Register</button>";
   echo "<button id='btnLogin' id='navbar_button'>Login</button>";
} else {
   echo '<div class="account_main">';
   echo "<button id='btnAccount' id='navbar_button' onclick=account()>Account</button>";
}
?>
<div class="account" id="account" style='display: none'>

<div class=cart_main><button class='cart_button' onclick=cart() id='navbar_button'>Shopping cart</button>
<div class="cart" id="cart" style='display: none'>




<?php
if ($_SESSION['loggedin'] === true) {
$totalCount = 0; $totalPrice = 0;
$user_id = $_SESSION['user_id'];
$sql = <<<EOF
   SELECT * FROM cart_items WHERE user_id = $user_id
   EOF;
$res = $db->query($sql);

$hasItems = false;

if (isset($_POST['remove'])) {
   $product_id = $_POST['remove'];
   $sql = <<<EOF
      DELETE FROM cart_items WHERE user_id = $user_id AND product_id = $product_id
      EOF;

   $res3 = $db->query($sql);
}

while($row = $res->fetchArray(SQLITE3_ASSOC)) {

   $product = $row['product_id'];
   $qty = $row['quantity']; 
   $res2 = $db->query("SELECT * FROM PRODUCTS WHERE ID = $product");
while($row2 = $res2->fetchArray(SQLITE3_ASSOC) ) {
   $hasItems = true;
    $name = htmlspecialchars($row2['NAME']);
    $price = $row2['PRICE'];

    echo "<p>" . $name . " " . $price . "€ " . $qty . "x" . "</p>" . "<form method='post'><button type=submit name='remove' value='$product'>Remove</button></form>";
    $totalCount += $qty;
    $totalPrice += $price;


}
}

if (!$hasItems) {
   echo "<p>Shopping cart is empty.</p>";
} else {
 echo "Total item count: " . $totalCount . "<br>";
echo "Total sum: " .  $totalPrice;
}
}


?>
</div>
</div>


<!-- Iepirkumu grozs beigas -->

<div class=favorites_main><button class='favorites_button' onclick=favorites() id='navbar_button'>Favorites</button>
<div class="favorites" id="favorites" style='display: none'>




<?php
if (isset($_SESSION['user_id'])) {

   $user_id = $_SESSION['user_id'];
$sql = <<<EOF
   SELECT * FROM FAVORITES WHERE user_id = $user_id
   EOF;
$res = $db->query($sql);

$hasItems = false;

if (isset($_POST['removeFavorite'])) {
   $product_id = $_POST['removeFavorite'];
   $sql = <<<EOF
      DELETE FROM FAVORITES WHERE user_id = $user_id AND product_id = $product_id
      EOF;

   $res2 = $db->query($sql);
}

while($row = $res->fetchArray(SQLITE3_ASSOC)) {

   $product = $row['product_id'];
      $res3 = $db->query("SELECT * FROM PRODUCTS WHERE ID = $product");
      while($row2 = $res3->fetchArray(SQLITE3_ASSOC) ) {
         $hasItems = true;
         $name = htmlspecialchars($row2['NAME']);
         $price = $row2['PRICE'];

         echo "<a href='product.php?id=" . urlencode($product) . "'<p>" . $name . " " . $price . "€ " . "<form method='post' style='display:inline'><button type='submit' name='removeFavorite' value='$product'>Remove</button></form></p></a>";
      }
}

if (!$hasItems) {
   echo "<p>No favorites found.</p>";
} 

}



?>
</div>
</div>

</div>
</div>

<!-- Logout poga -->
 <?php
 if ($_SESSION['loggedin'] === true) {
 echo '<form method="post" >';
echo '<button type="submit" name="logout" class="logout_button" id="navbar_button">Logout</button>';
echo '</form>';
 }
 ?>

<!-- Logout poga beigas-->

</div>
</div> <!-- Navbar beigas-->



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

<?php
$id = $_GET['id'];
$stmt = $db->prepare("SELECT * FROM PRODUCTS WHERE ID = :id");
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$res = $stmt->execute();
$row = $res->fetchArray(SQLITE3_ASSOC);


if ($row) {

$name = htmlspecialchars($row['NAME']);
$price = htmlspecialchars($row['PRICE']);
$description = htmlspecialchars($row['DESCRIPTION']);
   echo "<div class='container_product'>";

echo '<div class="left">';

echo '<h3>' . $name . '</h3>';
echo $price . " €" . "<br>";
echo $description;

echo '</div>';


if (isset($_SESSION['user_id'])) {
   echo "<form method='POST' class='formQ'> <div class='quantity'><label for='quantity'>Quantity</label><input type='number' id='quantity' name='quantity' min='1' max='999'  value='1'></div><button type='submit' id='<?php echo $id?>' name='cart'>Add to cart</button></form>";
       $user_id = $_SESSION['user_id'];
  $product_id = $db->escapeString($id);

      $checkStmt = $db->prepare("SELECT * FROM FAVORITES WHERE user_id = :uid AND product_id = :pid");
    $checkStmt->bindValue(':uid', $user_id, SQLITE3_INTEGER);
    $checkStmt->bindValue(':pid', $product_id, SQLITE3_INTEGER);
    $checkRes = $checkStmt->execute();
    $existing = $checkRes->fetchArray(SQLITE3_ASSOC);
if ($existing) {
   echo "<form method='POST'><button type='submit' name='favorite' id='image'><img src='images/heart.png' class='image'></button></form>";
} else {
   echo "<form method='POST'><button type='submit' name='favorite' id='image'><img src='images/empty_heart.png' class='image' ></button></form>";
}

echo "</div>";


}
}
 

else {
   echo "Product not found.";
}






?>









<script type="text/javascript">

    document.getElementById("btnRegister").onclick = function () {
    
    location.href = "register.php";
    };
     document.getElementById("btnLogin").onclick = function () {
        location.href = "login.php";
    };


</script>
<script src="js/cart.js"></script>
</body>
</html>