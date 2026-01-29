<x-farmer-layout>
    <x-slot name="title">Help and Support - FAQ</x-slot>

    <div class="min-h-full bg-gray-50" x-data="helpPage()">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-xl font-semibold text-gray-800">How can we help?</h1>
            </div>

            <!-- Tab Navigation -->
            <div class="flex space-x-4 mb-8">
                <button @click="activeTab = 'topics'" 
                        :class="activeTab === 'topics' ? 'bg-green-500 text-white' : 'bg-white text-gray-700 border border-gray-300'"
                        class="flex-1 px-6 py-3 rounded-full font-medium transition-colors">
                    Popular Topics
                </button>
                <button @click="activeTab = 'chat'" 
                        :class="activeTab === 'chat' ? 'bg-green-500 text-white' : 'bg-white text-gray-700 border border-gray-300'"
                        class="flex-1 px-6 py-3 rounded-full font-medium transition-colors">
                    Chat Support
                </button>
                <button @click="activeTab = 'tickets'" 
                        :class="activeTab === 'tickets' ? 'bg-green-500 text-white' : 'bg-white text-gray-700 border border-gray-300'"
                        class="flex-1 px-6 py-3 rounded-full font-medium transition-colors">
                    Tickets
                </button>
            </div>

            <!-- Popular Topics Tab -->
            <div x-show="activeTab === 'topics'" class="space-y-8">
                <!-- Registration and Account Management -->
                <div>
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Registration and Account Management</h2>
                    <div class="space-y-3 ml-4">
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('reg1')">
                            <p class="text-gray-700">• How do I register for an account?</p>
                            <div x-show="openAnswer === 'reg1'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                To register, click the "Sign Up" button on the login page. Fill in your personal information including your name, contact details, municipality, and create a password. You'll need a valid RSBSA ID to complete registration.
                            </div>
                        </div>
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('reg2')">
                            <p class="text-gray-700">• What information do I need to provide for registration?</p>
                            <div x-show="openAnswer === 'reg2'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                You'll need: Full name, RSBSA ID number, contact number, municipality in Benguet, and a valid email address (optional). Your RSBSA ID helps verify your farmer status.
                            </div>
                        </div>
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('reg3')">
                            <p class="text-gray-700">• How do I recover my username/reset my password?</p>
                            <div x-show="openAnswer === 'reg3'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                Click "Forgot Password" on the login page. Enter your registered email or contact number, and we'll send you instructions to reset your password. If you can't access your account, contact the DA-Benguet office.
                            </div>
                        </div>
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('reg4')">
                            <p class="text-gray-700">• Can I link multiple farm operations to one account?</p>
                            <div x-show="openAnswer === 'reg4'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                Currently, each account is linked to one RSBSA ID. If you manage multiple farm operations, you can track all of them under one account by adding different crop plans in your calendar.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technical Support and Troubleshooting -->
                <div>
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Technical Support and Troubleshooting</h2>
                    <div class="space-y-3 ml-4">
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('tech1')">
                            <p class="text-gray-700">• Who can I contact for technical support?</p>
                            <div x-show="openAnswer === 'tech1'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                For technical support, you can:<br>
                                - Email: support@pasya-benguet.ph<br>
                                - Call: DA-Benguet Hotline at (074) 422-XXXX<br>
                                - Visit: DA Provincial Office, La Trinidad, Benguet<br>
                                - Use the "Submit Ticket" feature in this app
                            </div>
                        </div>
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('tech2')">
                            <p class="text-gray-700">• What permissions does the app require, and why?</p>
                            <div x-show="openAnswer === 'tech2'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                PASYA may request:<br>
                                - <strong>Location:</strong> To provide accurate weather data for your municipality<br>
                                - <strong>Notifications:</strong> To alert you about weather changes, price updates, and harvest reminders<br>
                                - <strong>Camera (optional):</strong> For uploading crop photos if needed
                            </div>
                        </div>
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('tech3')">
                            <p class="text-gray-700">• I'm having trouble with [specific function]. How can I fix it?</p>
                            <div x-show="openAnswer === 'tech3'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                Try these steps:<br>
                                1. Refresh the page or restart the app<br>
                                2. Clear your browser cache<br>
                                3. Check your internet connection<br>
                                4. Log out and log back in<br>
                                If the problem persists, submit a support ticket with details about the issue.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data and Privacy -->
                <div>
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Data and Privacy</h2>
                    <div class="space-y-3 ml-4">
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('data1')">
                            <p class="text-gray-700">• How is my farm data used?</p>
                            <div x-show="openAnswer === 'data1'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                Your farm data is used to:<br>
                                - Provide personalized crop recommendations<br>
                                - Generate harvest predictions based on your plans<br>
                                - Help DA-Benguet plan agricultural support programs<br>
                                Your individual data is kept confidential and only aggregated statistics are shared.
                            </div>
                        </div>
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('data2')">
                            <p class="text-gray-700">• How long is my data stored?</p>
                            <div x-show="openAnswer === 'data2'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                Your data is stored securely for as long as your account is active. Historical crop plans and harvest records are kept to help you track your farming progress over the years.
                            </div>
                        </div>
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('data3')">
                            <p class="text-gray-700">• How do I delete my account and associated data?</p>
                            <div x-show="openAnswer === 'data3'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                To delete your account, please contact DA-Benguet support. They will verify your identity and process your request within 30 days. Note that some anonymized agricultural data may be retained for research purposes.
                            </div>
                        </div>
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('data4')">
                            <p class="text-gray-700">• Is my data shared with third parties?</p>
                            <div x-show="openAnswer === 'data4'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                Your personal data is NOT shared with third parties. Only aggregated, anonymous statistics may be shared with partner agencies like the Department of Agriculture for planning purposes.
                            </div>
                        </div>
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('data5')">
                            <p class="text-gray-700">• Can I export my data from the app?</p>
                            <div x-show="openAnswer === 'data5'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                Yes! You can view and print your harvest history from the Harvest History page. For a complete data export, please contact support.
                            </div>
                        </div>
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('data6')">
                            <p class="text-gray-700">• Can I share information with other farmers or agricultural experts through the app?</p>
                            <div x-show="openAnswer === 'data6'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                Currently, PASYA focuses on individual farmer support. Community features for sharing information with other farmers are planned for future updates.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Using PASYA Features -->
                <div>
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Using PASYA Features</h2>
                    <div class="space-y-3 ml-4">
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('feat1')">
                            <p class="text-gray-700">• How do I use the Calendar to plan my crops?</p>
                            <div x-show="openAnswer === 'feat1'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                1. Go to the Calendar page<br>
                                2. Click "Plan New Crop"<br>
                                3. Select your crop type, planting date, and area<br>
                                4. The system will calculate your expected harvest date and predicted yield<br>
                                5. Save your plan to track it in your Harvest History
                            </div>
                        </div>
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('feat2')">
                            <p class="text-gray-700">• How does Price Watch work?</p>
                            <div x-show="openAnswer === 'feat2'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                Price Watch shows current market prices from La Trinidad Trading Post. Prices are updated regularly. You can filter by crop type and see price trends to help decide the best time to sell your harvest.
                            </div>
                        </div>
                        <div class="cursor-pointer hover:text-green-600 transition" @click="toggleAnswer('feat3')">
                            <p class="text-gray-700">• What do the weather forecasts mean for my crops?</p>
                            <div x-show="openAnswer === 'feat3'" x-collapse class="ml-4 mt-2 text-gray-600 text-sm bg-green-50 p-3 rounded-lg">
                                The weather widget shows current conditions and forecasts for your municipality. Pay attention to:<br>
                                - <strong>Temperature:</strong> Most Benguet vegetables prefer 15-25°C<br>
                                - <strong>Rain:</strong> Heavy rain may affect planting or harvesting<br>
                                - <strong>Humidity:</strong> High humidity can increase disease risk
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Support Tab -->
            <div x-show="activeTab === 'chat'" class="bg-gray-900 rounded-2xl overflow-hidden shadow-xl" style="height: 500px;">
                <div class="flex flex-col h-full">
                    <!-- Chat Messages Area -->
                    <div class="flex-1 p-4 overflow-y-auto" id="chatMessages">
                        <!-- Bot Welcome Message -->
                        <div class="flex items-start mb-4">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                                    <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/>
                                </svg>
                            </div>
                            <div class="bg-green-100 rounded-2xl rounded-tl-none p-4 max-w-md">
                                <p class="text-gray-800 text-sm font-medium mb-3">Hello! I'm PASYA Assistant. How can I help you today?</p>
                                <p class="text-gray-700 text-sm mb-2">Here are some common questions:</p>
                                <div class="space-y-2">
                                    <button @click="sendQuickQuestion('How do I register for an account?')" 
                                            class="block w-full text-left text-sm text-gray-700 hover:text-green-600 transition">
                                        • How do I register for an account?
                                    </button>
                                    <button @click="sendQuickQuestion('What information do I need to provide for registration?')" 
                                            class="block w-full text-left text-sm text-gray-700 hover:text-green-600 transition">
                                        • What information do I need to provide for registration?
                                    </button>
                                    <button @click="sendQuickQuestion('How do I recover my username/reset my password?')" 
                                            class="block w-full text-left text-sm text-gray-700 hover:text-green-600 transition">
                                        • How do I recover my username/reset my password?
                                    </button>
                                    <button @click="sendQuickQuestion('Can I link multiple farm operations to one account?')" 
                                            class="block w-full text-left text-sm text-gray-700 hover:text-green-600 transition">
                                        • Can I link multiple farm operations to one account?
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Dynamic Chat Messages -->
                        <template x-for="(msg, index) in chatMessages" :key="index">
                            <div :class="msg.isUser ? 'justify-end' : 'justify-start'" class="flex mb-4">
                                <!-- Bot Avatar -->
                                <div x-show="!msg.isUser" class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                                    </svg>
                                </div>
                                
                                <div :class="msg.isUser ? 'bg-green-500 text-white rounded-br-none' : 'bg-green-100 text-gray-800 rounded-tl-none'" 
                                     class="rounded-2xl p-4 max-w-md">
                                    <p class="text-sm" x-text="msg.text"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Online Status -->
                    <div class="px-4 py-2">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-green-400 text-xs">Online</span>
                        </div>
                    </div>
                    
                    <!-- Chat Input -->
                    <div class="p-4 border-t border-gray-700">
                        <div class="flex items-center bg-gray-800 rounded-full px-4 py-2">
                            <input type="text" 
                                   x-model="chatInput" 
                                   @keyup.enter="sendMessage"
                                   placeholder="Type your question here..."
                                   class="flex-1 bg-transparent text-white placeholder-gray-400 focus:outline-none text-sm">
                            <button @click="sendMessage" class="ml-3 text-gray-400 hover:text-green-400 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets Tab -->
            <div x-show="activeTab === 'tickets'" class="bg-white rounded-xl p-6 shadow-sm">
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Need More Help?</h3>
                    <p class="text-gray-600 mb-6">Submit a support ticket and we'll get back to you as soon as possible.</p>
                    
                    <form @submit.prevent="submitTicket" class="max-w-md mx-auto text-left space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input type="text" x-model="ticket.subject" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="What do you need help with?">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select x-model="ticket.category" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select a category</option>
                                <option value="account">Account Issues</option>
                                <option value="technical">Technical Problem</option>
                                <option value="feature">Feature Request</option>
                                <option value="data">Data & Privacy</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                            <textarea x-model="ticket.message" rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                      placeholder="Describe your issue or question in detail..."></textarea>
                        </div>
                        <button type="submit" 
                                class="w-full bg-green-500 text-white py-3 rounded-lg font-medium hover:bg-green-600 transition">
                            Submit Ticket
                        </button>
                    </form>
                </div>

                <!-- My Tickets List -->
                <div class="mt-8 border-t pt-6">
                    <h4 class="font-semibold text-gray-800 mb-4">My Previous Tickets</h4>
                    <div class="text-center text-gray-500 py-4">
                        <p>No tickets submitted yet.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rate Us Button -->
        <div class="fixed bottom-6 right-6">
            <button @click="showRatingModal = true" class="flex flex-col items-center text-gray-600 hover:text-green-600 transition">
                <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center shadow-lg mb-1">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium">Rate Us</span>
            </button>
        </div>

        <!-- Rating Modal -->
        <div x-show="showRatingModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
             @click="showRatingModal = false"
             style="display: none;">
            <div class="bg-white rounded-xl p-6 w-80 shadow-xl" @click.stop>
                <!-- Stars -->
                <div class="flex justify-center space-x-1 mb-4">
                    <template x-for="star in 5" :key="star">
                        <button @click="rating = star" class="text-3xl transition-transform hover:scale-110"
                                :class="star <= rating ? 'text-yellow-400' : 'text-gray-300'">
                            ★
                        </button>
                    </template>
                </div>
                
                <p class="text-center text-gray-800 font-medium mb-4">Rate Us!</p>
                
                <!-- Comment Box -->
                <div class="bg-gray-100 rounded-lg p-3 mb-4">
                    <textarea x-model="feedback" rows="3" 
                              class="w-full bg-transparent text-gray-700 text-sm focus:outline-none resize-none"
                              placeholder="Add Comment"></textarea>
                </div>
                
                <button @click="submitRating" 
                        class="w-full bg-green-400 text-white py-2 rounded-lg font-medium hover:bg-green-500 transition">
                    Submit
                </button>
            </div>
        </div>

        <!-- Thank You Modal -->
        <div x-show="showThankYou" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
             @click="showThankYou = false"
             style="display: none;">
            <div class="bg-green-400 rounded-full px-8 py-4 shadow-xl" @click.stop>
                <p class="text-white font-semibold text-lg">Thank you for rating us!</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function helpPage() {
            return {
                activeTab: 'topics',
                openAnswer: null,
                showRatingModal: false,
                showThankYou: false,
                rating: 0,
                feedback: '',
                chatInput: '',
                chatMessages: [],
                ticket: {
                    subject: '',
                    category: '',
                    message: ''
                },
                
                // FAQ answers for the chatbot
                faqAnswers: {
                    'How do I register for an account?': 'To register, click the "Sign Up" button on the login page. Fill in your personal information including your name, contact details, municipality, and create a password. You\'ll need a valid RSBSA ID to complete registration.',
                    'What information do I need to provide for registration?': 'You\'ll need: Full name, RSBSA ID number, contact number, municipality in Benguet, and a valid email address (optional). Your RSBSA ID helps verify your farmer status.',
                    'How do I recover my username/reset my password?': 'Click "Forgot Password" on the login page. Enter your registered email or contact number, and we\'ll send you instructions to reset your password. If you can\'t access your account, contact the DA-Benguet office.',
                    'Can I link multiple farm operations to one account?': 'Currently, each account is linked to one RSBSA ID. If you manage multiple farm operations, you can track all of them under one account by adding different crop plans in your calendar.'
                },
                
                toggleAnswer(id) {
                    this.openAnswer = this.openAnswer === id ? null : id;
                },
                
                sendQuickQuestion(question) {
                    this.chatMessages.push({ text: question, isUser: true });
                    
                    setTimeout(() => {
                        const answer = this.faqAnswers[question] || 'I\'m sorry, I don\'t have an answer for that. Please submit a ticket for personalized support.';
                        this.chatMessages.push({ text: answer, isUser: false });
                        this.scrollToBottom();
                    }, 500);
                    
                    this.scrollToBottom();
                },
                
                sendMessage() {
                    if (!this.chatInput.trim()) return;
                    
                    const userMessage = this.chatInput.trim();
                    this.chatMessages.push({ text: userMessage, isUser: true });
                    this.chatInput = '';
                    
                    setTimeout(() => {
                        // Check if the question matches any FAQ
                        let answer = null;
                        for (const [question, ans] of Object.entries(this.faqAnswers)) {
                            if (userMessage.toLowerCase().includes(question.toLowerCase().substring(0, 20))) {
                                answer = ans;
                                break;
                            }
                        }
                        
                        if (!answer) {
                            answer = 'Thank you for your question! For personalized assistance, please submit a support ticket in the "Tickets" tab or contact DA-Benguet directly.';
                        }
                        
                        this.chatMessages.push({ text: answer, isUser: false });
                        this.scrollToBottom();
                    }, 800);
                    
                    this.scrollToBottom();
                },
                
                scrollToBottom() {
                    setTimeout(() => {
                        const chatArea = document.getElementById('chatMessages');
                        if (chatArea) {
                            chatArea.scrollTop = chatArea.scrollHeight;
                        }
                    }, 100);
                },
                
                submitTicket() {
                    if (!this.ticket.subject || !this.ticket.category || !this.ticket.message) {
                        alert('Please fill in all fields');
                        return;
                    }
                    // TODO: Submit to backend
                    alert('Thank you! Your ticket has been submitted. We will get back to you soon.');
                    this.ticket = { subject: '', category: '', message: '' };
                    this.activeTab = 'topics';
                },
                
                submitRating() {
                    if (this.rating === 0) {
                        alert('Please select a rating');
                        return;
                    }
                    // TODO: Submit to backend
                    this.showRatingModal = false;
                    this.showThankYou = true;
                    
                    setTimeout(() => {
                        this.showThankYou = false;
                        this.rating = 0;
                        this.feedback = '';
                    }, 2000);
                }
            }
        }
    </script>
    @endpush
</x-farmer-layout>
