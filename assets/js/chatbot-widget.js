(function() {
    // Inject CSS with creative design, smooth animation, and AI label
    const style = document.createElement('style');
    style.innerHTML = `
        #chatbot-toggle { 
            z-index: 1000; 
            position: fixed; 
            bottom: 20px; 
            right: 20px; 
            width: 60px; 
            height: 60px; 
            background: linear-gradient(135deg, #ff6f61, #6b48ff); 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            cursor: pointer; 
            box-shadow: 0 4px 15px rgba(107, 72, 255, 0.4); 
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
        }
        #chatbot-toggle:hover { 
            transform: scale(1.1); 
            box-shadow: 0 6px 20px rgba(107, 72, 255, 0.6); 
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
            width: 60px; 
            height: 60px; 
            background: #fff; 
            border-radius: 15px; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
            overflow: hidden; 
            display: none; 
            transition: all 0.4s ease-out; 
        }
        #chatbox.open { 
            width: 360px; 
            height: 500px; 
            border-radius: 15px; 
            display: flex; 
            flex-direction: column; 
            animation: slideIn 0.4s ease-out; 
        }
        @keyframes slideIn { 
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        #messages { 
            padding: 15px; 
            flex-grow: 1; 
            overflow-y: auto; 
            background: linear-gradient(135deg, #f0f4f8, #e0e7ff); 
            border-top-left-radius: 15px; 
            border-top-right-radius: 15px; 
        }
        .message { 
            margin: 10px 0; 
            padding: 12px 18px; 
            border-radius: 18px; 
            line-height: 1.5; 
            max-width: 75%; 
            word-wrap: break-word; 
        }
        .bot-message { 
            background: #e0f7fa; 
            align-self: flex-start; 
            position: relative; 
            overflow: hidden; 
            border-left: 4px solid #00c4cc; 
        }
        .user-message { 
            background: #ffeb3b; 
            color: #333; 
            align-self: flex-end; 
            border-right: 4px solid #ffca28; 
        }
        .typing { 
            background: #e0f7fa; 
            align-self: flex-start; 
        }
        .typing::after { 
            content: ''; 
            display: inline-block; 
            width: 10px; 
            height: 10px; 
            background: #00c4cc; 
            border-radius: 50%; 
            animation: typing-dot 1.4s infinite ease-in-out; 
        }
        @keyframes typing-dot { 
            0%, 80%, 100% { opacity: 0; } 
            40% { opacity: 1; } 
        }
        .option-btn { 
            background: #fff3e0; 
            border: none; 
            padding: 8px 15px; 
            margin: 5px; 
            border-radius: 15px; 
            cursor: pointer; 
            transition: background-color 0.3s; 
            font-size: 14px; 
            border: 1px solid #ff9800; 
            color: #ff5722; 
        }
        .option-btn:hover { 
            background: #ffe0b2; 
        }
        #chatbox input { 
            width: 70%; 
            padding: 12px; 
            border: none; 
            border-top: 1px solid #ddd; 
            border-bottom-left-radius: 15px; 
            outline: none; 
            font-size: 14px; 
            background: #f8f9fa; 
        }
        #chatbox button { 
            width: 30%; 
            background: linear-gradient(135deg, #ff9800, #ff5722); 
            color: white; 
            border: none; 
            padding: 12px; 
            border-bottom-right-radius: 15px; 
            cursor: pointer; 
            transition: background 0.3s; 
            font-size: 14px; 
        }
        #chatbox button:hover { 
            background: linear-gradient(135deg, #ff7043, #f57c00); 
        }
        #validationError { 
            color: #d32f2f; 
            font-size: 12px; 
            padding: 5px 15px; 
            display: none; 
            background: #ffebee; 
            border-radius: 5px; 
        }
        #clearBtn { 
            width: 100%; 
            background: linear-gradient(135deg, #d32f2f, #b71c1c); 
            color: white; 
            border: none; 
            padding: 12px; 
            border-bottom-left-radius: 15px; 
            border-bottom-right-radius: 15px; 
            cursor: pointer; 
            transition: background 0.3s; 
            font-size: 14px; 
        }
        #clearBtn:hover { 
            background: linear-gradient(135deg, #ef5350, #c62828); 
        }
        #ai-label { 
            position: fixed; 
            bottom: 10px; 
            right: 90px; 
            font-size: 12px; 
            color: #424242; 
            background: rgba(255, 255, 255, 0.9); 
            padding: 5px 10px; 
            border-radius: 10px; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); 
            z-index: 1000; 
            font-weight: bold; 
        }
    `;
    document.head.appendChild(style);

    // Create Chatbot Button, Container, and AI Label
    const chatbotButton = document.createElement('div');
    chatbotButton.id = 'chatbot-toggle';
    chatbotButton.innerHTML = '<i class="fas fa-comment"></i>';
    document.body.appendChild(chatbotButton);

    const chatbox = document.createElement('div');
    chatbox.id = 'chatbox';
    chatbox.innerHTML = `
        <div id="messages"></div>
        <div style="display: flex; align-items: center; border-top: 1px solid #ddd;">
            <input type="text" id="userInput" placeholder="Type your message...">
            <button onclick="sendMessage()">Send</button>
        </div>
        <div id="validationError"></div>
        <button id="clearBtn" onclick="clearChat()">Clear</button>
    `;
    document.body.appendChild(chatbox);

    const aiLabel = document.createElement('div');
    aiLabel.id = 'ai-label';
    aiLabel.textContent = 'Powered by AI Recruit AI';
    document.body.appendChild(aiLabel);

    // Global function definitions
    window.selectOption = function(option) {
        document.getElementById('userInput').value = option;
        sendMessage();
    };

    window.sendMessage = function() {
        if (isChatComplete) {
            showValidationError('Chat is complete. Please clear to start anew.');
            return;
        }

        let input = document.getElementById('userInput').value.trim();
        const column = getColumnForStep(currentStep, userType);
        if (!validateInput(input, column)) {
            showValidationError(getValidationMessage(column, input));
            return;
        }

        typeMessage(input, 'user');
        document.getElementById('userInput').value = '';
        document.getElementById('chatbox').scrollTop = document.getElementById('chatbox').scrollHeight;

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'http://localhost/recruitment-chatbot/api/chatbot_api.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);
                    if (data.status === 'success') {
                        typeMessage(data.question, 'bot', true);
                        if (data.options) showOptions(data.options);
                        if (data.complete) {
                            setTimeout(() => {
                                typeMessage('Your enquiry has been saved successfully!', 'bot', true);
                                disableChatInput();
                            }, 1000);
                        }
                        currentStep = data.step || currentStep + 1;
                    } else {
                        showValidationError(data.message);
                    }
                } else {
                    showValidationError('Error connecting to server. Status: ' + xhr.status);
                }
            }
        };
        xhr.send('action=send&userId=' + encodeURIComponent(userId) + '&message=' + encodeURIComponent(input));
    };

    window.clearChat = function() {
        document.getElementById('messages').innerHTML = '';
        document.getElementById('validationError').style.display = 'none';
        userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        currentStep = 0;
        userType = '';
        isChatComplete = false;
        document.getElementById('userInput').disabled = false;
        document.getElementById('userInput').placeholder = 'Type your message...';
        document.querySelector('button[onclick="sendMessage()"]').disabled = false;
        startChat();
    };

    // Initialize chatbot
    let userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    let currentStep = 0;
    let userType = '';
    let isChatComplete = false;

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
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'http://localhost/recruitment-chatbot/api/chatbot_api.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);
                    if (data.status === 'success') {
                        typeMessage(data.question, 'bot', true);
                        if (data.options) showOptions(data.options);
                        currentStep = 1; // Start step after initial question
                    } else {
                        showValidationError(data.message || 'Error starting chat.');
                    }
                } else {
                    showValidationError('Error connecting to server. Status: ' + xhr.status);
                }
            }
        };
        xhr.send('action=start&userId=' + encodeURIComponent(userId));
    }

    function showOptions(options) {
        const messages = document.getElementById('messages');
        const optionsDiv = document.createElement('div');
        options.forEach(option => {
            const button = document.createElement('button');
            button.textContent = option;
            button.className = 'option-btn';
            button.onclick = function() { selectOption(option); };
            optionsDiv.appendChild(button);
        });
        messages.appendChild(optionsDiv);
        document.getElementById('chatbox').scrollTop = document.getElementById('chatbox').scrollHeight;
    }

    function typeMessage(text, type, animate = false) {
        const messages = document.getElementById('messages');
        const message = document.createElement('div');
        message.className = 'message ' + type + '-message';
        messages.appendChild(message);
        if (animate) {
            let index = 0;
            message.textContent = '';
            const typingInterval = setInterval(() => {
                if (index < text.length) {
                    message.textContent += text.charAt(index);
                    index++;
                    document.getElementById('chatbox').scrollTop = document.getElementById('chatbox').scrollHeight;
                } else {
                    clearInterval(typingInterval);
                }
            }, 50);
        } else {
            message.textContent = text;
            document.getElementById('chatbox').scrollTop = document.getElementById('chatbox').scrollHeight;
        }
    }

    function showValidationError(message) {
        const errorDiv = document.getElementById('validationError');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 3000);
    }

    function disableChatInput() {
        document.getElementById('userInput').disabled = true;
        document.getElementById('userInput').placeholder = 'Chat completed.';
        document.querySelector('button[onclick="sendMessage()"]').disabled = true;
        isChatComplete = true;
    }

    function getColumnForStep(step, userType) {
        if (userType === 'employer') {
            switch (step) {
                case 1: return 'name';
                case 2: return 'organisation_name';
                case 3: return 'city_state';
                case 4: return 'position';
                case 5: return 'hiring_count';
                case 6: return 'requirements';
                case 7: return 'email';
                case 8: return 'phone';
                default: return '';
            }
        } else if (userType === 'job_seeker') {
            switch (step) {
                case 1: return 'name';
                case 2: return 'fresher_experienced';
                case 3: return 'applying_for_job';
                case 4: return 'position';
                case 5: return 'experience_years';
                case 6: return 'skills_degree';
                case 7: return 'location_preference';
                case 8: return 'email';
                case 9: return 'phone';
                case 10: return 'comments';
                default: return '';
            }
        }
        return '';
    }

    function validateInput(value, column) {
        switch (column) {
            case 'email':
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            case 'phone':
                return /^\d{10,15}$/.test(value);
            case 'hiring_count':
            case 'experience_years':
                return /^\d+$/.test(value) && parseInt(value) >= 0;
            default:
                return value.trim().length > 0;
        }
    }

    function getValidationMessage(column, value) {
        switch (column) {
            case 'email':
                return 'Please enter a valid email address (e.g., example@domain.com).';
            case 'phone':
                return 'Please enter a valid phone number (10-15 digits).';
            case 'hiring_count':
            case 'experience_years':
                return 'Please enter a valid number (0 or greater).';
            default:
                return 'Please enter a valid response.';
        }
    }
})();