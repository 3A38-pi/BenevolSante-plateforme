// public/js/commentaire.js
document.addEventListener("DOMContentLoaded", function() {
    let editPopup = document.getElementById("editCommentPopup");
    let textarea = document.getElementById("editCommentContent");
    let confirmBtn = document.getElementById("confirmEdit");
    let cancelBtn = document.getElementById("cancelEdit");
    let currentCommentId = null;

    document.querySelectorAll(".edit-comment").forEach(button => {
        button.addEventListener("click", function(event) {
            event.preventDefault();
            currentCommentId = this.getAttribute("data-id");
            textarea.value = this.getAttribute("data-content");
            editPopup.style.display = "flex";
        });
    });

    cancelBtn.addEventListener("click", function() {
        editPopup.style.display = "none";
    });

    confirmBtn.addEventListener("click", function() {
        let newContent = textarea.value;
        
        fetch(`/commentaire/modifier/${currentCommentId}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ content: newContent })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`comment-text-${currentCommentId}`).innerText = newContent;
                editPopup.style.display = "none";
            } else {
                alert("Erreur : " + data.message);
            }
        })
        .catch(error => console.error("Erreur :", error));
    });

    // Suppression immédiate sans confirmation
    document.querySelectorAll(".delete-comment").forEach(button => {
        button.addEventListener("click", function(event) {
            event.preventDefault();
            let commentId = this.getAttribute("data-id");

            fetch(`/commentaire/supprimer/${commentId}`, {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json"
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`comment-${commentId}`).remove();
                } else {
                    alert("Erreur : " + data.message);
                }
            })
            .catch(error => console.error("Erreur :", error));
        });
    });
});
