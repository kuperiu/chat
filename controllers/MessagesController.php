<?php
class MessagesController extends Controller {
  // Create  new message.
  public function createMessage() {
    if (!isset($_POST['body'], $_POST['to'], $_POST['from'])) {
      return http_response_code(400);
    }

    if ($_POST['body'] === '') {
      return json_encode([
        'status' => 'error',
        'message' => 'Message cannot be empty'
      ]);
    }

    $body = trim($_POST['body']);
    $body = htmlspecialchars($body);

    // Yay, we got markdown! [Relevant XKCD](https://xkcd.com/208/).
    $body = preg_replace('/_(.*)_/', '<i>${1}</i>', $body);
    $body = preg_replace('/\*(.*)\*/', '<b>${1}</b>', $body);
    $body = preg_replace('/~(.*)~/', '<s>${1}</s>', $body);
    $body = preg_replace('/!\[img\]\((.*)\)/', '<img src="${1}"></img>', $body);

    $query = "
      INSERT INTO messages ('from', 'to', body)
      VALUES (:from, :to, :body)
    ";

    try {
      $stmt = $this -> pdo -> prepare($query);
      $stmt -> execute([
        'from' => $_POST['from'],
        'to' => $_POST['to'],
        'body' => $body
      ]);

      return json_encode(['status' => 'okay']);
    } catch (PDOException $e){
      // FOREIGN KEY constraint failed
      if ($e -> getCode() === '23000') {
        return json_encode([
          'status' => 'error',
          'message' => 'User does not exist'
        ]);
      } else {
        throw $e;
      }
    }
  }

  // Get all messages related to a specific userID or all messages
  // between userID and userID2 if userID2 is specified.
  public function getMessages() {
    if (!isset($_GET['userID'])) {
      return http_response_code(400);
    }

    if (isset($_GET['userID2'])) {
      return $this -> getConversationMessages();
    }

    $query = "
      SELECT m.t, uf.username as 'from', ut.username as 'to', m.body
      FROM messages m
      JOIN users uf ON m.'from' = uf.id
      JOIN users ut ON m.'to' = ut.id
      WHERE m.'from' = :userID OR m.'to' = :userID
      ORDER BY m.t ASC
    ";

    $stmt = $this -> pdo -> prepare($query);
    $stmt -> execute(['userID' => $_GET['userID']]);
    $data = $stmt -> fetchAll();

    return json_encode(['status' => 'okay', 'data' => $data]);
  }

  // Get all messages in the conversation between userID and userID2.
  public function getConversationMessages() {
    $query = "
      SELECT m.t, uf.username as 'from', ut.username as 'to', m.body
      FROM messages m
      JOIN users uf ON m.'from' = uf.id
      JOIN users ut ON m.'to' = ut.id
      WHERE (m.'from' = :userID AND m.'to' = :userID2)
      OR (m.'from' = :userID2 AND m.'to' = :userID)
      ORDER BY m.t ASC
    ";

    $stmt = $this -> pdo -> prepare($query);
    $stmt -> execute([
      'userID' => $_GET['userID'],
      'userID2' => $_GET['userID2']
    ]);
    $data = $stmt -> fetchAll();

    return json_encode(['status' => 'okay', 'data' => $data]);
  }

  // Get all existing conversations that involve userID.
  // Returns the other participant for each conversation, not the messages.
  public function getConversations() {
    if (!isset($_GET['userID'])) {
      return http_response_code(400);
    }

    $query = "
      SELECT users.username, m.'from' FROM messages m
      JOIN users ON m.'from' = users.id
      WHERE m.'to' = :userID

      UNION

      SELECT users.username, m.'to' FROM messages m
      JOIN users ON m.'to' = users.id
      WHERE m.'from' = :userID
    ";

    $stmt = $this -> pdo -> prepare($query);
    $stmt -> execute(['userID' => $_GET['userID']]);
    $data = $stmt -> fetchAll(PDO::FETCH_KEY_PAIR);

    return json_encode(['status' => 'okay', 'data' => $data]);
  }
}
?>
