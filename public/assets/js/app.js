(() => {
  const one = (selector, context = document) => context.querySelector(selector);
  const all = (selector, context = document) => [...context.querySelectorAll(selector)];
  const sidebar = one('.sidebar');
  const backdrop = one('.sidebar-backdrop');
  const openSidebar = () => { sidebar?.classList.add('open'); backdrop?.classList.add('open'); document.body.style.overflow = 'hidden'; };
  const closeSidebar = () => { sidebar?.classList.remove('open'); backdrop?.classList.remove('open'); document.body.style.overflow = ''; };

  one('.menu-toggle')?.addEventListener('click', openSidebar);
  all('[data-sidebar-close], .sidebar-backdrop').forEach(element => element.addEventListener('click', closeSidebar));
  all('.side-nav a').forEach(link => link.addEventListener('click', () => { if (window.innerWidth <= 900) closeSidebar(); }));

  all('[data-modal-open]').forEach(button => button.addEventListener('click', () => one('#' + button.dataset.modalOpen)?.classList.add('open')));
  all('[data-modal-close]').forEach(button => button.addEventListener('click', () => button.closest('.modal')?.classList.remove('open')));
  all('.modal').forEach(modal => modal.addEventListener('click', event => { if (event.target === modal) modal.classList.remove('open'); }));
  document.addEventListener('keydown', event => { if (event.key === 'Escape') { all('.modal.open').forEach(modal => modal.classList.remove('open')); closeSidebar(); } });

  all('input[inputmode="numeric"]').forEach(input => input.addEventListener('input', () => {
    input.value = input.value.replace(/\D/g, '').slice(0, Number(input.maxLength) > 0 ? Number(input.maxLength) : undefined);
  }));

  all('form').forEach(form => form.addEventListener('submit', event => {
    if (!form.checkValidity()) { event.preventDefault(); form.reportValidity(); return; }
    const button = form.querySelector('button[type="submit"],button:not([type])');
    if (button && !button.dataset.keepEnabled) { button.disabled = true; button.dataset.original = button.textContent; button.textContent = 'Please wait…'; }
  }));

  all('input[type="datetime-local"]').forEach(input => {
    if (!input.min) { const date = new Date(Date.now() - new Date().getTimezoneOffset() * 60000); input.min = date.toISOString().slice(0,16); }
  });

  all('[data-password-toggle]').forEach(button => button.addEventListener('click', () => {
    const input = document.getElementById(button.dataset.passwordToggle);
    if (!input) return;
    const showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    button.textContent = showing ? 'Show' : 'Hide';
    button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
    button.setAttribute('aria-pressed', String(!showing));
    input.focus();
  }));
})();
