document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    const dateInput = document.getElementById("date");
    const timeInput = document.getElementById("time");

    // Désactiver les dates passées
    let today = new Date().toISOString().split("T")[0];
    dateInput.setAttribute("min", today);

    // Validation du formulaire
    form.addEventListener("submit", function (e) {
        let name = document.getElementById("name").value.trim();
        let email = document.getElementById("email").value.trim();
        let phone = document.getElementById("phone").value.trim();

        if (name === "" || email === "" || phone === "") {
            e.preventDefault();
            alert("Veuillez remplir tous les champs obligatoires !");
        }
    });

    // Ajout d'une alerte après soumission
    form.addEventListener("submit", function () {
        alert("Votre rendez-vous a été réservé avec succès !");
    });
});
