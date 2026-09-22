<script data-server-time="{{ now()->getTimestamp() }}">
(() => {
    const serverTime = Number(document.currentScript.dataset.serverTime) * 1000;
    const loadedAt = performance.now();
    function removeExpiredSchedules() {
        const now = serverTime + performance.now() - loadedAt;
        document.querySelectorAll('[data-schedule-expires]').forEach(element => {
            if (Number(element.dataset.scheduleExpires) * 1000 > now) return;
            element.querySelectorAll('input').forEach(input => {
                input.checked = false;
                input.disabled = true;
            });
            element.style.display = 'none';
        });
    }
    removeExpiredSchedules();
    setInterval(removeExpiredSchedules, 1000);
    document.addEventListener('visibilitychange', removeExpiredSchedules);
})();
</script>
