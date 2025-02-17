 // Filtre de recherche dans le tableau
 document.getElementById("search").addEventListener("input", function () {
    let searchValue = this.value.toLowerCase();
    let rows = document.querySelectorAll("#table-body tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(searchValue) ? "" : "none";
    });
});

// Suppression d'un article sans confirmation
document.querySelectorAll('.delete-article').forEach(button => {
    button.addEventListener('click', function (event) {
        event.preventDefault();
        let articleId = this.getAttribute('data-id');

        fetch(`/article/supprimer/${articleId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.closest('tr').remove();
            } else {
                console.error("Erreur: " + data.message);
            }
        })
        .catch(error => console.error("Erreur:", error));
    });
});

