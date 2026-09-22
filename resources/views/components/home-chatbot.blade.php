<aside class="home-chatbot" data-home-chatbot
    data-chat-url="{{ route('assistant.chat') }}"
    data-login-url="{{ route('login') }}"
    data-register-url="{{ route('register') }}"
    data-dashboard-url="@auth{{ route(auth()->user()->dashboardRoute()) }}@endauth"
    data-ai-url="@if(auth()->check() && auth()->user()->role?->role_name === 'patient'){{ route('patient.ai') }}@endif"
    data-booking-url="@if(auth()->check() && auth()->user()->role?->role_name === 'patient'){{ route('patient.appointments.book') }}@endif"
    aria-label="MEDICARE virtual assistant">
    <section class="chatbot-panel" id="medicare-chat-panel" role="dialog" aria-modal="false"
        aria-labelledby="medicare-chat-title" hidden>
        <header class="chatbot-header">
            <span class="chatbot-avatar" aria-hidden="true">M</span>
            <div>
                <strong id="medicare-chat-title">MEDICARE Assistant</strong>
                <small><span class="chatbot-online-dot"></span> Care guide online</small>
            </div>
            <button class="chatbot-close" type="button" data-chatbot-close aria-label="Close assistant">&times;</button>
        </header>
        <div class="chatbot-messages" data-chatbot-messages role="log" aria-live="polite" aria-relevant="additions"></div>
        <div class="chatbot-suggestions" data-chatbot-suggestions aria-label="Suggested questions">
            <button type="button" data-chatbot-question="How do I book an appointment?">Book appointment</button>
            <button type="button" data-chatbot-question="I need help choosing a doctor">Choose a doctor</button>
            <button type="button" data-chatbot-question="What services are available?">Hospital services</button>
        </div>
        <form class="chatbot-form" data-chatbot-form>
            @csrf
            <label class="visually-hidden" for="medicare-chat-input">Ask MEDICARE a question</label>
            <input id="medicare-chat-input" data-chatbot-input type="text" maxlength="1000"
                placeholder="Ask a medical or healthcare question..." autocomplete="off" required>
            <button type="submit" data-keep-enabled="true" aria-label="Send message">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 20 18-8L3 4v6l13 2-13 2v6Z"/></svg>
            </button>
        </form>
        <p class="chatbot-disclaimer">Here to guide your next step. General guidance is not a medical diagnosis.</p>
    </section>

    <span class="chatbot-prompt" data-chatbot-prompt>Hi! How can I help?</span>
    <button class="chatbot-launcher" type="button" data-chatbot-toggle aria-controls="medicare-chat-panel"
        aria-expanded="false" aria-label="Open MEDICARE assistant">
        <svg class="chatbot-message-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Zm0 14H5.17L4 17.17V4h16v12Z"/><path d="M7 7h10v2H7zm0 4h7v2H7z"/></svg>
        <svg class="chatbot-x-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m18.3 5.7-1.4-1.4L12 9.2 7.1 4.3 5.7 5.7l4.9 4.9-4.9 4.9 1.4 1.4 4.9-4.9 4.9 4.9 1.4-1.4-4.9-4.9 4.9-4.9Z"/></svg>
    </button>
</aside>
