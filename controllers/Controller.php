<?php
require(__DIR__ . '/../connection.php');

class Controller {
  function __construct() {
    global $pdo;
    $this -> pdo = $pdo;
  }
}
?>
