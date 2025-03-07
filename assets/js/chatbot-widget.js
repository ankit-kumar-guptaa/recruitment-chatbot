(function() {
    // Enhanced CSS with modern design and animations
    const style = document.createElement('style');
    style.innerHTML = `
        #chatbot-toggle {
            z-index: 1000;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4a90e2, #50e3c2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(74, 144, 226, 0.4);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        #chatbot-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(74, 144, 226, 0.6);
        }
        #chatbot-toggle i {
            color: #fff;
            font-size: 24px;
        }
        #chatbox {
            z-index: 1000;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 0;
            height: 0;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: none;
            transition: all 0.4s ease-out;
        }
        #chatbox.open {
            width: 380px;
            height: 520px;
            display: flex;
            flex-direction: column;
            animation: slideIn 0.4s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        #messages {
            padding: 20px;
            flex-grow: 1;
            overflow-y: auto;
            background: linear-gradient(135deg, #f5f7fa, #e0eaff);
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .message {
            margin: 12px 0;
            padding: 15px 20px;
            border-radius: 20px;
            line-height: 1.5;
            max-width: 80%;
            word-wrap: break-word;
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .bot-message {
            background: #e6f3ff;
            align-self: flex-start;
            border-left: 5px solid #4a90e2;
        }
        .user-message {
            background: #ffebee;
            color: #333;
            align-self: flex-end;
            border-right: 5px solid #f44336;
        }
        .option-btn {
            background: #fff3e0;
            border: none;
            padding: 10px 18px;
            margin: 5px;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            font-size: 14px;
            border: 2px solid #ff9800;
            color: #ff5722;
        }
        .option-btn:hover {
            background: #ffe0b2;
            transform: scale(1.05);
        }
        #chatbox input {
            width: 70%;
            padding: 15px;
            border: none;
            border-top: 1px solid #ddd;
            border-bottom-left-radius: 15px;
            outline: none;
            font-size: 14px;
            background: #f8f9fa;
            transition: background 0.3s;
        }
        #chatbox input:focus {
            background: #fff;
        }
        #chatbox button {
            width: 30%;
            background: linear-gradient(135deg, #4a90e2, #50e3c2);
            color: white;
            border: none;
            padding: 15px;
            border-bottom-right-radius: 15px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            font-size: 14px;
        }
        #chatbox button:hover {
            background: linear-gradient(135deg, #357abd, #38d9a9);
            transform: scale(1.05);
        }
        #validationError {
            color: #d32f2f;
            font-size: 12px;
            padding: 10px;
            display: none;
            background: #ffebee;
            border-radius: 10px;
            text-align: center;
            animation: shake 0.5s;
        }
        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
            100% { transform: translateX(0); }
        }
        #clearBtn {
            width: 100%;
            background: linear-gradient(135deg, #d32f2f, #b71c1c);
            color: white;
            border: none;
            padding: 15px;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            font-size: 14px;
        }
        #clearBtn:hover {
            background: linear-gradient(135deg, #ef5350, #c62828);
            transform: scale(1.02);
        }
        #ai-label {
            position: fixed;
            bottom: 15px;
            right: 100px;
            font-size: 12px;
            color: #424242;
            background: rgba(255, 255, 255, 0.95);
            padding: 8px 12px;
            border-radius: 15px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            font-weight: bold;
        }
        #loading {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #4a90e2;
        }
        #loading.show {
            display: block;
        }
    `;
    document.head.appendChild(style);

    // Create Chatbot UI
    const chatbotButton = document.createElement('div');
    chatbotButton.id = 'chatbot-toggle';
    chatbotButton.innerHTML = '<i class="fas fa-comment"></i>';
    document.body.appendChild(chatbotButton);

    const chatbox = document.createElement('div');
    chatbox.id = 'chatbox';
    chatbox.innerHTML = `
        <div id="messages"></div>
        <div style="display: flex; align-items: center; border-top: 1px solid #ddd;">
            <input type="text" id="userInput" placeholder="Type your message or select an option...">
            <button onclick="sendMessage()">Send</button>
        </div>
        <div id="validationError"></div>
        <button id="clearBtn" onclick="clearChat()">Clear</button>
        <div id="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
        <div id="ai-label">Powered by AI Recruit AI</div>
    `;
    document.body.appendChild(chatbox);

    // Initialize chatbot
    let userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    let currentStep = 0;
    let userType = '';
    let isChatComplete = false;

    window.sendMessage = function() {
        if (isChatComplete) {
            showValidationError('Chat is complete. Please start a new session to begin again.');
            return;
        }

        let input = $('#userInput').val().trim();
        if (input === '') {
            showValidationError('Please enter a message or select an option.');
            return;
        }

        $('#messages').append(`<div class="message user-message">${input}</div>`);
        $('#userInput').val('');
        $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
        hideValidationError();
        $('#loading').addClass('show');

        $.ajax({
            url: 'https://recruitment-chatbot.greencarcarpool.com/api/chatbot_api.php',
            type: 'POST',
            data: { action: 'send', userId: userId, message: input },
            dataType: 'json',
            success: function(data) {
                $('#loading').removeClass('show');
                if (data.status === 'success') {
                    if (data.question) typeMessage(data.question);
                    if (data.options) showOptions(data.options);
                    if (data.complete) {
                        setTimeout(() => {
                            typeMessage('Your enquiry has been saved successfully!');
                            disableChatInput();
                        }, 1000);
                    }
                    currentStep = data.step || currentStep + 1;
                    userType = data.userType || userType;
                    console.log('Updated Step:', currentStep, 'User Type:', userType);
                } else {
                    showValidationError(data.message);
                }
            },
            error: function(xhr, status, error) {
                $('#loading').removeClass('show');
                console.error('AJAX Error:', error, 'Status:', status, 'Response:', xhr.responseText);
                showValidationError('Error connecting to server. Please try again or contact support at support@example.com.');
            }
        });
    };

    window.clearChat = function() {
        $('#messages').empty();
        $('#loading').removeClass('show');
        userType = '';
        userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        currentStep = 0;
        isChatComplete = false;
        $('#userInput').prop('disabled', false).attr('placeholder', 'Type your message or select an option...');
        $('button[onclick="sendMessage()"]').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed').addClass('hover:scale-105');
        startChat();
    };

    document.getElementById('chatbot-toggle').addEventListener('click', function() {
        const chatbox = document.getElementById('chatbox');
        if (chatbox.classList.contains('open')) {
            chatbox.classList.remove('open');
            chatbox.style.display = 'none';
        } else {
            chatbox.classList.add('open');
            chatbox.style.display = 'flex';
            if (currentStep === 0) startChat();
        }
    });

    document.addEventListener('click', function(event) {
        const chatbox = document.getElementById('chatbox');
        if (!chatbox.contains(event.target) && event.target.id !== 'chatbot-toggle') {
            chatbox.classList.remove('open');
            chatbox.style.display = 'none';
        }
    });

    function startChat() {
        $('#loading').addClass('show');
        $.ajax({
            url: 'https://recruitment-chatbot.greencarcarpool.com/api/chatbot_api.php',
            type: 'POST',
            data: { action: 'start', userId: userId },
            dataType: 'json',
            success: function(data) {
                $('#loading').removeClass('show');
                if (data.status === 'success') {
                    typeMessage(data.question);
                    if (data.options) showOptions(data.options);
                    currentStep = 1;
                    userType = '';
                } else {
                    showValidationError(data.message || 'Error starting chat.');
                }
            },
            error: function(xhr, status, error) {
                $('#loading').removeClass('show');
                console.error('AJAX Error:', error, 'Status:', status, 'Response:', xhr.responseText);
                showValidationError('Error connecting to server. Please try again or contact support at support@example.com.');
            }
        });
    }

    window.selectOption = function(option) {
        $('#userInput').val(option);
        sendMessage();
    };

    function showOptions(options) {
        let $options = $('#messages').append('<div class="flex flex-wrap mt-4"></div>');
        options.forEach(option => {
            $options.append(`<button class="option-btn mr-2 mb-2" onclick="selectOption('${option}')">${option}</button>`);
        });
        $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
    }

    function typeMessage(text) {
        let $message = $('<div class="message bot-message"></div>');
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

    // Enter key support
    $('#userInput').on('keypress', function(e) {
        if (e.which === 13 && !isChatComplete) sendMessage();
    });

    // Initialize
    $(document).ready(function() {
        startChat();
    });
})();