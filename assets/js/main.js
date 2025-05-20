/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 20/05/2025
 * Time: 22:59
 */
document.addEventListener("DOMContentLoaded", function (event) {
    document.querySelectorAll('.copy-url-btn').forEach((btn) => {
        console.log(btn);
        btn.addEventListener('click', (e) => {
            navigator.clipboard.writeText(btn.getAttribute('data-url')).then(() => {

                const messageEl = btn.parentNode.querySelector('.copy-url-success-message');
                messageEl.classList.remove('hidden');
                btn.classList.add('hidden');
                setTimeout(() => {
                    messageEl.classList.add('hidden');
                    btn.classList.remove('hidden');
                }, 2000);
            });
            e.preventDefault();
            return false;
        });
    })
});