<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
   $_SESSION['name'] = "guest";
   $_SESSION['loggedin'] = false;
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
   $tableExists2 = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='cart_items'");
   $tableExists3 = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='FAVORITES'");
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
  added_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
SQL
);
   }


      if (!$tableExists3) {
   $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS FAVORITES (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  product_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL
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
$_SESSION['user_id'] = $row['ID'];
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
    <title>2a.lv</title>
     <link rel="icon" type="image/x-icon" href="images/con.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>



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
   echo "<button id='btnAccount' onclick=account()>Account</button>";
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
</div>



</div>
</div> <!-- Navbar beigas-->




<?php
if(isset($_POST['logout'])) {
   setcookie('user_login', '', time() - 3600, '/');
   unset($_COOKIE['user_login']);
   $_SESSION['loggedin'] = false;
   session_unset();
   session_destroy();
   header("location: index.php");
   echo '<script>window.location.href = window.location.pathname;</script>';
}
?>




<!-- Galvenais div -->
<div class="container_main">
    <div class="container_filter">
      <!-- Filtru izvade + izvēlne -->

<div class='filter-item'>
   <label>Sort</label>
      <select id="sort">
<option value="default">--Select--</option>
<option value="LtH">Low to high (ascending)</option>
<option value="HtL">High to low (descending)</option>
      </select>
      </div>

<?php
$res = $db->query("SELECT DISTINCT CATEGORY FROM PRODUCTS");
$count = 0;
while($row = $res->fetchArray(SQLITE3_ASSOC) ) {
$category = htmlspecialchars($row['CATEGORY']);
$categoryTrim = str_replace('_', ' ', $category);

      $count++;

if ($count >= 10) {
   echo "<div class='filter-item' style='display: none' id='extrafilter'>
        <label for='$category'>$categoryTrim</label>
        <input type='checkbox' name='filter' id='$category' value='$category'>
      </div>";
} else {
   echo "<div class='filter-item'>
        <label for='$category'>$categoryTrim</label>
        <input type='checkbox' name='filter' id='$category' value='$category'>
      </div>";
}

}

$maxQuery = $db->query("SELECT MAX(PRICE) FROM PRODUCTS");
$maxNumber = $maxQuery->fetchArray(SQLITE3_ASSOC);

$max = ceil($maxNumber["MAX(PRICE)"] / 100) * 100 + 1;


if (isset($_GET['min']) && isset($_GET['max'])) {
   $minGet = $_GET['min'];
   $maxGet = $_GET['max'];
}

?>


<button id="extraFilterBtn" onclick="extraFilter()">See more</button>


<!-- Cenu izvēlne -->
<div id="rangeBox">
    <div class="range-wrapper">
      <form method="get">
        <div class="range-group">
            <input type="range" id="slider0to50" step="1" min="1" max="500" value="<?php if (isset($minGet)) {echo $minGet;} else echo 1 ?>">
            <input type="number" step="1" id="min" name="min" min="1" max="500" required placeholder="Minimal price" value="<?php if (isset($minGet)) {echo $minGet;} else echo 1?>">
        </div>
        <div class="range-group">
            <input type="range" id="slider51to100" step="1" min="500" max="<?= $max+5?>" value="<?php if (isset($maxGet)) {echo $maxGet;} else echo $max ?>">
            <input type="number" step="1" id="max" name="max" min="500" max="<?= $max+5 ?>" required placeholder="Maximum price" value="<?php if (isset($maxGet)) {echo $maxGet;} else echo $max ?>">
        </div>
    </div>
    <button type="submit">Go</button>
   </form>
</div>
<!-- Cenu izvēlnes beigas -->


</div> <!-- Filtru beigas -->

<!-- Produktu izvade -->
    <div class="container_products">
<?php 
if (!empty($_GET['min']) && !empty($_GET['max'])) {
   $min = $_GET['min'];
   $max = $_GET['max'];
   $stmt = $db->prepare("SELECT * FROM PRODUCTS WHERE PRICE >= :min AND PRICE <= :max");
   $stmt->bindValue(':min', $min, SQLITE3_FLOAT);
      $stmt->bindValue(':max', $max, SQLITE3_FLOAT);
   $res = $stmt->execute();

if (!$res->fetchArray(SQLITE3_ASSOC)) {
   echo "No products match selected filters.";
} 

}
else {$res = $db->query("SELECT * FROM PRODUCTS");};


while($row = $res->fetchArray(SQLITE3_ASSOC) ) {
    $id = $row['ID'];
    $name = htmlspecialchars($row['NAME']);
    $price = htmlspecialchars($row['PRICE']);
    $category = htmlspecialchars($row['CATEGORY']);

    echo '<a class="product-card" href="product.php?id=' . urlencode($id) . '" id="'. $category . '" name="product" value="' . $category . '" price="' . $price . '">';
    echo '  <div class="card-inner">';
    echo "    <h3>$name</h3>";
    echo "    <div class='price'>$price €</div>";
    echo '  </div>';
    echo '</a>';
}

?>
</div>
<!-- Produktu izvades beigas-->


<div class="temp"></div>

</div>
<script type="text/javascript">

    document.getElementById("btnRegister").onclick = function () {
    
    location.href = "register.php";
    };
     document.getElementById("btnLogin").onclick = function () {
        location.href = "login.php";
    };


</script>
<script src="js/filter.js"></script>
<script src="js/slider.js"></script>
<script src="js/isShown.js"></script>
<script src="js/cart.js"></script>
</body>
</html>