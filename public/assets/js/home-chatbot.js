(() => {
  const widget = document.querySelector('[data-home-chatbot]');
  if (!widget) return;

  const panel = widget.querySelector('.chatbot-panel');
  const toggle = widget.querySelector('[data-chatbot-toggle]');
  const close = widget.querySelector('[data-chatbot-close]');
  const prompt = widget.querySelector('[data-chatbot-prompt]');
  const messages = widget.querySelector('[data-chatbot-messages]');
  const form = widget.querySelector('[data-chatbot-form]');
  const input = widget.querySelector('[data-chatbot-input]');
  const suggestions = widget.querySelector('[data-chatbot-suggestions]');
  let greeted = false;
  let busy = false;
  const conversation = [];

  const urls = {
    login: widget.dataset.loginUrl,
    register: widget.dataset.registerUrl,
    dashboard: widget.dataset.dashboardUrl,
    ai: widget.dataset.aiUrl,
    booking: widget.dataset.bookingUrl,
    chat: widget.dataset.chatUrl,
  };

  const addMessage = (text, sender = 'bot', action = null) => {
    const message = document.createElement('div');
    message.className = `chatbot-message chatbot-message-${sender}`;
    const copy = document.createElement('p');
    copy.textContent = text;
    message.appendChild(copy);
    if (action?.url) {
      const link = document.createElement('a');
      link.href = action.url;
      link.textContent = action.label;
      link.className = 'chatbot-action';
      message.appendChild(link);
    }
    messages.appendChild(message);
    messages.scrollTop = messages.scrollHeight;
  };

  const openPanel = () => {
    panel.hidden = false;
    widget.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
    prompt.hidden = true;
    if (!greeted) {
      addMessage('Hello! I can answer medical and health-related questions and help with hospital services or appointments.');
      greeted = true;
    }
    window.setTimeout(() => input.focus(), 50);
  };

  const closePanel = () => {
    panel.hidden = true;
    widget.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.focus();
  };

  const askAssistant = async (question) => {
    if (busy) return;
    busy = true;
    input.disabled = true;
    const submit = form.querySelector('button[type="submit"]');
    submit.disabled = true;

    const thinking = document.createElement('div');
    thinking.className = 'chatbot-message chatbot-message-bot chatbot-thinking';
    const thinkingCopy = document.createElement('p');
    thinkingCopy.textContent = 'Thinking...';
    thinking.appendChild(thinkingCopy);
    messages.appendChild(thinking);
    messages.scrollTop = messages.scrollHeight;

    try {
      conversation.push({ role: 'user', content: question });
      const response = await fetch(urls.chat, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: JSON.stringify({ messages: conversation.slice(-10) }),
      });

      if (!response.ok) throw new Error(`Assistant request failed (${response.status})`);
      const data = await response.json();
      if (data.ignored === true || !data.reply) {
        conversation.pop();
        thinking.remove();
        return;
      }
      conversation.push({ role: 'assistant', content: data.reply });
      thinking.remove();
      addMessage(data.reply);
    } catch (error) {
      thinking.remove();
      conversation.pop();
    } finally {
      busy = false;
      input.disabled = false;
      submit.disabled = false;
      input.focus();
    }
  };

  const submitQuestion = (question) => {
    const cleaned = question.trim();
    if (!cleaned || busy) return;
    addMessage(cleaned, 'user');
    input.value = '';
    askAssistant(cleaned);
  };

  toggle.addEventListener('click', () => panel.hidden ? openPanel() : closePanel());
  close.addEventListener('click', closePanel);
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    submitQuestion(input.value);
  });
  suggestions.addEventListener('click', (event) => {
    const button = event.target.closest('[data-chatbot-question]');
    if (button) submitQuestion(button.dataset.chatbotQuestion);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !panel.hidden) closePanel();
  });
})();
