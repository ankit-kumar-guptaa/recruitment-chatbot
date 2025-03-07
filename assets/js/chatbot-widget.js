(function() {
    // Create Chatbot UI
    const chatbotButton = document.createElement('div');
    chatbotButton.id = 'chatbot-toggle';
    chatbotButton.className = 'fixed bottom-6 right-6 w-16 h-16 bg-gradient-to-r from-blue-500 to-teal-400 rounded-full flex items-center justify-center cursor-pointer shadow-lg hover:scale-105 transition-transform duration-300 text-white font-bold';
    chatbotButton.textContent = 'Chat';
    document.body.appendChild(chatbotButton);

    const chatbox = document.createElement('div');
    chatbox.id = 'chatbox';
    chatbox.className = 'fixed bottom-6 right-6 w-0 h-0 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden hidden flex-col transition-all duration-500';
    chatbox.innerHTML = `
        <div id="chat-header" class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-500 to-teal-400 text-white rounded-t-2xl">
            <h2 class="text-lg font-semibold">AI Recruit Chatbot</h2>
            <button id="theme-toggle" class="text-xl">
                <span class="dark:hidden">🌙</span><span class="hidden dark:inline">☀️</span>
            </button>
        </div>
        <div id="messages" class="flex-1 p-5 overflow-y-auto bg-gray-50 dark:bg-gray-900"></div>
        <div class="flex items-center border-t border-gray-200 dark:border-gray-700">
            <input type="text" id="userInput" class="flex-1 p-4 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 outline-none" placeholder="Type your message or select an option...">
            <button onclick="sendMessage()" class="w-24 p-4 bg-gradient-to-r from-blue-500 to-teal-400 text-white font-semibold hover:from-blue-600 hover:to-teal-500 transition-colors duration-300">Send</button>
        </div>
        <div id="validationError" class="hidden p-3 text-red-600 bg-red-100 dark:bg-red-200 dark:text-red-800 text-center text-sm"></div>
        <button id="clearBtn" onclick="clearChat()" class="p-3 bg-red-500 text-white font-semibold hover:bg-red-600 transition-colors duration-300 rounded-b-2xl">Clear Chat</button>
        <div id="loading" class="hidden absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-blue-500">Loading...</div>
        <div id="ai-label" class="fixed bottom-4 right-28 text-sm text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 px-4 py-2 rounded-full shadow-md">Powered by AI Recruit AI</div>
    `;
    document.body.appendChild(chatbox);

    // Initialize chatbot
    let userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    let currentStep = 0;
    let userType = '';
    let isChatComplete = false;

    // Theme toggle
    $('#theme-toggle').on('click', function() {
        $('body').toggleClass('dark');
        localStorage.setItem('theme', $('body').hasClass('dark') ? 'dark' : 'light');
        $(this).html($('body').hasClass('dark') ? '☀️' : '🌙');
    });

    if (localStorage.getItem('theme') === 'dark') {
        $('body').addClass('dark');
        $('#theme-toggle').html('☀️');
    }

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

        $('#messages').append(`<div class="message user-message ml-auto max-w-[80%] bg-red-100 dark:bg-red-200 text-gray-800 dark:text-gray-900 p-3 rounded-2xl mb-3 shadow-md">${input}</div>`);
        $('#userInput').val('');
        $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
        hideValidationError();
        $('#loading').removeClass('hidden');

        $.ajax({
            url: 'https://recruitment-chatbot.greencarcarpool.com/api/chatbot_api.php',
            type: 'POST',
            data: { action: 'send', userId: userId, message: input },
            dataType: 'json',
            success: function(data) {
                $('#loading').addClass('hidden');
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
                $('#loading').addClass('hidden');
                console.error('AJAX Error:', error, 'Status:', status, 'Response:', xhr.responseText);
                showValidationError('Error connecting to server. Please try again or contact support at support@example.com. Status: ' + status + ', Response: ' + xhr.responseText);
            }
        });
    };

    window.clearChat = function() {
        $('#messages').empty();
        $('#loading').addClass('hidden');
        userType = '';
        userId = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        currentStep = 0;
        isChatComplete = false;
        $('#userInput').prop('disabled', false).attr('placeholder', 'Type your message or select an option...');
        $('button[onclick="sendMessage()"]').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        startChat();
    };

    document.getElementById('chatbot-toggle').addEventListener('click', function() {
        const chatbox = document.getElementById('chatbox');
        if (chatbox.classList.contains('open')) {
            chatbox.classList.remove('open');
            chatbox.classList.add('hidden');
            chatbox.style.width = '0';
            chatbox.style.height = '0';
        } else {
            chatbox.classList.add('open');
            chatbox.classList.remove('hidden');
            chatbox.style.width = '380px';
            chatbox.style.height = '520px';
            if (currentStep === 0) startChat();
        }
    });

    document.addEventListener('click', function(event) {
        const chatbox = document.getElementById('chatbox');
        if (!chatbox.contains(event.target) && event.target.id !== 'chatbot-toggle') {
            chatbox.classList.remove('open');
            chatbox.classList.add('hidden');
            chatbox.style.width = '0';
            chatbox.style.height = '0';
        }
    });

    function startChat() {
        $('#loading').removeClass('hidden');
        $.ajax({
            url: 'https://recruitment-chatbot.greencarcarpool.com/api/chatbot_api.php',
            type: 'POST',
            data: { action: 'start', userId: userId },
            dataType: 'json',
            success: function(data) {
                $('#loading').addClass('hidden');
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
                $('#loading').addClass('hidden');
                console.error('AJAX Error:', error, 'Status:', status, 'Response:', xhr.responseText);
                showValidationError('Error connecting to server. Please try again or contact support at support@example.com. Status: ' + status + ', Response: ' + xhr.responseText);
            }
        });
    }

    window.selectOption = function(option) {
        $('#userInput').val(option);
        sendMessage();
    };

    function showOptions(options) {
        let optionsHtml = '<div class="flex flex-wrap mt-3">';
        options.forEach(option => {
            optionsHtml += `<button class="option-btn mr-2 mb-2 px-4 py-2 bg-orange-100 dark:bg-orange-200 text-orange-700 dark:text-orange-800 rounded-full border-2 border-orange-500 hover:bg-orange-200 dark:hover:bg-orange-300 transition-all duration-300" onclick="selectOption('${option}')">${option}</button>`;
        });
        optionsHtml += '</div>';
        $('#messages').append(optionsHtml);
        $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
    }

    function typeMessage(text) {
        let $message = $('<div class="message bot-message max-w-[80%] bg-blue-100 dark:bg-blue-200 text-gray-800 dark:text-gray-900 p-3 rounded-2xl mb-3 shadow-md"></div>');
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
        $('#validationError').text(message).removeClass('hidden');
        setTimeout(hideValidationError, 3000);
    }

    function hideValidationError() {
        $('#validationError').addClass('hidden');
    }

    function disableChatInput() {
        $('#userInput').prop('disabled', true).attr('placeholder', 'Chat completed. Clear to start anew.');
        $('button[onclick="sendMessage()"]').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
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