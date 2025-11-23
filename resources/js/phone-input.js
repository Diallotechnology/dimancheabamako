import intlTelInput from "intl-tel-input";
import "intl-tel-input/build/css/intlTelInput.css";

document.addEventListener("DOMContentLoaded", () => {
    const input = document.querySelector("#contact");
    if (!input) return;

    const form = input.closest("form");

    const iti = intlTelInput(input, {
        initialCountry: "ml",
        preferredCountries: ["ml", "fr", "ci", "sn"],
        separateDialCode: true,
        nationalMode: false,
        utilsScript:
            "https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/utils.js",
    });

    form.addEventListener("submit", (event) => {
        if (!iti.isValidNumber()) {
            alert("Numéro invalide ou utils non chargé.");
            event.preventDefault();
            return;
        }

        const fullNumber = iti.getNumber(intlTelInputUtils.numberFormat.E164);
        console.log("Numéro envoyé :", fullNumber);
        if (!fullNumber) {
            alert("Impossible de lire le numéro.");
            event.preventDefault();
            return;
        }

        input.value = fullNumber;
        console.log("Numéro envoyé :", fullNumber);
    });
});
