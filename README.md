# Chat App (Coding Assignment)
This is a basic chat application with a PHP backend. Users can send messages to
each other, and they can retrieve the received messages together with author
name and timestamp.

## Getting Started
The dependencies of this project are PHP, SQLite and a SQLite driver for PHP.
These can be installed with the following command on a Debian based distro.

    sudo apt-get install php5 sqlite3 php5-sqlite

Once you have these programs, you can just run the `run.sh` script to start the
server. The webpage will show at [http://localhost:3000](http://localhost:3000)

## Usage
Press enter to submit on any input field.

The chat supports some simple markdown, the possible stylings are:

- \*bold\* → **bold**
- \_italic\_ → _italic_
- ~strikethrough~ → ~~strikethrough~~
- !\[img](https://imgs.xkcd.com/comics/team_chat.png) →
![img](https://imgs.xkcd.com/comics/team_chat.png)

## API Usage
All API routes return a JSON object. This object always contains the `status`
attribute, which can be either `okay` or `error`. If the status is error, then
there will be a `message` attribute with the error message. If the status is
okay, depending on the route there may be one other attribute or none. Below a
table containing all API routes, their parameters and what they return.

|route          |method|params          |response |response example  |
|---------------|------|----------------|---------|------------------|
|/users         |POST  |username        |id       |"1"               |
|/users         |GET   |username        |id       |"1"               |
|/users         |GET   |-               |data     |{"1": "username"} |
|/messages      |POST  |from, to, body  |-        |-                 |
|/messages      |GET   |userID          |data     |[message*]        |
|/messages      |GET   |userID, userID2 |data     |[message*]        |
|/conversations |GET   |userID          |data     |{"username": "1"} |

*the message representation is the following:

    {
      "body": "Hello World!",
      "from": "foo",
      "to": "bar",
      "t": "2017-03-24 18:55:51"
    }

## Folder Structure
The folder structure should be pretty intuitive. On the `client` folder is
sitting all the client side code, `router.php` is the server entry point, which
based on the requested URL may call some of the functions on the `controllers`
folder, and `connection.php` is the file that takes care of the DB connection
(if you rename your DB, you should edit that file).

## Notes
The JavaScript code is written using ES6 syntax, and may not run on all
browsers. To fix this, the code should be transpiled (eg. using Babel),
I didn't do it to keep things as simple as possible, and to not add external
code.
