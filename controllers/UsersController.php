<?php
class UsersController extends Controller {
  // Create user and return its ID.
  public function createUser() {
    if (!isset($_POST['username'])) {
      return http_response_code(400);
    }

    if (strlen($_POST['username']) > 30 || strlen($_POST['username']) < 4) {
      return json_encode([
        'status' => 'error',
        'message' => 'Username has to be between 4 and 30 characters.'
      ]);
    }

    $query = 'INSERT INTO users (username) VALUES (:username)';
    $username = trim(strtolower($_POST['username']));
    $username = htmlspecialchars($username);

    try {
      $stmt = $this -> pdo -> prepare($query);
      $stmt -> execute(['username' => $username]);

      $_GET['username'] = $username;
      return $this -> getUser();
    } catch (PDOException $e) {
      // UNIQUE constraint failed.
      if ($e -> getCode() === '23000') {
        return json_encode([
          'status' => 'error',
          'message' => 'Username already in use'
        ]);
      } else {
        throw $e;
      }
    }
  }

  // Return user ID by username.
  public function getUser() {
    // If username is not between 4 and 30 characters it doesn't exist.
    // No point in looking for something that you know it doesn't exist.
    if (strlen($_GET['username']) > 30 || strlen($_GET['username']) < 4) {
      return json_encode(['status' => 'okay', 'id' => null]);
    }

    $username = trim(strtolower($_GET['username']));
    $query = 'SELECT id FROM users WHERE username = :username';
    $stmt = $this -> pdo -> prepare($query);
    $stmt -> execute(['username' => $username]);

    $row = $stmt -> fetch();
    return json_encode(['status' => 'okay', 'id' => $row['id']]);
  }

  // Call getUser if username is specified, otherwise return all users.
  public function getUsers() {
    if (isset($_GET['username'])) {
      return $this -> getUser();
    }

    $stmt = $this -> pdo -> query('SELECT id, username FROM users');
    $data = $stmt -> fetchAll(PDO::FETCH_KEY_PAIR);

    return json_encode(['status' => 'okay', 'data' => $data]);
  }
}
?>
