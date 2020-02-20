<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Chat</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/png">
    <link rel="stylesheet" href="style/style.css">
</head>
<body>

    <form id="chat" class="chat-container">
        <div id="chat-result">
            <ol class="chat">
            </ol>
        </div>

        <div class="msg-send">
            <p class="msg-error">Сообщение не может быть пустым</p>
            <input name="chat-message" id="chat-message" class="textarea" type="text" placeholder="Сообщение">
            <button type="submit">
                <img src="img/mail.svg" alt="Send message" title="Send message">
            </button>
        </div>
    </form>

    <!-- TODO  <input type="text" name="chat-user" id="chat-user" placeholder="user"> -->
    <!-- TODO  минификация -->
    <!-- TODO  логгирование -->

    <script
            src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
            crossorigin="anonymous"></script>
    <script src="script/script.js"></script>
</body>
</html>