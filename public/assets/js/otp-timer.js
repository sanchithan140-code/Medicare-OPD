document.addEventListener('DOMContentLoaded', () => {
    const timer = document.querySelector('[data-otp-timer]');
    const resend = document.querySelector('[data-otp-resend]');

    if (!timer || !resend) return;

    const expiresAt = Number(timer.dataset.expiresAt) * 1000;
    const lockedUntil = Number(timer.dataset.lockedUntil || 0) * 1000;
    const value = timer.querySelector('strong');

    const update = () => {
        const locked = lockedUntil > Date.now();
        const remaining = Math.max(0, Math.ceil(((locked ? lockedUntil : expiresAt) - Date.now()) / 1000));
        resend.disabled = locked || remaining > 0;
        timer.firstChild.textContent = locked ? 'Try again in: ' : 'Time remaining: ';
        const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
        const seconds = String(remaining % 60).padStart(2, '0');
        value.textContent = `${minutes}:${seconds}`;

        if (remaining === 0) {
            resend.disabled = false;
            timer.firstChild.textContent = 'OTP expired. ';
            return true;
        }

        return false;
    };

    if (!update()) {
        const interval = window.setInterval(() => {
            if (update()) window.clearInterval(interval);
        }, 1000);
    }
});
