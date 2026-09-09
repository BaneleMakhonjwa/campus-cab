/* ================= PAGE NAVIGATION ================= */

function showPage(pageId, button) {
    let pages = document.querySelectorAll(".page");
    pages.forEach(function(page) {
        page.classList.remove("active");
    });

    document.getElementById(pageId).classList.add("active");

    let buttons = document.querySelectorAll(".menu");
    buttons.forEach(function(btn) {
        btn.classList.remove("active");
    });

    button.classList.add("active");
}


/* ================= APPROVE DRIVER ================= */

function approveDriver(button) {
    let row = button.parentElement.parentElement;
    let status = row.querySelector(".status");

    status.innerHTML = "Approved";
    status.style.color = "green";

    button.style.display = "none";
    row.querySelector(".reject").style.display = "none";

    alert("Driver approved successfully!");
}


/* ================= REJECT DRIVER ================= */

function rejectDriver(button) {
    let row = button.parentElement.parentElement;
    let status = row.querySelector(".status");

    let driverName = row.querySelector("td").innerText;

    let reason = prompt(`Reason for rejecting ${driverName}'s driver application:`);

    if (!reason || !reason.trim()) {
        alert("A reason is required to reject this application.");
        return;
    }

    status.innerHTML = `
        Rejected
        <div style="font-size:12px; font-weight:normal; color:#f87171; margin-top:4px;">
            ${reason.trim()}
        </div>
    `;
    status.style.color = "red";

    button.style.display = "none";
    row.querySelector(".approve").style.display = "none";

    alert("Driver rejected.");
}


/* ================= RESOLVE COMPLAINT ================= */

/* ================= RESOLVE COMPLAINT ================= */

function resolveComplaint(button) {
    let row = button.parentElement.parentElement;
    let status = row.querySelector(".complaintStatus");

    let studentName = row.querySelector("td").innerText;

    let resolution = prompt(`How was ${studentName}'s complaint resolved?`);

    if (!resolution || !resolution.trim()) {
        alert("Please describe how the complaint was resolved.");
        return;
    }

    status.innerHTML = `
        Resolved
        <div style="font-size:12px; font-weight:normal; color:#4ade80; margin-top:4px;">
            ${resolution.trim()}
        </div>
    `;
    status.style.color = "green";

    button.innerHTML = "Resolved";
    button.disabled = true;
}


/* ================= PAY / REIMBURSE DRIVER ================= */

/* ================= PAY / REIMBURSE DRIVER ================= */

function payDriver(button) {
    let row = button.parentElement.parentElement;
    let status = row.querySelector(".compensationStatus");

    let driverName = row.querySelector("td").innerText;

    let note = prompt(`How was ${driverName}'s claim resolved? (e.g. "Paid in full", "Partial payment approved", "Denied")`);

    if (!note || !note.trim()) {
        alert("Please describe how this claim was resolved.");
        return;
    }

    status.innerHTML = `
        Resolved
        <div style="font-size:12px; font-weight:normal; color:#4ade80; margin-top:4px;">
            ${note.trim()}
        </div>
    `;
    status.style.color = "green";

    button.innerHTML = "Resolved";
    button.disabled = true;
}


/* ================= SEARCH TABLE ================= */

function searchTable(input, tableId) {
    let filter = input.value.toLowerCase();
    let table = document.getElementById(tableId);
    let rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) {
        let nameCell = rows[i].getElementsByTagName("td")[1] || rows[i].getElementsByTagName("td")[0];
        if (!nameCell) continue;

        let text = nameCell.textContent || nameCell.innerText;
        rows[i].style.display = text.toLowerCase().indexOf(filter) > -1 ? "" : "none";
    }
}
/* ================= DRIVER RATINGS SIMULATION ================= */

// Simulated individual review scores per driver (this is what a real DB table would store: one row per review)
const driverReviews = {
    "Thabo Mokoena": [5, 4, 5, 5, 3, 5, 4, 5, 5, 4, 5, 5, 4, 5],
    "Lerato Molefe":  [4, 3, 4, 5, 4, 3, 4, 4, 5, 4],
    "Sam Nkosi":      [4, 5, 3, 4, 4, 5, 4, 3, 4, 5, 4, 4, 5, 3, 4, 4, 3]
};

const driverTrips = {
    "Thabo Mokoena": 14,
    "Lerato Molefe": 32,
    "Sam Nkosi": 27
};

function calculateAverage(reviews) {
    let total = reviews.reduce((sum, r) => sum + r, 0);
    return total / reviews.length;
}

function renderStars(average) {
    let rounded = Math.round(average);
    let full = "★".repeat(rounded);
    let empty = "☆".repeat(5 - rounded);
    return full + empty;
}

function renderRatingsTable() {
    let table = document.getElementById("ratingsTable");

    // Remove all rows except the header
    while (table.rows.length > 1) {
        table.deleteRow(1);
    }

    for (let driver in driverReviews) {
        let reviews = driverReviews[driver];
        let average = calculateAverage(reviews);
        let stars = renderStars(average);
        let trips = driverTrips[driver] || 0;

        let flagged = average < systemSettings.lowRatingThreshold;

        let row = table.insertRow();

        row.innerHTML = `
            <td>${driver} ${flagged ? '<span class="claimBadge notCovered" style="margin-left:8px;">⚠ Below threshold</span>' : ""}</td>
            <td class="stars">${stars} <span class="avgNumber">(${average.toFixed(1)})</span></td>
            <td>${reviews.length}</td>
            <td>${trips}</td>
        `;
    }
}

// Run on page load
document.addEventListener("DOMContentLoaded", renderRatingsTable);
/* ================= SYSTEM SETTINGS ================= */

let systemSettings = {
    commissionRate: 0.80, // 80% default
    baseFare: 20,
    pricePerKm: 5,
    cancellationFee: 50,
    lowRatingThreshold: 3.0,
    
};
/* ================= DRIVER COMPENSATION ================= */

const driverCompensation = [
  {
    id: "COM-1001",
    driverId: "DRV-0417",
    driverName: "Nomvula Khumalo",
    frequency: "Weekly",
    period: "18 Aug – 24 Aug",
    completedRides: 32,
    totalRideValue: 3200,
    commissionRate: 0.80,
    baseCommission: 2560,
    penalties: 0,
    cancellation: 300,
    status: "Calculated"
  },
  {
    id: "COM-1002",
    driverId: "DRV-0418",
    driverName: "Sipho Mahlangu",
    frequency: "Monthly",
    period: "August 2026",
    completedRides: 120,
    totalRideValue: 12000,
    commissionRate: 0.80,
    baseCommission: 9600,
    penalties: 100,
    cancellation: 0,
    status: "Approved"
  },
  {
    id: "COM-1003",
    driverId: "DRV-0419",
    driverName: "Aisha Patel",
    frequency: "Weekly",
    period: "18 Aug – 24 Aug",
    completedRides: 27,
    totalRideValue: 2700,
    commissionRate: 0.80,
    baseCommission: 2160,
    penalties: 100,
    cancellation: 0,
    status: "Calculated"
  }
];

function currencyZAR(n) {
    return new Intl.NumberFormat("en-ZA", {
        style: "currency",
        currency: "ZAR",
        maximumFractionDigits: 0
    }).format(n);
}

function calculatedCommission(c) {
    return c.totalRideValue * systemSettings.commissionRate;
}

function finalPayment(c) {
    return calculatedCommission(c) - c.penalties + c.cancellation;
}

function renderCompensationKpis() {
    const totalPayments = driverCompensation.length;
    const totalRides = driverCompensation.reduce((s, c) => s + c.completedRides, 0);
    const totalCommission = driverCompensation.reduce((s, c) => s + finalPayment(c), 0);

    document.getElementById("compensationKpis").innerHTML = `
        <div class="card">
            <h3>Total Payments</h3>
            <p>${totalPayments}</p>
        </div>
        <div class="card">
            <h3>Completed Rides</h3>
            <p>${totalRides}</p>
        </div>
        <div class="card">
            <h3>Total Commission</h3>
            <p>${currencyZAR(totalCommission)}</p>
        </div>
    `;
}

function renderCompensationList() {
    const container = document.getElementById("compensationList");

    if (driverCompensation.length === 0) {
        container.innerHTML = `<p>No commission records yet.</p>`;
        return;
    }

    container.innerHTML = driverCompensation.map(c => {

        let statusColor = "#9ca3af";
        if (c.status === "Calculated") statusColor = "#f59e0b";
        if (c.status === "Approved") statusColor = "#2563eb";
        if (c.status === "Paid") statusColor = "#16a34a";
        if (c.status === "Rejected") statusColor = "#dc2626";

        let actionsHtml = "";
        if (c.status === "Calculated") {
            actionsHtml = `
                <button class="approve" onclick="approveCompensation('${c.id}')">Approve</button>
                <button class="reject" onclick="rejectCompensation('${c.id}')">Reject</button>
            `;
        } else if (c.status === "Approved") {
            actionsHtml = `
                <button class="green" onclick="payCompensation('${c.id}')">Mark as Paid</button>
            `;
        }

        return `
            <div class="section" style="margin-bottom:15px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div>
                        <strong>${c.driverName}</strong> (${c.driverId})<br>
                        <span style="color:#9ca3af; font-size:13px;">${c.frequency} · ${c.period}</span>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:20px; font-weight:bold;">${currencyZAR(finalPayment(c))}</div>
                        <span style="color:${statusColor}; font-weight:bold;">${c.status}</span>
                    </div>
                </div>

                <div class="compStats">
                    <div class="compStatItem">
                        <span class="compStatLabel">Completed rides</span>
                        <span class="compStatValue">${c.completedRides}</span>
                    </div>
                    <div class="compStatItem">
                        <span class="compStatLabel">Total fare</span>
                        <span class="compStatValue">${currencyZAR(c.totalRideValue)}</span>
                    </div>
                    <div class="compStatItem">
                    <span class="compStatLabel">Commission</span>
<span class="compStatValue">${Math.round(systemSettings.commissionRate * 100)}%</span>
                    </div>
                </div>

                <div class="compCalcBox">
                    <div class="compCalcTitle">Calculation</div>
                   <div class="compCalcLine">
    ${currencyZAR(c.totalRideValue)} × ${Math.round(systemSettings.commissionRate * 100)}%
    = <strong>${currencyZAR(calculatedCommission(c))}</strong>
</div>
                    ${c.penalties > 0 ? `<div class="compCalcLine compCalcMinus">− Penalty: ${currencyZAR(c.penalties)}</div>` : ""}
                    ${c.cancellation > 0 ? `<div class="compCalcLine compCalcPlus">+ Cancellation: ${currencyZAR(c.cancellation)}</div>` : ""}
                    <div class="compCalcFinal">Final payment: ${currencyZAR(finalPayment(c))}</div>
                </div>

                ${c.rejectionReason ? `
                    <p style="margin-top:8px; padding:10px; background:rgba(220,38,38,0.15); border-left:3px solid #dc2626; border-radius:4px;">
                        <strong>Rejection reason:</strong> ${c.rejectionReason}
                    </p>
                ` : ""}

                <div style="margin-top:12px;">
                    ${actionsHtml}
                </div>
            </div>
        `;
    }).join("");
}

function renderCompensationPage() {
    renderCompensationKpis();
    renderCompensationList();
}

function approveCompensation(id) {
    const c = driverCompensation.find(x => x.id === id);
    if (!c) return;
    c.status = "Approved";
    alert(`${c.driverName}'s commission payment was approved.`);
    renderCompensationPage();
}

function rejectCompensation(id) {
    const c = driverCompensation.find(x => x.id === id);
    if (!c) return;

    const reason = prompt(`Reason for rejecting ${c.driverName}'s commission payment:`);

    if (!reason || !reason.trim()) {
        alert("A reason is required to reject this payment.");
        return;
    }

    c.status = "Rejected";
    c.rejectionReason = reason.trim();

    alert(`${c.driverName}'s commission payment was rejected.`);
    renderCompensationPage();
}

function payCompensation(id) {
    const c = driverCompensation.find(x => x.id === id);
    if (!c) return;
    c.status = "Paid";
    alert(`${c.driverName}'s payment has been marked as paid.`);
    renderCompensationPage();
}
function renderSettingsPage() {
    const input = document.getElementById("commissionRateInput");
    input.value = Math.round(systemSettings.commissionRate * 100);
}
/* ================= DASHBOARD ALERTS ================= */

function countPendingDrivers() {
    let table = document.getElementById("driverTable");
    if (!table) return 0;
    let cells = table.querySelectorAll(".status");
    let count = 0;
    cells.forEach(c => {
        if (c.innerText.trim().startsWith("Pending")) count++;
    });
    return count;
}

function countOpenComplaints() {
    let table = document.getElementById("complaintsTable");
    if (!table) return 0;
    let cells = table.querySelectorAll(".complaintStatus");
    let count = 0;
    cells.forEach(c => {
        let text = c.innerText.trim();
        if (text.startsWith("Pending") || text.startsWith("Investigating")) count++;
    });
    return count;
}

function countPendingReimbursement() {
    let table = document.getElementById("reimbursementTable");
    if (!table) return 0;
    let cells = table.querySelectorAll(".compensationStatus");
    let count = 0;
    cells.forEach(c => {
        if (c.innerText.trim().startsWith("Pending")) count++;
    });
    return count;
}

function countPendingCommission() {
    return driverCompensation.filter(c => c.status === "Calculated").length;
}

function renderAlertsPanel() {
    const alerts = [
        {
            label: "Open complaints",
            count: countOpenComplaints(),
            nav: "complaints",
            icon: "📢",
            weight: 4 // highest priority — service/safety issues
        },
        {
            label: "Pending driver approvals",
            count: countPendingDrivers(),
            nav: "drivers",
            icon: "🚗",
            weight: 3
        },
        {
            label: "Pending reimbursements",
            count: countPendingReimbursement(),
            nav: "reimbursement",
            icon: "💵",
            weight: 2
        },
        {
            label: "Commission payments to review",
            count: countPendingCommission(),
            nav: "driverCompensation",
            icon: "💰",
            weight: 1
        }
    ];

    // Only show categories with something pending, ranked by weight (urgency) then count
    const active = alerts
        .filter(a => a.count > 0)
        .sort((a, b) => b.weight - a.weight || b.count - a.count);

    const panel = document.getElementById("alertsPanel");

    if (active.length === 0) {
        panel.innerHTML = `
            <div class="compCalcBox" style="border-left-color:#16a34a;">
                ✅ All caught up — nothing urgent right now.
            </div>
        `;
        return;
    }

    panel.innerHTML = active.map((a, i) => {
        let urgencyColor = "#f59e0b";
        if (i === 0) urgencyColor = "#dc2626";
        else if (i === 1) urgencyColor = "#ea580c";

        return `
            <div class="compCalcBox"
                 style="border-left-color:${urgencyColor}; cursor:pointer; margin-bottom:10px;"
                 onclick="showPage('${a.nav}', document.querySelector('[onclick*=\\'${a.nav}\\']'))">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span>${a.icon} ${a.label}</span>
                    <span style="font-weight:bold; color:${urgencyColor};">${a.count}</span>
                </div>
            </div>
        `;
    }).join("");
}

// Run on page load, and whenever we return to the dashboard
document.addEventListener("DOMContentLoaded", renderAlertsPanel);

function saveCommissionRate() {
    const input = document.getElementById("commissionRateInput");
    const value = parseFloat(input.value);

    if (isNaN(value) || value < 0 || value > 100) {
        alert("Please enter a valid percentage between 0 and 100.");
        return;
    }

    systemSettings.commissionRate = value / 100;

    const msg = document.getElementById("settingsSavedMsg");
    msg.style.display = "block";
    setTimeout(() => { msg.style.display = "none"; }, 2500);

    // Refresh the compensation tab so it reflects the new rate immediately
    renderCompensationPage();
}

document.addEventListener("DOMContentLoaded", renderSettingsPage);

// Run once on page load so the tab has content ready when clicked
document.addEventListener("DOMContentLoaded", renderCompensationPage);


/* ================= FARE SETTINGS ================= */

function calculateFare(km) {
    return systemSettings.baseFare + (systemSettings.pricePerKm * km);
}

function renderFareSettings() {
    document.getElementById("baseFareInput").value = systemSettings.baseFare;
    document.getElementById("pricePerKmInput").value = systemSettings.pricePerKm;
    updateFarePreview();
}

function updateFarePreview() {
    const exampleKm = 8;
    const fare = calculateFare(exampleKm);

    document.getElementById("farePreview").innerHTML = `
        Base fare: ${currencyZAR(systemSettings.baseFare)}<br>
        + ${exampleKm}km × ${currencyZAR(systemSettings.pricePerKm)}/km
        = ${currencyZAR(systemSettings.pricePerKm * exampleKm)}<br>
        <strong>Total fare: ${currencyZAR(fare)}</strong>
    `;
}

function saveFareSettings() {
    const base = parseFloat(document.getElementById("baseFareInput").value);
    const perKm = parseFloat(document.getElementById("pricePerKmInput").value);

    if (isNaN(base) || base < 0 || isNaN(perKm) || perKm < 0) {
        alert("Please enter valid, non-negative numbers for fare settings.");
        return;
    }

    systemSettings.baseFare = base;
    systemSettings.pricePerKm = perKm;

    updateFarePreview();

    const msg = document.getElementById("fareSettingsSavedMsg");
    msg.style.display = "block";
    setTimeout(() => { msg.style.display = "none"; }, 2500);
}

document.addEventListener("DOMContentLoaded", renderFareSettings);


/* ================= CANCELLATION FEE ================= */

function renderCancellationFee() {
    document.getElementById("cancellationFeeInput").value = systemSettings.cancellationFee;
    updateCancellationAmounts();
}

function updateCancellationAmounts() {
    document.querySelectorAll(".cancellationAmount").forEach(cell => {
        cell.innerText = "R" + systemSettings.cancellationFee;
    });
}

function saveCancellationFee() {
    const value = parseFloat(document.getElementById("cancellationFeeInput").value);

    if (isNaN(value) || value < 0) {
        alert("Please enter a valid, non-negative cancellation fee.");
        return;
    }

    systemSettings.cancellationFee = value;
    updateCancellationAmounts();

    const msg = document.getElementById("cancellationFeeSavedMsg");
    msg.style.display = "block";
    setTimeout(() => { msg.style.display = "none"; }, 2500);
}

document.addEventListener("DOMContentLoaded", renderCancellationFee);


/* ================= LOW RATING THRESHOLD ================= */

function renderLowRatingThreshold() {
    document.getElementById("lowRatingInput").value = systemSettings.lowRatingThreshold;
}

function saveLowRatingThreshold() {
    const value = parseFloat(document.getElementById("lowRatingInput").value);

    if (isNaN(value) || value < 0 || value > 5) {
        alert("Please enter a valid rating threshold between 0 and 5.");
        return;
    }

    systemSettings.lowRatingThreshold = value;

    const msg = document.getElementById("lowRatingSavedMsg");
    msg.style.display = "block";
    setTimeout(() => { msg.style.display = "none"; }, 2500);

    // Refresh the ratings tab so flags update immediately
    renderRatingsTable();
}

document.addEventListener("DOMContentLoaded", renderLowRatingThreshold);