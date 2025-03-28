// Load jQuery if not already loaded
if (typeof jQuery === 'undefined') {
    var script = document.createElement('script');
    script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
    script.onload = initChatbot;
    document.head.appendChild(script);
} else {
    initChatbot();
}

function initChatbot() {
    // Add Chatbot HTML to the page
    const chatbotHtml = `
        <div id="chatbot-container" class="chatbot-footer">
            <div id="chatbot-toggle" class="chatbot-toggle">How may I help you?</div>
            <div id="chatbot-box" class="chatbot-box hidden">
                <div id="chatbox-header">
                    <span>Chat with Us</span>
                    <button onclick="toggleChatbot()">✖</button>
                </div>
                <div id="chatbox">
                    <div id="messages"></div>
                </div>
                <div id="chatbox-footer">
                    <input type="text" id="userInput" placeholder="Type your message or select an option..." />
                    <button onclick="sendMessage()">Send</button>
                    
                </div>
                <div id="validationError" class="hidden"></div>
                <div id="loading" class="hidden">Loading...</div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', chatbotHtml);

    // Add CSS dynamically
    const cssLink = document.createElement('link');
    cssLink.rel = 'stylesheet';
    cssLink.href = 'https://recruitment-chatbot.greencarcarpool.com/script/chatbot.css'; // Localhost URL
    document.head.appendChild(cssLink);

    // Add click event to toggle chatbot
    $('#chatbot-toggle').on('click', function() {
        toggleChatbot();
    });

    // Chatbot Logic
    let userType = '';
    let userData = { 
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
    let userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    let currentStep = 0;
    let isChatComplete = false;

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
                saveUserInput('user_type', userType, userId);
                currentStep = 1;
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
        if (!userData[column] || userData[column] === '') {
            userData[column] = value;
        }
        $('#loading').addClass('show');
        $.ajax({
            url: 'https://recruitment-chatbot.greencarcarpool.com/process_chat.php', // Localhost URL
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
                    currentStep = data.nextStep;
                    if (column === 'user_type') {
                        showNextQuestion();
                    } else {
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
            if (filledFields === 1) {
                typeMessage("What’s your name?");
            } else if (filledFields === 2) {
                typeMessage("Your organisation name?");
            } else if (filledFields === 3) {
                typeMessage("You are from which City & State?");
            } else if (filledFields === 4) {
                typeMessage("Great! What position are you looking to hire for?");
            } else if (filledFields === 5) {
                typeMessage("Nice! How many people do you want to hire?");
            } else if (filledFields === 6) {
                typeMessage("Got it! Any specific skills, qualifications, or experience you require for this role?");
            } else if (filledFields === 7) {
                typeMessage("Please provide your email address (e.g., ankit2@email.com).");
            } else if (filledFields === 8) {
                typeMessage("Please provide your phone number.");
            } else if (filledFields === 9) {
                typeMessage("Thanks for the details! Our Sales Team will connect with you soon.");
                setTimeout(() => {
                    typeMessage("Your enquiry has been saved successfully!");
                    disableChatInput();
                }, 1000);
            }
        } else if (userType === 'job seeker') {
            const filledFields = Object.keys(userData).filter(key => userData[key] !== '' && userData[key] !== null && userData[key] !== undefined).length;
            if (filledFields === 1) {
                typeMessage("What’s your name?");
            } else if (filledFields === 2) {
                showOptionsForFresherExperienced();
            } else if (filledFields === 3) {
                showOptionsForJobApplication();
            } else if (filledFields === 4) {
                typeMessage("Awesome! What type of job are you looking for? E.g., Software Developer, Marketing Specialist, etc.");
            } else if (filledFields === 5) {
                if (userData.fresher_experienced.toLowerCase() === 'experienced') {
                    typeMessage("Great! How many years of experience do you have in this field?");
                } else {
                    userData.experience_years = '0';
                    saveUserInput('experience_years', '0', userId);
                }
            } else if (filledFields === 6 || (filledFields === 5 && userData.fresher_experienced.toLowerCase() === 'fresher')) {
                typeMessage("Thanks! What specific skills or Degree do you have that make you stand out for this role?");
            } else if (filledFields === 7) {
                typeMessage("Perfect! Are you open to relocating, or do you prefer a specific location like a city or region?");
            } else if (filledFields === 8) {
                typeMessage("Please provide your email address (e.g., ankit2@email.com).");
            } else if (filledFields === 9) {
                typeMessage("Please provide your phone number.");
            } else if (filledFields === 10) {
                typeMessage("Any other comments?");
            } else if (filledFields === 11) {
                typeMessage("Thank you for sharing your details! We have saved your information and will connect with you soon. Please note that we place candidates based on company requirements—we do not create job openings.");
                setTimeout(() => {
                    typeMessage("Your enquiry has been saved successfully!");
                    disableChatInput();
                }, 1000);
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
            // Add buttons without inline onclick
            $options.append(`<button class="option-btn mr-2 mb-2" data-option="${option}">${option}</button>`);
        });
        // Add click event listener using jQuery
        $('.option-btn').off('click').on('click', function() {
            const selectedOption = $(this).data('option');
            selectOption(selectedOption);
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
        }, 50);
    }

    function getColumnForStep() {
        const filledFields = Object.keys(userData).filter(key => userData[key] !== '' && userData[key] !== null && userData[key] !== undefined).length;
        if (userType === 'employer') {
            switch (filledFields) {
                case 0: return 'user_type';
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
                case 0: return 'user_type';
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
                case 1: return 'text';
                case 2: return 'text';
                case 3: return 'text';
                case 4: return 'text';
                case 5: return 'number';
                case 6: return 'text';
                case 7: return 'email';
                case 8: return 'phone';
            }
        } else if (userType === 'job seeker') {
            switch (filledFields) {
                case 1: return 'text';
                case 4: return 'text';
                case 5: return 'number';
                case 6: return 'text';
                case 7: return 'text';
                case 8: return 'email';
                case 9: return 'phone';
                case 10: return 'text';
            }
        }
        return 'text';
    }

    function validateLocalInput(input, type) {
        switch (type) {
            case 'email':
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input);
            case 'phone':
                return /^[0-9]{10,15}$/.test(input);
            case 'number':
                return /^[0-9]+$/.test(input) && parseInt(input) > 0;
            case 'text':
                return input.trim().length > 0;
            default:
                return true;
        }
    }

    function showValidationError(message) {
        $('#validationError').text(message).removeClass('hidden').addClass('show');
        setTimeout(hideValidationError, 3000);
    }

    function hideValidationError() {
        $('#validationError').addClass('hidden').removeClass('show');
    }

    function disableChatInput() {
        $('#userInput').prop('disabled', true).attr('placeholder', 'Chat completed. Clear to start anew.');
        $('button[onclick="sendMessage()"]').prop('disabled', true).addClass('opacity-50 cursor-not-allowed').removeClass('hover:scale-105');
        isChatComplete = true;
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
        userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        currentStep = 0;
        isChatComplete = false;
        $('#userInput').prop('disabled', false).attr('placeholder', 'Type your message or select an option...');
        $('button[onclick="sendMessage()"]').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed').addClass('hover:scale-105');
        typeMessage('Hello! Are you an employer looking to hire, or a job seeker looking for a job?');
        showOptions(['Employer', 'Job Seeker']);
    }

    // Enter key support
    $('#userInput').on('keypress', function(e) {
        if (e.which === 13 && !isChatComplete) {
            sendMessage();
        }
    });

    // Initialize the chatbot
    $(document).ready(function() {
        typeMessage('Hello! Are you an employer looking to hire, or a job seeker looking for a job?');
        showOptions(['Employer', 'Job Seeker']);
    });
}

// Toggle Chatbot visibility
function toggleChatbot() {
    $('#chatbot-box').toggleClass('hidden');
}