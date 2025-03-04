<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-gray-900 to-gray-800 text-gray-100 font-sans antialiased min-h-screen flex flex-col">
    <header class="fixed top-0 left-0 w-full bg-gradient-to-r from-blue-900 via-gray-800 to-blue-700 shadow-lg z-50">
        <div class="container mx-auto p-4 max-w-3xl flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-robot text-3xl text-white mr-3 animate-pulse-slow"></i>
                <h1 class="text-2xl font-bold tracking-tight text-white">Recruitment AI</h1>
            </div>
            <button class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-full shadow-md transition-transform duration-300 hover:scale-105 toggle-btn" onclick="toggleMode()">
                <i class="fas fa-adjust mr-2"></i> Toggle Theme
            </button>
        </div>
    </header>

    <div class="container mx-auto p-4 pt-20 flex-1 max-w-3xl">
        <div class="bg-white/5 backdrop-blur-md shadow-2xl rounded-3xl overflow-hidden border border-gray-700/50 transform transition-all duration-500 hover:scale-102 hover:shadow-3xl">
            <div class="p-6 bg-gradient-to-r from-blue-800 via-gray-700 to-blue-600 text-white text-center rounded-t-3xl flex items-center justify-center">
                <i class="fas fa-robot text-4xl mr-4 animate-pulse-slow"></i>
                <h2 class="text-3xl font-bold tracking-wide">Recruitment AI Assistant</h2>
            </div>
            <div id="chatbox" class="p-6 h-[calc(100vh-300px)] overflow-y-auto scrollbar-thin scrollbar-thumb-blue-700 scrollbar-track-gray-800 relative">
                <div id="messages" class="space-y-4"></div>
                <div id="loading" class="hidden absolute inset-0 flex items-center justify-center bg-gray-900/70">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i>
                </div>
                <div class="absolute inset-0 bg-gradient-to-r from-transparent to-blue-900/10 pointer-events-none"></div>
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIj48ZyBmaWxsPSIjZmZmIiBvcGFjaXR5PSIuMDUiPjxjaXJjbGUgY3g9IjUwIiBjeT0iNTAiIHI9IjIiLz48L2c+PC9zdmc+')] bg-repeat opacity-5 pointer-events-none"></div>
            </div>
        </div>
    </div>

    <footer class="w-full bg-gradient-to-r from-gray-700 to-gray-800 p-4 shadow-lg border-t border-gray-600 z-40">
        <div class="container mx-auto max-w-3xl flex items-center justify-between">
            <div class="flex-1 flex space-x-4 items-center">
                <input type="text" id="userInput" class="flex-1 p-4 border border-gray-600 rounded-l-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-800/50 text-white placeholder-gray-400 shadow-md transition-transform duration-300 hover:scale-102" placeholder="Type your message or select an option...">
                <button class="bg-blue-700 hover:bg-blue-600 text-white p-4 rounded-r-2xl transition-transform duration-300 hover:scale-105 shadow-md flex items-center" onclick="sendMessage()">
                    <i class="fas fa-paper-plane mr-2"></i> Send
                </button>
                <div id="validationError" class="ml-4 text-red-500 text-sm transition-opacity duration-300 opacity-0 hidden">Please enter valid input!</div>
            </div>
            <button class="ml-4 bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-full shadow-md transition-transform duration-300 hover:scale-105 flex items-center" onclick="clearChat()">
                <i class="fas fa-trash mr-2"></i> Clear
            </button>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>