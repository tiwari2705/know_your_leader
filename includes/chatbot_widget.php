<div class="chat-widget-container">
    <button id="chat-bubble-btn" class="btn btn-primary btn-lg rounded-circle shadow-lg">
        <i class="fas fa-robot"></i>
    </button>

    <div id="chat-window" class="card shadow-lg">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-robot me-2"></i>Political Assistant</h5>
            <button id="chat-close-btn" class="btn-close btn-close-white"></button>
        </div>
        <div class="card-body" id="chat-history">
            <div class="chat-message ai-message">
                <p>Hello! How can I help you today? You can ask me about any leader in our database or for recent political news.</p>
            </div>
        </div>
        <div class="card-footer">
            <form id="chat-form">
                <div class="input-group">
                    <input type="text" id="chat-input" class="form-control" placeholder="Ask a question..." required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>