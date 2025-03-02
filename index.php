<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment Chatbot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-gray-900 via-purple-900 to-black text-white font-sans antialiased">
    <button class="fixed top-4 right-4 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-full shadow-lg toggle-btn" onclick="toggleMode()">
        <i class="fas fa-adjust mr-2"></i> Toggle Theme
    </button>

    <div class="container mx-auto mt-12 p-4 max-w-3xl">
        <div class="bg-gradient-to-r from-blue-900 via-purple-800 to-indigo-900 shadow-3xl rounded-3xl overflow-hidden transform transition-all duration-700 hover:scale-105 hover:shadow-4xl">
            <div class="p-8 bg-gradient-to-r from-blue-800 via-purple-700 to-indigo-800 text-white text-center rounded-t-3xl flex items-center justify-center">
                <i class="fas fa-robot text-4xl mr-4 animate-pulse"></i>
                <h1 class="text-4xl font-extrabold tracking-wide">Recruitment AI - Futuristic Hiring Powerhouse</h1>
            </div>
            <div id="chatbox" class="p-8 h-[700px] overflow-y-auto scrollbar-thin scrollbar-thumb-blue-700 scrollbar-track-gray-900 relative">
                <div id="messages" class="space-y-6"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-transparent to-indigo-900 opacity-15 pointer-events-none"></div>
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIj48ZyBmaWxsPSIjZmZmIiBvcGFjaXR5PSIuMTUiPjxjaXJjbGUgY3g9IjUwIiBjeT0iNTAiIHI9IjMiLz48L2c+PC9zdmc+')] bg-repeat opacity-10 pointer-events-none"></div>
            </div>
            <div class="p-8 border-t border-gray-800 flex items-center">
                <div class="flex-1 flex space-x-3">
                    <input type="text" id="userInput" class="flex-1 p-6 border border-gray-700 rounded-l-3xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-800 text-white placeholder-gray-500 shadow-inner" placeholder="Type your message or select an option...">
                    <button class="bg-blue-700 hover:bg-blue-600 text-white p-6 rounded-r-3xl transition duration-300 shadow-md flex items-center" onclick="sendMessage()">
                        <i class="fas fa-paper-plane mr-2"></i> Send
                    </button>
                </div>
                <div id="validationError" class="ml-4 text-red-500 text-sm hidden">Please enter valid input!</div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>