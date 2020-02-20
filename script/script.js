/**
 * Вывод сообщения пользователя
 * @param text
 * @param owner
 */
function userMessage(text, owner = false) {
    var date       = new Date();
    var ownerClass = owner ? 'self' : 'other';

    $('#chat-result .chat').append(
        "<li class=" +ownerClass+">" +
            "<div class='msg'>" +
                // "<p class='msg-author'>Kate</p>" +
                "<p>"+ text +"</p>" +
                "<time>"+ date.getHours() + ":" + date.getMinutes() +"</time>" +
            "</div>" +
        "</li>"
    );
    messageSound();
    scrollToMessage();
}

/**
 * Вывод сообщения пользователя анонсера
 * @param text
 * @constructor
 */
function chatMessage(text) {
    $('#chat-result .chat').append(
        "<p class='chat-announcer'>" +
        "<b>Чат-анонсер: <span class='chat-status'>"+ text +"</span></b>" +
        "</p>"
    );
}

/**
 * Перемещает экран к последнему сообщению
 * @returns {boolean}
 */
function scrollToMessage() {
    var destination = $('#chat-message').offset().top;
    $('html').animate({ scrollTop: destination }, 1100);
    return false;
}

function messageSound() {
    var audio = new Audio('sound.mp3');
    audio.play();
}

/**
 * Генерит уникальное значение для идентификации пользователя
 * @param length
 * @returns {string}
 */
function generateId(length) {
    var result           = '';
    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for ( var i = 0; i < length; i++ ) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}

$(document).ready(function () {
    const url = "server ip:port/chat/server.php";

    var socket = new WebSocket("ws://" + url);
    var sender = generateId(32);

    socket.onopen = function () {
        chatMessage("Соединение установлено");
    };
    socket.onerror = function (error) {
        chatMessage("Ошибка соединения " + (error.message ? error.message : ''));
    };
    socket.onclose = function () {
        chatMessage("Сервер недоступен")
    };
    socket.onmessage = function (e) {
        var data = JSON.parse(e.data);
        
        if (data.type === 'chat_message') {
            if (data.sender === sender) {
                userMessage(data.message, true);
            } else {
                userMessage(data.message);
            }
            return false;
        }

        chatMessage(data.message);
    };

    $('#chat').on('submit', function () {
        $('.msg-error').hide();

        var msgLength = $('#chat-message').val().length;
        if (msgLength < 1) {
            $('.msg-error').show();
            return false;
        }

        var message = {
            chat_message : $('#chat-message').val(),
            chat_user    : $('#chat-user').val(),
            sender       : sender,
        };

        $('#chat-user').attr('type', 'hidden');
        $('#chat-message').val('');

        socket.send(JSON.stringify(message));
        return false;
    });
});