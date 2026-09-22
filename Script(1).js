/* ================= DRIVER RATINGS (LIVE FROM DATABASE) ================= */

function renderStars(average) {
    let rounded = Math.round(average);
    let full = "★".repeat(rounded);
    let empty = "☆".repeat(5 - rounded);
    return full + empty;
}

function renderRatingsTable(ratingsData) {
    let table = document.getElementById("ratingsTable");
    if (!table || !ratingsData) return;

    while (table.rows.length > 1) {
        table.deleteRow(1);
    }

    ratingsData.forEach(function(d) {
        let average = parseFloat(d.average_rating);
        let stars = renderStars(average);
        let flagged = average < systemSettings.lowRatingThreshold;
        let fullName = d.DriverFname + " " + d.DriverLname;

        let row = table.insertRow();

        row.innerHTML = `
            <td>${fullName} ${flagged ? '<span class="claimBadge notCovered" style="margin-left:8px;">⚠ Below threshold</span>' : ""}</td>
            <td class="stars">${stars} <span class="avgNumber">(${average.toFixed(1)})</span></td>
            <td>${d.review_count}</td>
            <td>${d.trip_count}</td>
        `;
    });
}
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

/* ================= PENDING DRIVERS (LIVE FROM DATABASE) ================= */

function renderPendingDriversTable(pendingDrivers) {
    const tableBody = document.getElementById("driverTableBody");

    if (!tableBody) {
        console.error("driverTableBody was not found.");
        return;
    }

    // Clear existing rows
    tableBody.innerHTML = "";

    if (!pendingDrivers || pendingDrivers.length === 0) {
        const row = tableBody.insertRow();

        row.innerHTML = `
            <td colspan="5">No pending driver applications.</td>
        `;

        return;
    }

    pendingDrivers.forEach(function(d) {
        const row = tableBody.insertRow();

        row.setAttribute("data-driver-id", d.DriverID);

        row.innerHTML = `
            <td>${d.DriverFname} ${d.DriverLname}</td>
            <td>${d.DriverEmail}</td>
            <td>${d.licence_number}</td>
            <td class="status">${d.driverAccount_Status}</td>
            <td>
                <button class="approve" onclick="approveDriverReal(${d.DriverID})">
                    Approve
                </button>

                <button class="reject" onclick="rejectDriverReal(${d.DriverID})">
                    Reject
                </button>
            </td>
        `;
    });
}

function approveDriverReal(driverId) {
    fetch('admin_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'approve_driver', driverId: driverId, adminName: 'Admin' })
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert("Driver approved successfully!");
            loadDashboardData();
        } else {
            alert("Error: " + (result.error || "Could not approve driver."));
        }
    });
}

function rejectDriverReal(driverId) {
    let reason = prompt("Reason for rejecting this driver's application:");

    if (!reason || !reason.trim()) {
        alert("A reason is required to reject this application.");
        return;
    }

    fetch('admin_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'reject_driver', driverId: driverId, reason: reason.trim(), adminName: 'Admin' })
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert("Driver rejected.");
            loadDashboardData();
        } else {
            alert("Error: " + (result.error || "Could not reject driver."));
        }
    });
}


//* ================= COMPLAINTS (LIVE FROM DATABASE) ================= */

function renderComplaintsTable(complaints) {
    const tableBody = document.getElementById("complaintsTableBody");
    if (!tableBody) return;

    tableBody.innerHTML = "";

    if (!complaints || complaints.length === 0) {
        let row = tableBody.insertRow();
        row.innerHTML = `<td colspan="7">No complaints on record.</td>`;
        return;
    }

    complaints.forEach(function(c) {
        let studentName = (c.studentFname && c.studentLname) ? `${c.studentFname} ${c.studentLname}` : "—";
        let driverName = (c.DriverFname && c.DriverLname) ? `${c.DriverFname} ${c.DriverLname}` : "—";
        let statusColor = c.status === "Resolved" ? "green" : (c.status === "Dismissed" ? "#9ca3af" : "orange");

        let actionHtml = (c.status === "New" || c.status === "Investigating")
            ? `<button class="blue" onclick="resolveComplaintReal(${c.complaint_id})">Resolve</button>`
            : "";

        let row = tableBody.insertRow();
        row.innerHTML = `
            <td>${studentName}</td>
            <td>${driverName}</td>
            <td>${c.category || "—"}</td>
            <td>${c.description}</td>
            <td>${c.filed_at}</td>
            <td class="complaintStatus" style="color:${statusColor};">
                ${c.status}
                ${c.resolution ? `<div style="font-size:12px; font-weight:normal; color:#4ade80; margin-top:4px;">${c.resolution}</div>` : ""}
            </td>
            <td>${actionHtml}</td>
        `;
    });
}

function resolveComplaintReal(complaintId) {
    let resolution = prompt("How was this complaint resolved?");

    if (!resolution || !resolution.trim()) {
        alert("Please describe how the complaint was resolved.");
        return;
    }

    fetch('admin_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'resolve_complaint', complaintId: complaintId, resolution: resolution.trim(), adminName: 'Admin' })
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert("Complaint marked as resolved.");
            loadDashboardData();
        } else {
            alert("Error: " + (result.error || "Could not resolve complaint."));
        }
    });
}


/* ================= DRIVER CLAIMS / REIMBURSEMENT (LIVE FROM DATABASE) ================= */

function renderReimbursementTable(claims) {
    const tableBody = document.getElementById("reimbursementTableBody");
    if (!tableBody) return;

    tableBody.innerHTML = "";

    if (!claims || claims.length === 0) {
        let row = tableBody.insertRow();
        row.innerHTML = `<td colspan="6">No reimbursement claims on record.</td>`;
        return;
    }

    claims.forEach(function(c) {
        let driverName = (c.DriverFname && c.DriverLname) ? `${c.DriverFname} ${c.DriverLname}` : "—";

        let badgeClass = "discretionary";
        if (c.claim_category === "Covered") badgeClass = "covered";
        if (c.claim_category === "Not Covered") badgeClass = "notCovered";

        let statusColor = c.status === "Resolved" ? "green" : "orange";

        let actionHtml = c.status === "Pending"
            ? `<button class="green" onclick="resolveClaimReal(${c.claim_id})">Resolve</button>`
            : "";

        let row = tableBody.insertRow();
        row.innerHTML = `
            <td>${driverName}</td>
            <td>${c.claim_type}</td>
            <td><span class="claimBadge ${badgeClass}">${c.claim_category}</span></td>
            <td>R${parseFloat(c.amount).toFixed(0)}</td>
            <td class="compensationStatus" style="color:${statusColor};">
                ${c.status}
                ${c.resolution_note ? `<div style="font-size:12px; font-weight:normal; color:#4ade80; margin-top:4px;">${c.resolution_note}</div>` : ""}
            </td>
            <td>${actionHtml}</td>
        `;
    });
}

function resolveClaimReal(claimId) {
    let note = prompt('How was this claim resolved? (e.g. "Paid in full", "Partial payment approved", "Denied")');

    if (!note || !note.trim()) {
        alert("Please describe how this claim was resolved.");
        return;
    }

    fetch('admin_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'resolve_claim', claimId: claimId, note: note.trim(), adminName: 'Admin' })
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert("Claim marked as resolved.");
            loadDashboardData();
        } else {
            alert("Error: " + (result.error || "Could not resolve claim."));
        }
    });
}


/* ================= SEARCH TABLE ================= */



function searchTable(input, tableId) {
    let filter = input.value.toLowerCase();
    let table = document.getElementById(tableId);
    let rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) {
        let cells = rows[i].getElementsByTagName("td");
        if (cells.length === 0) continue;

        let rowText = "";
        for (let j = 0; j < cells.length; j++) {
            rowText += (cells[j].textContent || cells[j].innerText) + " ";
        }

        rows[i].style.display = rowText.toLowerCase().indexOf(filter) > -1 ? "" : "none";
    }
}
/* ================= LOAD ALL DASHBOARD DATA FROM SERVER ================= */

let dashboardData = null;

function loadDashboardData() {
    fetch('get_dashboard_data.php')
        .then(async response => {
            const rawText = await response.text();

            console.log("HTTP status:", response.status);
            console.log("Server response:", rawText);

            if (!response.ok) {
                throw new Error(`Server returned HTTP ${response.status}`);
            }

            try {
                return JSON.parse(rawText);
            } catch (e) {
                throw new Error("Server did not return valid JSON: " + rawText);
            }
        })
        .then(data => {
            console.log("Dashboard data:", data);

            dashboardData = data;

            // Apply real system settings
            systemSettings.commissionRate = parseFloat(data.systemSettings.commissionRate);
            systemSettings.baseFare = parseFloat(data.systemSettings.baseFare);
            systemSettings.pricePerKm = parseFloat(data.systemSettings.pricePerKm);
            systemSettings.cancellationFee = parseFloat(data.systemSettings.cancellationFee);
            systemSettings.lowRatingThreshold = parseFloat(data.systemSettings.lowRatingThreshold);
            systemSettings.platformName = data.systemSettings.platformName;
            systemSettings.supportEmail = data.systemSettings.supportEmail;
            systemSettings.currencySymbol = data.systemSettings.currencySymbol;

            // Dashboard cards
            document.getElementById("studentCount").innerText = data.counts.activeStudents;
            document.getElementById("staffCount").innerText = data.counts.activeStaff;
            document.getElementById("driverCount").innerText = data.counts.activeDrivers;
            document.getElementById("cancelledRidesCount").innerText = data.counts.cancelledRides;
            document.getElementById("activeUsersCount").innerText =
                data.counts.activeStudents + data.counts.activeStaff;

            // Tables
            renderRatingsTable(data.driverRatings);
            renderPendingDriversTable(data.pendingDrivers);
            renderDriverRosterTable(data.driverRoster);

            renderAlertsPanel();            // Render driver roster (Drivers tab)
            renderDriverRosterTable(data.driverRoster);
            /* ================= STUDENTS (LIVE FROM DATABASE) ================= */

function renderStudentsTable(students) {
    let tableBody = document.getElementById("studentTableBody");
    if (!tableBody) return;

    tableBody.innerHTML = "";

    students.forEach(function(s) {
        let row = tableBody.insertRow();
        row.innerHTML = `
            <td>${s.studentNumber || "—"}</td>
            <td>${s.studentFname} ${s.studentLname}</td>
            <td>${s.studentEmail}</td>
            <td>${s.studentAccount_status}</td>
        `;
    });
}

/* ================= STAFF (LIVE FROM DATABASE) ================= */

function renderStaffTable(staffList) {
    let tableBody = document.getElementById("staffTableBody");
    if (!tableBody) return;

    tableBody.innerHTML = "";

    staffList.forEach(function(s) {
        let row = tableBody.insertRow();
        row.innerHTML = `
            <td>STA${String(s.staffID).padStart(3, "0")}</td>
            <td>${s.staffFname} ${s.staffLname}</td>
            <td>${s.staffEmail}</td>
            <td>${s.staffAccount_status}</td>
        `;
    });
}

            // Render complaints
                       // Render complaints
            renderComplaintsTable(data.complaints);

                       // Render reimbursement claims
            renderReimbursementTable(data.driverClaims);

                       // Render students and staff
            renderStudentsTable(data.students);
            renderStaffTable(data.staff);

            // Render driver compensation (Trip Earnings tab)
            driverCompensation = transformCompensationData(data.driverCompensation);
            renderCompensationPage();

            if (document.getElementById("commissionRateInput")) {
                renderSettingsPage();
                renderFareSettings();
                renderCancellationFee();
                renderLowRatingThreshold();
            }
        })
        .catch(err => {
            console.error("FAILED:", err);
            alert("Dashboard error: " + err.message);
        });
}


/* ================= SYSTEM SETTINGS ================= */

let systemSettings = {
    commissionRate: 0.80, // 80% default
    baseFare: 20,
    pricePerKm: 5,
    cancellationFee: 50,
    lowRatingThreshold: 3.0,
    
};
/* ================= DRIVER COMPENSATION (LIVE FROM DATABASE) ================= */

let driverCompensation = [];

function transformCompensationData(rawData) {
    return rawData.map(function(d) {
        return {
            id: "DRV-" + d.DriverID,
            driverId: "DRV-" + d.DriverID,
            driverName: d.DriverFname + " " + d.DriverLname,
            frequency: "All time",
            period: "Total to date",
            completedRides: parseInt(d.completed_rides),
            totalRideValue: parseFloat(d.total_ride_value) || 0,
            totalDriverEarning: parseFloat(d.total_driver_earning) || 0,
            totalCommission: parseFloat(d.total_commission) || 0
        };
    });
}

function currencyZAR(n) {
    return new Intl.NumberFormat("en-ZA", {
        style: "currency",
        currency: "ZAR",
        maximumFractionDigits: 0
    }).format(n);
}

function calculatedCommission(c) {
    return c.totalCommission;
}

function finalPayment(c) {
    return c.totalDriverEarning;
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

               let statusColor = "#16a34a";

        return `
            <div class="section" style="margin-bottom:15px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div>
                        <strong>${c.driverName}</strong> (${c.driverId})<br>
                        <span style="color:#9ca3af; font-size:13px;">${c.frequency} · ${c.period}</span>
                    </div>
                                       <div style="text-align:right;">
                        <div style="font-size:20px; font-weight:bold;">${currencyZAR(finalPayment(c))}</div>
                        <span style="color:${statusColor}; font-weight:bold;">Live</span>
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
                    <div class="compCalcTitle">Earnings Breakdown</div>
                    <div class="compCalcLine">
                        Total ride value: <strong>${currencyZAR(c.totalRideValue)}</strong>
                    </div>
                    <div class="compCalcLine">
                        Platform commission: <strong>${currencyZAR(c.totalCommission)}</strong>
                    </div>
                    <div class="compCalcFinal">Driver earnings: ${currencyZAR(finalPayment(c))}</div>
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
    return dashboardData && dashboardData.pendingDrivers ? dashboardData.pendingDrivers.length : 0;
}

function countOpenComplaints() {
    if (!dashboardData || !dashboardData.complaints) return 0;
    return dashboardData.complaints.filter(c => c.status === "New" || c.status === "Investigating").length;
}

function countPendingReimbursement() {
    if (!dashboardData || !dashboardData.driverClaims) return 0;
    return dashboardData.driverClaims.filter(c => c.status === "Pending").length;
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


function saveSettingToServer(key, value) {
    return fetch('admin_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'save_setting', key: key, value: value, adminName: 'Admin' })
    }).then(res => res.json());
}

function saveCommissionRate() {
    const input = document.getElementById("commissionRateInput");
    const value = parseFloat(input.value);

    if (isNaN(value) || value < 0 || value > 100) {
        alert("Please enter a valid percentage between 0 and 100.");
        return;
    }

    systemSettings.commissionRate = value / 100;

    saveSettingToServer('commissionRate', systemSettings.commissionRate).then(result => {
        if (!result.success) {
            alert("Error saving to server: " + (result.error || "unknown error"));
            return;
        }
        const msg = document.getElementById("settingsSavedMsg");
        msg.style.display = "block";
        setTimeout(() => { msg.style.display = "none"; }, 2500);
        renderCompensationPage();
    });
}






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

    Promise.all([
        saveSettingToServer('baseFare', base),
        saveSettingToServer('pricePerKm', perKm)
    ]).then(results => {
        if (results.some(r => !r.success)) {
            alert("Error saving fare settings to server.");
            return;
        }
        updateFarePreview();
        const msg = document.getElementById("fareSettingsSavedMsg");
        msg.style.display = "block";
        setTimeout(() => { msg.style.display = "none"; }, 2500);
    });
}




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

    saveSettingToServer('cancellationFee', value).then(result => {
        if (!result.success) {
            alert("Error saving to server: " + (result.error || "unknown error"));
            return;
        }
        updateCancellationAmounts();
        const msg = document.getElementById("cancellationFeeSavedMsg");
        msg.style.display = "block";
        setTimeout(() => { msg.style.display = "none"; }, 2500);
    });
}




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

    saveSettingToServer('lowRatingThreshold', value).then(result => {
        if (!result.success) {
            alert("Error saving to server: " + (result.error || "unknown error"));
            return;
        }
        const msg = document.getElementById("lowRatingSavedMsg");
        msg.style.display = "block";
        setTimeout(() => { msg.style.display = "none"; }, 2500);
        renderRatingsTable(dashboardData.driverRatings);
    });
}

/* ================= DRIVER ROSTER (LIVE FROM DATABASE) ================= */

function renderDriverRosterTable(roster) {
    let table = document.getElementById("driversListTable");
    if (!table) return;

    while (table.rows.length > 1) {
        table.deleteRow(1);
    }

    roster.forEach(function(d) {
        let statusClass = d.driverAccount_Status === "Active" ? "active" : "suspended";
        let vehicle = d.make && d.model ? `${d.make} ${d.model}` : "—";

        let row = table.insertRow();
        row.innerHTML = `
            <td>DRV-${d.DriverID}</td>
            <td>${d.DriverFname} ${d.DriverLname}</td>
            <td>${vehicle}</td>
            <td class="driverListStatus ${statusClass}">${d.driverAccount_Status}</td>
        `;
    });
}
document.addEventListener("DOMContentLoaded", loadDashboardData);
