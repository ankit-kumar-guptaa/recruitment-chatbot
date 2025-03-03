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
<body class="bg-gradient-to-br from-gray-900 to-gray-800 text-gray-100 font-sans antialiased">
    <button class="fixed top-4 right-4 bg-gray-600 hover:bg-gray-500 text-white px-3 py-2 rounded-full shadow-lg toggle-btn" onclick="toggleMode()">
        <i class="fas fa-adjust mr-1"></i> Theme
    </button>

    <div class="container mx-auto mt-8 p-4 max-w-2xl">
        <div class="bg-gradient-to-r from-blue-900 via-gray-800 to-blue-700 shadow-2xl rounded-2xl overflow-hidden transform transition-all duration-500 hover:scale-102 hover:shadow-3xl">
            <div class="p-6 bg-gradient-to-r from-blue-800 via-gray-700 to-blue-600 text-white text-center rounded-t-2xl flex items-center justify-center">
                <i class="fas fa-robot text-3xl mr-3 animate-pulse"></i>
                <h1 class="text-3xl font-bold tracking-wide">Recruitment AI</h1>
            </div>
            <div id="chatbox" class="p-6 h-[calc(100vh-180px)] overflow-y-auto scrollbar-thin scrollbar-thumb-blue-700 scrollbar-track-gray-800 relative">
                <div id="messages" class="space-y-4"></div>
                <div id="loading" class="hidden absolute inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i>
                </div>
                <div class="absolute inset-0 bg-gradient-to-r from-transparent to-blue-900 opacity-5 pointer-events-none"></div>
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIj48ZyBmaWxsPSIjZmZmIiBvcGFjaXR5PSIuMTUiPjxjaXJjbGUgY3g9IjUwIiBjeT0iNTAiIHI9IjMiLz48L2c+PC9zdmc+')] bg-repeat opacity-3 pointer-events-none"></div>
            </div>
        </div>
    </div>

    <footer class="fixed bottom-0 left-0 w-full bg-gradient-to-r from-gray-700 to-gray-800 p-4 shadow-2xl">
        <div class="container mx-auto max-w-2xl flex items-center justify-between">
            <div class="flex-1 flex space-x-3 items-center">
                <input type="text" id="userInput" class="flex-1 p-4 border border-gray-600 rounded-l-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-800 text-white placeholder-gray-400 shadow-inner" placeholder="Type your message or select an option...">
                <button class="bg-blue-700 hover:bg-blue-600 text-white p-4 rounded-r-2xl transition duration-300 shadow-md flex items-center" onclick="sendMessage()">
                    <i class="fas fa-paper-plane mr-1"></i> Send
                </button>
                <div id="validationError" class="ml-3 text-red-500 text-sm hidden">Please enter valid input!</div>
            </div>
            <button class="ml-3 bg-gray-600 hover:bg-gray-500 text-white px-3 py-2 rounded-full shadow-md flex items-center" onclick="clearChat()">
                <i class="fas fa-trash mr-1"></i> Clear
            </button>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>