let step = 0;
let userType = '';
let userData = { name: '', email: '', phone: '', message: '', experience: '', specialization: '' };

function sendMessage() {
    let input = $('#userInput').val().trim();
    if (input === '') return;

    $('#messages').append(`<div class="message user-message">${input}</div>`);
    $('#userInput').val('');
    $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);

    $.ajax({
        url: 'process_chat.php',
        type: 'POST',
        data: { message: input, step: step, userType: userType, userData: JSON.stringify(userData) },
        success: function(response) {
            let botResponse = response;
            typeMessage(botResponse);
            step++;
        }
    });
}

function typeMessage(text) {
    let $message = $(`<div class="message bot-message" style="display: inline-block; overflow: hidden; white-space: nowrap;">${text}</div>`);
    $('#messages').append($message);
    $message.css('animation', 'typing 1s steps(40, end)');
    $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
}

$(document).ready(function() {
    typeMessage('Hey! Are you an employer looking for staff, or a job seeker looking for a healthcare job?');
});

// Dark/Light Mode Toggle
function toggleMode() {
    $('body').toggleClass('light-mode dark-mode');
    localStorage.setItem('theme', $('body').hasClass('dark-mode') ? 'dark' : 'light');
}

$(document).ready(function() {
    // Check saved theme
    if (localStorage.getItem('theme') === 'dark') {
        $('body').removeClass('light-mode').addClass('dark-mode');
    }
});