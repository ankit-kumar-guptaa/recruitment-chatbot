let userType = '';
let userData = { 
    user_type: '', 
    name: '', // Personal name
    organisation_name: '', // New field for organisation name
    city_state: '', // New field for city and state
    position: '', 
    hiring_count: '', 
    requirements: '', 
    email: '', 
    phone: '' 
};
let userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9); // Unique identifier for each user
let currentStep = 0; // Track the current step to ensure one question at a time
let isChatComplete = false; // Flag to track if the chat is complete

function sendMessage() {
    if (isChatComplete) {
        showValidationError('Chat is complete. Please start a new session to begin again.');
        return;
    }

    let input = $('#userInput').val().trim();
    if (input === '') {
        showValidationError('Please enter a message or select an option.');
        return;
    }

    if (userType === '') {
        if (input.toLowerCase() === 'employer' || input.toLowerCase() === 'job seeker' || input === 'Employer' || input === 'Job Seeker') {
            userType = input.toLowerCase();
            $('#messages').append(`<div class="message user-message">${input}</div>`);
            saveUserInput('user_type', userType, userId); // Save user_type and wait for server response
            currentStep = 1; // Set initial step after user type selection
        } else {
            showValidationError('Please select "Employer" or "Job Seeker".');
            return;
        }
    } else {
        if (!validateLocalInput(input, getInputTypeForStep())) {
            showValidationError('Invalid input. Please check the format and try again.');
            return;
        }
        $('#messages').append(`<div class="message user-message">${input}</div>`);
        saveUserInput(getColumnForStep(), input, userId);
    }

    $('#userInput').val('');
    $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
    hideValidationError();
    $('#loading').addClass('show');
}

function saveUserInput(column, value, userId) {
    // Ensure no duplicate or incorrect data is saved
    if (!userData[column] || userData[column] === '') {
        userData[column] = value;
    }
    $('#loading').addClass('show');
    $.ajax({
        url: 'process_chat.php',
        type: 'POST',
        data: { saveUserInput: true, userType: userType, column: column, value: value, userId: userId, currentStep: currentStep },
        dataType: 'json',
        async: true,
        success: function(data) {
            $('#loading').removeClass('show');
            if (data.error) {
                console.error('Save User Input Error:', data.error);
                typeMessage('Error saving your input: ' + (data.error.message || data.error) + ' Please try again or contact support at support@example.com.');
            } else if (data.success) {
                console.log('User input saved successfully:', data.success);
                currentStep = data.nextStep; // Update currentStep based on server response
                if (column === 'user_type') {
                    // Only show the next question after saving user_type
                    showNextQuestion();
                } else {
                    // For other inputs, show the next question immediately
                    showNextQuestion();
                }
            }
        },
        error: function(xhr, status, error) {
            $('#loading').removeClass('show');
            console.error('Save User Input AJAX Error:', error, 'Status:', status, 'Response:', xhr.responseText);
            let responseText = xhr.responseText;
            try {
                let parsedError = JSON.parse(responseText);
                typeMessage('Error saving your input: ' + (parsedError.error || 'Unexpected server response') + ' Please try again or contact support at support@example.com.');
            } catch (e) {
                typeMessage('Error saving your input. Unexpected server response. Please try again or contact support at support@example.com. Error details: ' + error + '. Status: ' + status);
            }
        }
    });
}

function showNextQuestion() {
    if (userType === 'employer') {
        const filledFields = Object.keys(userData).filter(key => userData[key] !== '' && userData[key] !== null && userData[key] !== undefined).length;
        if (filledFields === 1) { // After user_type is saved
            typeMessage("What’s your name?");
        } else if (filledFields === 2) { // After name is saved
            typeMessage("Your organisation name?");
        } else if (filledFields === 3) { // After organisation_name is saved
            typeMessage("You are from which City & State?");
        } else if (filledFields === 4) { // After city_state is saved
            typeMessage("Great! What position are you looking to hire for?");
        } else if (filledFields === 5) { // After position is saved
            typeMessage("Nice! How many people do you want to hire?");
        } else if (filledFields === 6) { // After hiring_count is saved
            typeMessage("Got it! Any specific skills, qualifications, or experience you require for this role?");
        } else if (filledFields === 7) { // After requirements is saved
            typeMessage("Please provide your email address (e.g., ankit2@email.com).");
        } else if (filledFields === 8) { // After email is saved
            typeMessage("Please provide your phone number.");
        } else if (filledFields === 9) { // After phone is saved
            typeMessage("Thanks for the details! Our Sales Team will connect with you soon. Please call us at 9871916980 for urgent discussion.");
            // Add confirmation message that the enquiry is saved (not saved to database, just UI confirmation)
            setTimeout(() => {
                typeMessage("Your enquiry has been saved successfully!");
                disableChatInput(); // Disable input and Send button after final message
            }, 1000); // Delay to simulate processing
        }
    } else if (userType === 'job seeker') {
        const filledFields = Object.keys(userData).filter(key => userData[key] !== '' && userData[key] !== null && userData[key] !== undefined).length;
        if (filledFields === 1) { // After user_type is saved
            typeMessage("What’s your name?");
        } else if (filledFields === 2) { // After name is saved
            showOptionsForFresherExperienced();
        } else if (filledFields === 3) { // After fresher_experienced is saved
            showOptionsForJobApplication();
        } else if (filledFields === 4) { // After applying_for_job is saved
            typeMessage("Awesome! What type of job are you looking for? E.g., Software Developer, Marketing Specialist, etc.");
        } else if (filledFields === 5) { // After position is saved
            if (userData.fresher_experienced.toLowerCase() === 'experienced') {
                typeMessage("Great! How many years of experience do you have in this field?");
            } else {
                // Skip experience_years for Fresher and move to next question
                userData.experience_years = '0'; // Default for Fresher
                saveUserInput('experience_years', '0', userId);
            }
        } else if (filledFields === 6 || (filledFields === 5 && userData.fresher_experienced.toLowerCase() === 'fresher')) { // After experience_years (or skipped for Fresher)
            typeMessage("Thanks! What specific skills or Degree do you have that make you stand out for this role?");
        } else if (filledFields === 7) { // After skills_degree is saved
            typeMessage("Perfect! Are you open to relocating, or do you prefer a specific location like a city or region?");
        } else if (filledFields === 8) { // After location_preference is saved
            typeMessage("Please provide your email address (e.g., ankit2@email.com).");
        } else if (filledFields === 9) { // After email is saved
            typeMessage("Please provide your phone number.");
        } else if (filledFields === 10) { // After phone is saved
            typeMessage("Any other comments?");
        } else if (filledFields === 11) { // After comments is saved
            typeMessage("Thank you for sharing your details! We have saved your information and will connect with you soon. Please note that we place candidates based on company requirements—we do not create job openings.");
            // Add confirmation message that the enquiry is saved (not saved to database, just UI confirmation)
            setTimeout(() => {
                typeMessage("Your enquiry has been saved successfully!");
                disableChatInput(); // Disable input and Send button after final message
            }, 1000); // Delay to simulate processing
        }
    }
}

function showOptionsForFresherExperienced() {
    typeMessage("Are you a Fresher or Experienced?");
    showOptions(['Fresher', 'Experienced']);
}

function showOptionsForJobApplication() {
    typeMessage("Are you applying for any job posted by us on our job portal or LinkedIn?");
    showOptions(['Yes', 'No']);
}

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
    let $message = $(`<div class="message bot-message"></div>`);
    $('#messages').append($message);
    let index = 0;
    let typingInterval = setInterval(() => {
        if (index < text.length) {
            $message.append(text.charAt(index));
            index++;
        } else {
            clearInterval(typingInterval);
        }
        $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
    }, 50); // Typing effect with 50ms delay between characters
}

function getColumnForStep() {
    const filledFields = Object.keys(userData).filter(key => userData[key] !== '' && userData[key] !== null && userData[key] !== undefined).length;
    if (userType === 'employer') {
        switch (filledFields) {
            case 0: return 'user_type'; // Already handled
            case 1: return 'name';
            case 2: return 'organisation_name';
            case 3: return 'city_state';
            case 4: return 'position';
            case 5: return 'hiring_count';
            case 6: return 'requirements';
            case 7: return 'email';
            case 8: return 'phone';
        }
    } else if (userType === 'job seeker') {
        switch (filledFields) {
            case 0: return 'user_type'; // Already handled
            case 1: return 'name';
            case 2: return 'fresher_experienced';
            case 3: return 'applying_for_job';
            case 4: return 'position';
            case 5: return userData.fresher_experienced.toLowerCase() === 'experienced' ? 'experience_years' : 'skills_degree';
            case 6: return 'skills_degree';
            case 7: return 'location_preference';
            case 8: return 'email';
            case 9: return 'phone';
            case 10: return 'comments';
        }
    }
    return '';
}

function getInputTypeForStep() {
    const filledFields = Object.keys(userData).filter(key => userData[key] !== '' && userData[key] !== null && userData[key] !== undefined).length;
    if (userType === 'employer') {
        switch (filledFields) {
            case 1: return 'text'; // Name
            case 2: return 'text'; // Organisation name
            case 3: return 'text'; // City & State (e.g., "New York, NY")
            case 4: return 'text'; // Position
            case 5: return 'number'; // Hiring count
            case 6: return 'text'; // Requirements
            case 7: return 'email'; // Email
            case 8: return 'phone'; // Phone
        }
    } else if (userType === 'job seeker') {
        switch (filledFields) {
            case 1: return 'text'; // Name
            case 4: return 'text'; // Position
            case 5: return 'number'; // Experience years (for Experienced only)
            case 6: return 'text'; // Skills/Degree
            case 7: return 'text'; // Location preference
            case 8: return 'email'; // Email
            case 9: return 'phone'; // Phone
            case 10: return 'text'; // Comments
        }
    }
    return 'text';
}

function validateLocalInput(input, type) {
    switch (type) {
        case 'email':
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input); // Basic email validation
        case 'phone':
            return /^[0-9]{10,15}$/.test(input); // 10-15 digit phone number
        case 'number':
            return /^[0-9]+$/.test(input) && parseInt(input) > 0; // Positive numbers only
        case 'text':
            return input.trim().length > 0; // Non-empty text
        default:
            return true;
    }
}

function validateInputByStep(input) {
    // This is now handled by validateLocalInput in sendMessage
    return validateLocalInput(input, getInputTypeForStep());
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

function disableChatInput() {
    $('#userInput').prop('disabled', true).attr('placeholder', 'Chat completed. Clear to start anew.');
    $('button[onclick="sendMessage()"]').prop('disabled', true).addClass('opacity-50 cursor-not-allowed').removeClass('hover:scale-105');
    isChatComplete = true; // Set flag to prevent further input
}

function clearChat() {
    $('#messages').empty();
    $('#loading').removeClass('show');
    userType = '';
    userData = { 
        user_type: '', 
        name: '', 
        organisation_name: '', 
        city_state: '', 
        position: '', 
        hiring_count: '', 
        requirements: '', 
        email: '', 
        phone: '' 
    };
    userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9); // Reset user ID
    currentStep = 0; // Reset step
    isChatComplete = false; // Reset chat completion flag
    $('#userInput').prop('disabled', false).attr('placeholder', 'Type your message or select an option...');
    $('button[onclick="sendMessage()"]').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed').addClass('hover:scale-105');
    typeMessage('Hello! Are you an employer looking to hire, or a job seeker looking for a job?');
    showOptions(['Employer', 'Job Seeker']);
}

// Enter key support
$('#userInput').on('keypress', function(e) {
    if (e.which === 13 && !isChatComplete) { // Enter key, only if chat isn’t complete
        sendMessage();
    }
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
    typeMessage('Hello! Are you an employer looking to hire, or a job seeker looking for a job?');
    showOptions(['Employer', 'Job Seeker']);
});