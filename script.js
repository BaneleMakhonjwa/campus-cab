
/* ================= MOBILE NAVIGATION ================= */

const menuToggle = document.getElementById("menuToggle");
const navMenu = document.getElementById("navMenu");

menuToggle.addEventListener("click", function () {

    navMenu.classList.toggle("active");

});

/* Close mobile menu when a link is clicked */

const navLinks = document.querySelectorAll("#navMenu a");

navLinks.forEach(function (link) {

    link.addEventListener("click", function () {

        navMenu.classList.remove("active");

    });

});


/* ================= REQUEST MODAL ================= */


function openRegisterModal() {
    const modal = document.getElementById("registerModal");
    modal.classList.add("active");
}

function handleRegister() {
    const role = document.getElementById("registerType").value;
    if (role === "student") {
        window.location.href = "student-register.html";
    } else if (role === "driver") {
        window.location.href = "driver-register.html";
    } else if (role === "staff") {
        window.location.href = "staff-register.html";
    }
}

function showRequestMessage(type) {

    const modal = document.getElementById("requestModal");

    const modalTitle = document.getElementById("modalTitle");

    const modalText = document.getElementById("modalText");

    const modalIcon = document.querySelector(".modal-icon");


    if (type === "transport") {

        modalIcon.textContent = "🚗";

        modalTitle.textContent = "Request Transport";

        modalText.textContent =
            "Please login to your CampusCab account to request transportation.";

    }

    if (type === "parcel") {

        modalIcon.textContent = "📦";

        modalTitle.textContent = "Request Parcel Service";

        modalText.textContent =
            "Please login to your CampusCab account to request parcel collection or transportation.";

    }

    modal.classList.add("active");

}
/* ================= CLOSE MODAL ================= */

function closeModal() {

    const modal = document.getElementById("requestModal");

    modal.classList.remove("active");

}

function closeModal() {
    document.querySelectorAll(".modal.active").forEach(modal => {
        modal.classList.remove("active");
    });
}

/* Close modal when clicking outside */

window.addEventListener("click", function (event) {

    const modal = document.getElementById("requestModal");

    if (event.target === modal) {

        closeModal();

    }

});

/* ================= LOGIN ================= */

function goToLogin() {

    alert(
        "The CampusCab login page will be available here when the login system is connected."
    );

}
/* ================= TRACK DRIVER ================= */

function trackDriver() {

    alert(
        "Live driver tracking will appear here when the tracking system is connected."
    );

}
/* ================= SUPPORT ================= */

function contactSupport() {

    alert(
        "CampusCab Support\n\nPlease contact the university transport office for assistance with your request."
    );
}

// Attach close button click
document.querySelectorAll(".close-modal").forEach(button => {
    button.addEventListener("click", () => {
        const modal = button.closest(".modal"); // find the parent modal
        modal.classList.remove("active");
    });
});
