// Get the 1st DOM element matching the given query.
// If the second parameter is defined return a list.
const q = (query, all) => (
  all ? document.querySelectorAll(query) : document.querySelector(query)
);

// Show a message using a snackbar.
const snack = (str) => {
  const s = q('#snackbar');
  s.innerText = str;
  s.style.top = 'calc(100vh - 2.5rem)';

  setTimeout(() => {
    s.style.top = '100vh';
  }, 3500);
};

// Convert object to URL notation.
const urlify = obj =>
  Object.keys(obj).map(x => `${x}=${encodeURI(obj[x])}`).join('&');

const get = (url, params, callback) => {
  const xhr = new XMLHttpRequest();
  xhr.onreadystatechange = () => {
    if (callback && xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status !== 200) {
        snack(xhr.statusText);
        return;
      }

      callback(JSON.parse(xhr.responseText));
    }
  };

  const urlifiedParams = params ? `?${urlify(params)}` : '';
  xhr.open('GET', url + urlifiedParams, true);
  xhr.send();
};

const post = (url, params, callback) => {
  const xhr = new XMLHttpRequest();
  xhr.onreadystatechange = () => {
    if (callback && xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status !== 200) {
        snack(xhr.statusText);
        return;
      }

      callback(JSON.parse(xhr.responseText));
    }
  };

  xhr.open('POST', url, true);
  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhr.send(urlify(params));
};


let username;
let userID;
let userID2;
let interval;

// Load current conversations and display them
const loadConversations = () => {
  get('/conversations', { userID }, (response) => {
    if (response.status === 'error') {
      snack(response.error);
      return;
    }

    // response.data is an object with username as keys
    // and userIDs as values
    const conversations = Object.keys(response.data).map(x => (`
      <article
        onclick="loadConversation('${response.data[x]}')"
        id="conv-${response.data[x]}"
      >
        <h1>${x}</h1>
      </article>
    `));

    q('#conversation-list').innerHTML = conversations.join('\n');

    if (userID2) {
      q(`#conv-${userID2}`).className = 'selected';
    }
  });
};

// Dummy login. Here I'm just retrieving the ID of the user if it exists
// and creating it otherwise. You have to "login" each time you reload the page.
const login = (event) => {
  event.preventDefault();
  username = q('#username').value;

  if (username.length < 4 || username.length > 30) {
    snack('Username must be between 4 and 30 characters');
    return;
  }

  get('/users', { username }, (response) => {
    if (response.status === 'error') {
      snack(response.message);
      return;
    }

    // if user is logged, hide login section and load conversations
    if (response.id) {
      userID = response.id;
      q('#login').className = 'hidden';
      loadConversations();
    } else {
      post('/users', { username }, (res) => {
        if (res.status === 'error') {
          snack(res.message);
          return;
        }
        userID = res.id;
        q('#login').className = 'hidden';
        loadConversations();
      });
    }
  });
};

// Load a single conversation and display it.
const loadConversation = (otherID) => {
  if (otherID) {
    // Remove selected class from previous selected conversation.
    if (userID2) {
      q(`#conv-${userID2}`).className = '';
    }

    // Add selected class to new selected conversation.
    q(`#conv-${otherID}`).className = 'selected';
    userID2 = otherID;
  }

  get('/messages', { userID, userID2 }, (response) => {
    if (response.status === 'error') {
      snack(response.message);
      return;
    }

    // response.data is an array of objects representing each message
    const messages = response.data.map(x => (
      `<article class="${x.from === username ? 'from-me' : 'from-other'}">
        <div class="message">
          <span class="body">${x.body}</span>
          <small class="time">${x.t.substring(11, 16)}</small>
        </div>
       </article>`
    ));

    const conversation = q('#conversation');
    conversation.innerHTML = messages.join('\n');
    conversation.scrollTop = conversation.scrollHeight;

    if (q('#message').attributes.disabled) {
      q('#message').attributes.removeNamedItem('disabled');
    }
  });

  // Refresh chat every 5 seconds.
  if (!interval) {
    interval = setInterval(loadConversation, 5000);
  }
};

// Send message to user on open conversation.
const sendMessage = (event) => {
  event.preventDefault();

  post('/messages', {
    from: userID,
    to: userID2,
    body: q('#message').value,
  }, (response) => {
    if (response.status === 'error') {
      snack(response.message);
      return;
    }

    q('#message').value = '';
    loadConversations();
    loadConversation(userID2);
  });
};

// Checks if user exists and if it does create a new conversation.
const newConversation = (event) => {
  event.preventDefault();

  const username2 = q('#username2').value;

  if (username2.length < 4 || username2.length > 30) {
    snack('The selected user does not exist');
    return;
  }

  get('/users', { username: username2 }, (response) => {
    if (response.status === 'error') {
      snack(response.message);
      return;
    }

    if (!response.id) {
      snack('The selected user does not exist');
      return;
    }

    const conversations = q('#conversation-list');
    conversations.innerHTML = `
      <article
        onclick="loadConversation('${response.id}')"
        id="conv-${response.id}"
      >
        <h1>${username2}</h1>
      </article>
      ${conversations.innerHTML}
    `;

    q('#username2').value = '';
    q('#conversation-list > article').click();

    if (q('#message').attributes.disabled) {
      q('#message').attributes.removeNamedItem('disabled');
    }
  });
};
