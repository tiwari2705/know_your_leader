const API_ENDPOINT = 'api/chat_assistant.php'; 

document.addEventListener('DOMContentLoaded', function() {

    const chatBubbleBtn = document.getElementById('chat-bubble-btn');
    const chatWindow = document.getElementById('chat-window');
    const chatCloseBtn = document.getElementById('chat-close-btn');
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatHistory = document.getElementById('chat-history');
    
    
    function addMessage(message, senderClass) {
        const msgDiv = document.createElement('div');
        msgDiv.classList.add('chat-message', senderClass); 
        msgDiv.innerHTML = `<p>${message}</p>`;
        chatHistory.appendChild(msgDiv);
        chatHistory.scrollTop = chatHistory.scrollHeight;
    }
    
    function showLoading() {
        const loadingHtml = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';
        addMessage(loadingHtml, 'ai-message');
        return chatHistory.lastChild; 
    }

    chatBubbleBtn.addEventListener('click', () => {
        chatWindow.style.display = 'flex';
    });
    
    chatCloseBtn.addEventListener('click', () => {
        chatWindow.style.display = 'none';
    });

    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const userMessage = chatInput.value.trim();
        if (!userMessage) return;

        addMessage(userMessage, 'user-message');
        chatInput.value = ''; 
        
        const loadingMessage = showLoading();

        fetch(API_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ query: userMessage })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.statusText} (${response.status})`);
            }
            return response.json();
        })
        .then(data => {
            loadingMessage.remove(); 
            
            // 4. Add AI response
            if (data.success) {
                addMessage(data.reply, 'ai-message');
            } else {
                // Display the error message returned from the PHP API
                addMessage(`Assistant Error: ${data.error}`, 'ai-message');
            }
        })
        .catch(error => {
            // Handle network/fetch errors (e.g., if the server is offline)
            loadingMessage.remove(); 
            addMessage(`Sorry, the political assistant is having connection issues. (${error.message})`, 'ai-message');
            console.error('Fetch error:', error);
        });
    });
});