let userType = '';
let userData = { 
    user_type: '', 
    name: '', 
    fresher_experienced: '', // New field for Fresher/Experienced
    applying_for_job: '', // New field for Yes/No on job application
    position: '', 
    experience_years: '', // Only for Experienced (not shown for Fresher)
    skills_degree: '', // Updated from skills_certifications to skills_degree
    location_preference: '', 
    email: '', 
    phone: '', 
    comments: '' // New field for additional comments
};
let userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9); // Unique identifier for each user
let currentStep = 0; // Track the current step to ensure one question at a time

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
            saveUserInput('user_type', userType, userId); // Save user_type and wait for server response
            currentStep = 1; // Set initial step after user type selection
        } else {
            showValidationError('Please select "Employer" or "Job Seeker".');
            return;
        }
    } else {
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

function validateInputByStep(input) {
    // Removed all validations, always return true
    return true;
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
    userData = { 
        user_type: '', 
        name: '', 
        fresher_experienced: '', 
        applying_for_job: '', 
        position: '', 
        experience_years: '', 
        skills_degree: '', 
        location_preference: '', 
        email: '', 
        phone: '', 
        comments: '' 
    };
    userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9); // Reset user ID
    currentStep = 0; // Reset step
    typeMessage('Hello! Are you an employer looking to hire, or a job seeker looking for a job?');
    showOptions(['Employer', 'Job Seeker']);
}

// Enter key support
$('#userInput').on('keypress', function(e) {
    if (e.which === 13) { // Enter key
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