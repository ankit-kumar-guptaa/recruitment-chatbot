let step = 0;
let userType = '';
let userData = { name: '', email: '', phone: '', position: '', hiring_count: '', requirements: '', location: '', experience: '', skills_certifications: '', location_preference: '' };
let sessionId = 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

function sendMessage() {
    let input = $('#userInput').val().trim();
    if (input === '') {
        showValidationError('Please enter a message or select an option.');
        return;
    }

    // Basic validation
    if (step > 0 && step < 7) { // Skip step 0 (initial option)
        if (step === 2 && !validateEmail(input)) {
            showValidationError('Please enter a valid email address (e.g., example@domain.com).');
            return;
        }
        if (step === 3 && !validatePhone(input)) {
            showValidationError('Please enter a valid phone number (e.g., +919876543210).');
            return;
        }
    }

    $('#messages').append(`<div class="message user-message">${input}</div>`);
    saveInteraction(input, '');
    $('#userInput').val('');
    $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
    hideValidationError();

    $.ajax({
        url: 'process_chat.php',
        type: 'POST',
        data: { message: input, step: step, userType: userType, userData: JSON.stringify(userData), sessionId: sessionId },
        success: function(response) {
            let data = JSON.parse(response);
            if (data.error) {
                console.error('Server Error:', data.error);
                typeMessage('Error: ' + data.error + ' Please try again or contact support.');
            } else {
                typeMessage(data.response);
                if (data.options) showOptions(data.options);
                saveInteraction(input, data.response);
                step++;
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error, 'Status:', status, 'Response:', xhr.responseText);
            typeMessage('Oops! There was a network issue. Please try again or contact support.');
        }
    });
}

// Enter key support
$('#userInput').on('keypress', function(e) {
    if (e.which === 13) { // Enter key
        sendMessage();
    }
});

function showOptions(options) {
    let $options = $('#messages').append('<div class="flex flex-wrap mt-6"></div>');
    options.forEach(option => {
        $options.append(`<button class="option-btn mr-3 mb-3" onclick="selectOption('${option}')">${option}</button>`);
    });
    $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
}

function selectOption(option) {
    $('#userInput').val(option);
    sendMessage();
}

function typeMessage(text) {
    let $message = $(`<div class="message bot-message" style="display: inline-block; overflow: hidden; white-space: nowrap;">${text}</div>`);
    $('#messages').append($message);
    $message.css('animation', 'typing 1.5s steps(40, end)');
    $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
}

function saveInteraction(userMsg, botResponse) {
    $.ajax({
        url: 'process_chat.php',
        type: 'POST',
        data: { saveInteraction: true, userType: userType, step: step, message: userMsg, response: botResponse, sessionId: sessionId, userData: JSON.stringify(userData) },
        async: true,
        success: function(response) {
            let data = JSON.parse(response);
            if (data.error) {
                console.error('Save Interaction Error:', data.error);
                typeMessage('Error saving your response: ' + data.error + ' Please try again.');
            } else {
                console.log('Interaction saved successfully:', data.success);
            }
        },
        error: function(xhr, status, error) {
            console.error('Save Interaction AJAX Error:', error, 'Status:', status, 'Response:', xhr.responseText);
            typeMessage('Error saving your response. Please try again or contact support.');
        }
    });
}

function showValidationError(message) {
    $('#validationError').text(message).removeClass('hidden').addClass('show');
    setTimeout(hideValidationError, 3000); // Hide after 3 seconds
}

function hideValidationError() {
    $('#validationError').addClass('hidden').removeClass('show');
}

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validatePhone(phone) {
    return /^\+?[1-9]\d{9,14}$/.test(phone);
}

$(document).ready(function() {
    typeMessage('Hello! Are you an employer looking to hire, or a job seeker looking for a job?');
    showOptions(['Employer', 'Job Seeker']);
});

// Dark/Light Mode Toggle
function toggleMode() {
    $('body').toggleClass('light-mode');
    localStorage.setItem('theme', $('body').hasClass('light-mode') ? 'light' : 'dark');
}

$(document).ready(function() {
    if (localStorage.getItem('theme') === 'light') {
        $('body').addClass('light-mode');
    }
});