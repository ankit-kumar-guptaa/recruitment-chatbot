let userType = '';
let userData = { user_type: '', name: '', email: '', phone: '', position: '', hiring_count: '', requirements: '', location: '', experience: '', skills_certifications: '', location_preference: '' };
let userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9); // Unique identifier for each user

function sendMessage() {
    let input = $('#userInput').val().trim();
    if (input === '') {
        showValidationError('Please enter a message or select an option.');
        return;
    }

    if (userType === '') {
        if (input.toLowerCase() === 'employer' || input.toLowerCase() === 'job seeker' || input === 'Employer' || input === 'Job Seeker') {
            userType = input.toLowerCase();
            $('#messages').append(`<div class="message user-message">${input}</div>`);
            typeMessage("What’s your name?");
            saveUserInput('user_type', userType, userId); // Save user_type only
        } else {
            showValidationError('Please select "Employer" or "Job Seeker".');
            return;
        }
    } else {
        $('#messages').append(`<div class="message user-message">${input}</div>`);
        if (!input) {
            showValidationError('Please enter a valid response.');
            return;
        }
        saveUserInput(getColumnForStep(), input, userId);
        showNextQuestion();
    }

    $('#userInput').val('');
    $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
    hideValidationError();
    $('#loading').addClass('show');

    $.ajax({
        url: 'process_chat.php',
        type: 'POST',
        data: { message: input, userType: userType, userData: JSON.stringify(userData), userId: userId },
        dataType: 'json',
        success: function(data) {
            $('#loading').removeClass('show');
            if (data.error) {
                console.error('Server Error:', data.error);
                typeMessage('Error: ' + data.error + ' Please try again or contact support at support@example.com.');
            } else {
                typeMessage(data.response);
            }
        },
        error: function(xhr, status, error) {
            $('#loading').removeClass('show');
            console.error('AJAX Error:', error, 'Status:', status, 'Response:', xhr.responseText);
            let responseText = xhr.responseText;
            if (status === 404) {
                typeMessage('Error: Server file not found. Please check the setup or contact support at support@example.com.');
            } else {
                try {
                    let parsedError = JSON.parse(responseText);
                    typeMessage('Error: ' + (parsedError.error || 'Unexpected server response. Please try again or contact support at support@example.com.'));
                } catch (e) {
                    typeMessage('Error: Unexpected server response. Please try again or contact support at support@example.com. Error details: ' + error + '. Status: ' + status);
                }
            }
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
    let $options = $('#messages').append('<div class="flex flex-wrap mt-4"></div>');
    options.forEach(option => {
        $options.append(`<button class="option-btn mr-2 mb-2" onclick="selectOption('${option}')">${option}</button>`);
    });
    $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
}

function selectOption(option) {
    $('#userInput').val(option);
    sendMessage();
}

function typeMessage(text) {
    let $message = $(`<div class="message bot-message typing" style="display: block; overflow: hidden; white-space: pre-wrap;">${text}</div>`);
    $('#messages').append($message);
    $message.css('animation', 'typing 1.5s steps(40, end) forwards, blink 0.5s step-end infinite');
    setTimeout(() => {
        $message.removeClass('typing').css('animation', 'none').css('border-right', 'none');
    }, 1500); // Match the typing animation duration
    $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
}

function saveUserInput(column, value, userId) {
    userData[column] = value;
    $('#loading').addClass('show');
    $.ajax({
        url: 'process_chat.php',
        type: 'POST',
        data: { saveUserInput: true, userType: userType, column: column, value: value, userId: userId },
        dataType: 'json',
        async: true,
        success: function(data) {
            $('#loading').removeClass('show');
            if (data.error) {
                console.error('Save User Input Error:', data.error);
                typeMessage('Error saving your input: ' + data.error + ' Please try again or contact support at support@example.com.');
            } else {
                console.log('User input saved successfully:', data.success);
            }
        },
        error: function(xhr, status, error) {
            $('#loading').removeClass('show');
            console.error('Save User Input AJAX Error:', error, 'Status:', status, 'Response:', xhr.responseText);
            let responseText = xhr.responseText;
            if (status === 404) {
                typeMessage('Error: Server file not found. Please check the setup or contact support at support@example.com.');
            } else {
                try {
                    let parsedError = JSON.parse(responseText);
                    typeMessage('Error saving your input: ' + (parsedError.error || 'Unexpected server response. Please try again or contact support at support@example.com.'));
                } catch (e) {
                    typeMessage('Error saving your input. Unexpected server response. Please try again or contact support at support@example.com. Error details: ' + error + '. Status: ' + status);
                }
            }
        }
    });
}

function showNextQuestion() {
    if (userType === 'employer') {
        const filledFields = Object.keys(userData).filter(key => userData[key] !== '').length;
        if (filledFields === 1) { // After user_type is saved
            typeMessage("What’s your name?");
        } else if (filledFields === 2) { // After name is saved
            typeMessage("Please provide your email address (e.g., ankit2@email.com).");
        } else if (filledFields === 3) { // After email is saved
            typeMessage("Please provide your phone number.");
        } else if (filledFields === 4) { // After phone is saved
            typeMessage("Great! What position are you looking to hire for? E.g., Software Engineer, Sales Manager, etc.");
        } else if (filledFields === 5) { // After position is saved
            typeMessage("Nice! How many people do you want to hire for this role?");
        } else if (filledFields === 6) { // After hiring_count is saved
            typeMessage("Got it! Any specific skills, qualifications, or experience you require for this role?");
        } else if (filledFields === 7) { // After requirements is saved
            typeMessage("Perfect! Any preferred location for this role, like a city or region?");
        } else if (filledFields === 8) { // After location is saved
            typeMessage("Thanks for the details! We’ve saved your enquiry. We’ll connect with you soon. Please don’t call us—we’ll reach out to you at: +91 98703 64340");
        }
    } else if (userType === 'job seeker') {
        const filledFields = Object.keys(userData).filter(key => userData[key] !== '').length;
        if (filledFields === 1) { // After user_type is saved
            typeMessage("What’s your name?");
        } else if (filledFields === 2) { // After name is saved
            typeMessage("Please provide your email address (e.g., ankit2@email.com).");
        } else if (filledFields === 3) { // After email is saved
            typeMessage("Please provide your phone number.");
        } else if (filledFields === 4) { // After phone is saved
            typeMessage("Awesome! What type of job are you looking for? E.g., Software Developer, Marketing Specialist, etc.");
        } else if (filledFields === 5) { // After position is saved
            typeMessage("Great! How many years of experience do you have in this field?");
        } else if (filledFields === 6) { // After experience is saved
            typeMessage("Thanks! What specific skills or certifications do you have that make you stand out for this role?");
        } else if (filledFields === 7) { // After skills_certifications is saved
            typeMessage("Perfect! Are you open to relocating, or do you prefer a specific location like a city or region?");
        } else if (filledFields === 8) { // After location_preference is saved
            typeMessage("Thanks for sharing! We’ve saved your enquiry. We’ll connect with you soon. Please don’t call us—we’ll reach out to you at: +91 98703 64340");
        }
    }
}

function validateInputByStep(input) {
    // Removed all validations, always return true
    return true;
}

function getColumnForStep() {
    const filledFields = Object.keys(userData).filter(key => userData[key] !== '').length;
    if (userType === 'employer') {
        switch (filledFields) {
            case 0: return 'user_type'; // Already handled
            case 1: return 'name';
            case 2: return 'email';
            case 3: return 'phone';
            case 4: return 'position';
            case 5: return 'hiring_count';
            case 6: return 'requirements';
            case 7: return 'location';
        }
    } else if (userType === 'job seeker') {
        switch (filledFields) {
            case 0: return 'user_type'; // Already handled
            case 1: return 'name';
            case 2: return 'email';
            case 3: return 'phone';
            case 4: return 'position';
            case 5: return 'experience';
            case 6: return 'skills_certifications';
            case 7: return 'location_preference';
        }
    }
    return '';
}

function validateLocalInput(input, type) {
    // Removed all validations, always return true
    return true;
}

function empty(str) {
    return str === '' || str === null || str === undefined;
}

function showValidationError(message) {
    $('#validationError').text(message).removeClass('hidden').addClass('show');
    setTimeout(hideValidationError, 3000); // Hide after 3 seconds
}

function hideValidationError() {
    $('#validationError').addClass('hidden').removeClass('show');
}

function clearChat() {
    $('#messages').empty();
    $('#loading').removeClass('show');
    userType = '';
    userData = { user_type: '', name: '', email: '', phone: '', position: '', hiring_count: '', requirements: '', location: '', experience: '', skills_certifications: '', location_preference: '' };
    userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9); // Reset user ID
    typeMessage('Hello! Are you an employer looking to hire, or a job seeker looking for a job?');
    showOptions(['Employer', 'Job Seeker']);
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