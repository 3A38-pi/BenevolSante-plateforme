// Scroll to the bottom of the messages container
function scrollToBottom() {
    const messagesContainer = document.getElementById('messages');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

// Automatically scroll to the bottom when the page loads
document.addEventListener('DOMContentLoaded', scrollToBottom);

// Handle form submission
document.querySelectorAll('.message-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        const textarea = this.querySelector('textarea');
        if (textarea.value.trim() === '') {
            e.preventDefault();
            alert('Veuillez écrire un message avant d\'envoyer.');
        }
    });
});