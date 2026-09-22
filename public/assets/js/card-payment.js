(() => {
    const form = document.querySelector('[data-card-payment]');
    if (!form) return;
    const number = form.elements.card_number;
    const expiry = form.elements.expiry;
    const cvv = form.elements.cvv;

    function format(input, max, formatter) {
        const position = input.selectionStart ?? input.value.length;
        const digitsBefore = input.value.slice(0, position).replace(/\D/g, '').length;
        input.value = formatter(input.value.replace(/\D/g, '').slice(0, max));
        let cursor = 0, count = 0;
        while (cursor < input.value.length && count < digitsBefore) {
            if (/\d/.test(input.value[cursor])) count++;
            cursor++;
        }
        input.setSelectionRange(cursor, cursor);
        input.setCustomValidity('');
    }
    function validate() {
        const digits = number.value.replace(/\s/g, '');
        number.setCustomValidity(digits && !/^[0-9]{16}$/.test(digits) ? 'Enter a 16-digit card number.' : '');
        const match = /^(0[1-9]|1[0-2])\/(\d{2})$/.exec(expiry.value);
        const now = new Date();
        const valid = match && (2000 + Number(match[2])) * 12 + Number(match[1]) >= now.getFullYear() * 12 + now.getMonth() + 1;
        expiry.setCustomValidity(expiry.value && !valid ? 'Enter a current or future expiry in MM/YY format.' : '');
        cvv.setCustomValidity(cvv.value && !/^\d{3,4}$/.test(cvv.value) ? 'Enter the 3 or 4 digit security code.' : '');
    }
    number.addEventListener('input', () => format(number, 16, digits => digits.match(/.{1,4}/g)?.join(' ') || ''));
    expiry.addEventListener('input', () => format(expiry, 4, digits => digits.length > 2 ? digits.slice(0, 2) + '/' + digits.slice(2) : digits));
    cvv.addEventListener('input', () => format(cvv, 4, digits => digits));
    [number, expiry, cvv].forEach(input => input.addEventListener('blur', validate));
    form.addEventListener('submit', event => {
        validate();
        if (!form.reportValidity()) event.preventDefault();
    });
})();
