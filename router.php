<?php
require(__DIR__ . '/controllers/Controller.php');
require(__DIR__ . '/controllers/UsersController.php');
require(__DIR__ . '/controllers/MessagesController.php');

/*
 * Execute the callback (or call a controller function) if
 * the requested method and URL match the given route and method
 */
function respond($method, $route, $callback) {
  if (gettype($callback) !== 'string' && !is_callable($callback)) {
    throw new Exception('Parameter $callback must be string or function');
  }

  if ($_SERVER['REQUEST_METHOD'] !== $method) {
    return;
  }

  $matches = null;
  if (!preg_match('/^\/' . $route . '/', $_SERVER['REQUEST_URI'], $matches)) {
    return;
  }

  // if callback is a string, call the specified controller function
  // the string must be formatted as ControllerClass@desiredMethod
  if (gettype($callback) === 'string') {
    list($controller, $action) = explode('@', $callback);

    if (class_exists($controller)) {
      $controller = new $controller();
      header('Content-type: application/json');
      echo $controller -> $action();
      exit;
    }
  } else {
    // in this case $callback is a function
    echo $callback($matches);
    exit;
  }
}

respond('POST', 'users', 'UsersController@createUser');
respond('GET', 'users', 'UsersController@getUsers');

respond('POST', 'messages', 'MessagesController@createMessage');
respond('GET', 'messages', 'MessagesController@getMessages');
respond('GET', 'conversations', 'MessagesController@getConversations');

// requests for js|css files
respond('GET', '[a-z_]*\.(css|js)$', function ($matches) {
  // $matches = array(0 => '/filename.extension', 1 => 'extension')
  if (file_exists(__DIR__ . '/client' . $matches[0])) {
    // set header based on file extension
    $type = $matches[1] === 'css' ? 'css' : 'javascript';
    header('Content-type: text/' . $type);

    readfile(__DIR__ . '/client' . $matches[0]);
  }
});

// return index.html to all requests not matched by the routes above
respond('GET', '', function () {
  readfile(__DIR__ . '/client/index.html');
});
?>
