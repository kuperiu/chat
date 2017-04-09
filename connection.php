<?php
$path = __DIR__ . '/chatDB.db';
$pdo = new PDO('sqlite:' . $path) or
  exit(json_encode(['status' => 'error', 'message' => 'Cannot open DB']));
$pdo -> setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
$pdo -> exec( 'PRAGMA foreign_keys = ON' );
?>
