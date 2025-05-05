document.addEventListener("DOMContentLoaded", function (event) {
    document.querySelectorAll('.copy-url-btn').forEach((btn) => {
        console.log(btn);
        btn.addEventListener('click', (e) => {
            navigator.clipboard.writeText(btn.getAttribute('data-url')).then(() => {

                const messageEl = btn.closest('tr').querySelector('.copy-url-success-message');
                messageEl.classList.remove('hidden');
                setTimeout(() => {
                    messageEl.classList.add('hidden');
                }, 2000);
            });
            e.preventDefault();
            return false;
        });
    })
});