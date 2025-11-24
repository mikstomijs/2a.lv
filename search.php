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
$search = trim($_GET['search']);
$pattern = '%' . strtolower($search) . '%';
$stmt = $db->prepare('SELECT ID, NAME, DESCRIPTIOn, PRICE FROM PRODUCTS WHERE LOWER(NAME) LIKE :p OR LOWER(DESCRIPTION) LIKE :p');
$stmt->bindValue(':p', $pattern, SQLITE3_TEXT);
$res = $stmt->execute();

$results = [];

while($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $results[] = $row;
}


if (count($results) === 0) {
    echo '<p>No results for: ' . htmlspecialchars($search);
    exit;
}

echo '<p>Search results for ' . htmlspecialchars($search) . '</p>';
echo '<div>';
foreach ($results as $r) {
    $name = htmlspecialchars($r['NAME']);
    $price = htmlspecialchars($r['PRICE']);
    $id = $r['ID'];
     echo '<a class="product-card" href="product.php?id=' . urlencode($id) . '">';
    echo '  <div class="card-inner">';
    echo "    <h3>$name</h3>";
    echo "    <div class='price'>$price €</div>";
    echo '  </div>';
    echo '</a>';


}
echo '</div>'
?>
</body>

