const dinnerCheckbox = document.getElementById("dinner");
const menuSelect = document.getElementById("idMenu");

function updateDinnerMenu() {

    menuSelect.disabled = !dinnerCheckbox.checked;

    if (!dinnerCheckbox.checked) {

            menuSelect.value = "";

    }

}

dinnerCheckbox.addEventListener("change", updateDinnerMenu);
updateDinnerMenu();

const breakfastCheckbox = document.getElementById("breakfast");
const bookingTotal = document.getElementById("booking-total");
const baseTotal = Number(bookingTotal.dataset.baseTotal);
const guests = Number(bookingTotal.dataset.guests);
const nights = Number(bookingTotal.dataset.nights);

function updateBookingTotal() {

    const supplement = 8 * guests * nights;
    let total = baseTotal;

    if(breakfastCheckbox.checked) {

        total = total + supplement;

    }

    bookingTotal.textContent = "Totale soggiorno: " +
     total.toLocaleString("it-IT", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
        }) + " €";
}

breakfastCheckbox.addEventListener("change", updateBookingTotal);
updateBookingTotal();