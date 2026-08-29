/* ============================================================
   FleetDesk — application logic
   Everything here is behaviour: state, rendering, and events.
   Markup lives in index.html, appearance lives in styles.css.
   ============================================================ */

/* ---------- tiny inline icon set (no external dependency) ---------- */

const ICON = {
  grid: '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
  userCheck: '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg>',
  warning: '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 15-6-11a2 2 0 0 0-3.5 0L3 15a2 2 0 0 0 1.7 3h13.5a2 2 0 0 0 1.7-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',
  wallet: '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-1"/><path d="M18 12a2 2 0 0 0 0 4h3v-4Z"/></svg>',
  search: '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>',
  check: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>',
  x: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
  shieldOk: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V5l8-3 8 3v8Z"/><path d="m9 12 2 2 4-4"/></svg>',
  shieldWarn: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V5l8-3 8 3v8Z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>',
  shieldQ: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V5l8-3 8 3v8Z"/><path d="M9.5 9a2.5 2.5 0 0 1 5 0c0 1.5-2 1.7-2 3.3"/><path d="M12 16h.01"/></svg>',
  phone: '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.68 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.32 1.85.55 2.81.68A2 2 0 0 1 22 16.92Z"/></svg>',
  mail: '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>',
  car: '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2v-4l-3-5H6L3 13v4h2"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="16.5" cy="17.5" r="2.5"/></svg>',
  user: '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>',
  banknote: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>',
  slash: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m5 5 14 14"/></svg>',
  arrowUp: '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>',
  badgeOk: '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 6v6c0 5 3.4 8 8 10 4.6-2 8-5 8-10V6Z"/><path d="m9 12 2 2 4-4"/></svg>',
};

/* ---------- seed data ---------- */

function freshSeedData() {
  return {
    driverCompensation: [
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
finalCommission: 2860,
    status: "Calculated",
    paymentDate: "2026-08-28"
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
    cancellation:0,
    finalCommission: 2060,
    status: "Approved",
    paymentDate: "2026-09-01"
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
    bonuses: 0,
    penalties: 100,
    finalCommission: 2060,
    status: "Calculated",
    paymentDate: "2026-08-28"
  }
],
    pendingDrivers: [
      { id: "DRV-0417", name: "Nomvula Khumalo", phone: "+27 71 204 5581", email: "n.khumalo@mailbox.co", city: "Fort Beaufort",
        vehicle: { make: "Toyota", model: "Corolla Quest", year: 2019, plate: "EC 41 KH GP" },
        documents: {  license: "verified",registration: "pending", background: "verified" },
        submittedAt: "2026-08-21" },
      { id: "DRV-0418", name: "Sipho Mahlangu", phone: "+27 82 917 3320", email: "sipho.m@fastmail.co", city: "Fort Beaufort",
        vehicle: { make: "Hyundai", model: "Grand i10", year: 2021, plate: "EC 88 SM GP" },
        documents: { license: "verified", registration: "verified", background: "verified" },
        submittedAt: "2026-08-23" },
      { id: "DRV-0419", name: "Aisha Patel", phone: "+27 64 550 1187", email: "aisha.patel@webmail.co", city: "Alice",
        vehicle: { make: "Volkswagen", model: "Polo Vivo", year: 2020, plate: "EC 12 AP GP" },
        documents: { license: "verified", registration: "verified", background: "pending" },
        submittedAt: "2026-08-24" },
      { id: "DRV-0420", name: "Thabo Nkosi", phone: "+27 76 442 9903", email: "t.nkosi@mailbox.co", city: "Fort Beaufort",
        vehicle: { make: "Nissan", model: "Almera", year: 2017, plate: "EC 05 TN GP" },
        documents: {  license: "verified", registration: "verified", background: "verified" },
        submittedAt: "2026-08-25" },
    ],
    processedDrivers: [
      { id: "DRV-0390", name: "Lindiwe Zulu", decision: "approved", decidedAt: "2026-08-18", reason: "" },
      { id: "DRV-0402", name: "Johan Coetzee", decision: "rejected", decidedAt: "2026-08-19",
        reason: "Vehicle registration details did not match the information submitted." },
    ],
    complaints: [
      { id: "CMP-2291", driverId: "DRV-0355", driverName: "Bongani Dube", riderName: "K. Adams",
        category: "Unsafe driving", severity: "High", status: "New",
        description: "Rider reports driver was speeding through a school zone and ran a red light on Alexandria Road during the trip.",
        filedAt: "2026-08-25", assignedTo: null, resolution: "" },
      { id: "CMP-2292", driverId: "DRV-0361", driverName: "Precious Mokoena", riderName: "S. van Wyk",
        category: "Overcharging", severity: "Medium", status: "Investigating",
        description: "Fare charged was significantly higher than the quoted estimate; rider suspects a manual override on the meter.",
        filedAt: "2026-08-24", assignedTo: "You", resolution: "" },
      { id: "CMP-2293", driverId: "DRV-0344", driverName: "Michael Botha", riderName: "N. Mtshali",
        category: "Vehicle condition", severity: "Low", status: "New",
        description: "Rider noted a strong smell of cigarette smoke and a cracked rear window.",
        filedAt: "2026-08-23", assignedTo: null, resolution: "" },
      { id: "CMP-2294", driverId: "DRV-0361", driverName: "Precious Mokoena", riderName: "T. Fortuin",
        category: "Rude behaviour", severity: "Medium", status: "Resolved",
        description: "Driver was reportedly dismissive and argumentative when rider asked for a route change.",
        filedAt: "2026-08-19", assignedTo: "You",
        resolution: "Driver coached on rider communication standards. Written warning issued." },
      { id: "CMP-2295", driverId: "DRV-0378", driverName: "Wandile Ngcobo", riderName: "R. Naidoo",
        category: "Route deviation", severity: "Low", status: "Dismissed",
        description: "Rider flagged a longer route than expected.",
        filedAt: "2026-08-17", assignedTo: "You",
        resolution: "GPS logs show detour was due to a road closure. No fault found." },
    ],
    compensation: [
      { id: "PAY-1188", driverId: "DRV-0355", driverName: "Bongani Dube", type: "Trip cancellation", amount: 145,
        status: "Pending", requestedAt: "2026-08-25",
        description: "Rider cancelled 4 minutes after pickup confirmation; driver had already arrived at pickup point.", note: "" },
      { id: "PAY-1189", driverId: "DRV-0361", driverName: "Precious Mokoena", type: "Vehicle damage", amount: 2400,
        status: "Pending", requestedAt: "2026-08-24",
        description: "Rider vomited in the vehicle; requesting cover for professional interior cleaning.", note: "" },
      { id: "PAY-1190", driverId: "DRV-0344", driverName: "Michael Botha", type: "Fuel surcharge", amount: 80,
        status: "Approved", requestedAt: "2026-08-21",
        description: "Long-distance out-of-zone trip requested by rider, return leg unpaid.",
        note: "Approved per out-of-zone policy 4.2." },
      { id: "PAY-1191", driverId: "DRV-0378", driverName: "Wandile Ngcobo", type: "Accident excess", amount: 3500,
        status: "Paid", requestedAt: "2026-08-14",
        description: "Minor collision, not at fault, insurance excess reimbursement.", note: "Paid via EFT batch 08-19." },
      { id: "PAY-1192", driverId: "DRV-0402", driverName: "Johan Coetzee", type: "Equipment damage", amount: 220,
        status: "Denied", requestedAt: "2026-08-15",
        description: "Requesting cover for a cracked phone mount.",
        note: "Wear-and-tear items are excluded under the driver equipment policy." },
    ],
  };
}

/* ---------- application state ----------
   Kept in memory only. Swap loadState()/saveState() for
   localStorage or a real backend call when you deploy this. */

let state = {
  data: freshSeedData(),
  tab: "overview",
  filters: { complaints: "Open", compensation: "Pending" },
  query: "",
  openForm: null, // { kind: 'reject'|'resolve'|'deny', id: '...' }
};

function loadState() { /* no-op: replace with real persistence if desired */ }
function saveState() { /* no-op: replace with real persistence if desired */ }

/* ---------- helpers ---------- */

const currency = (n) =>
  new Intl.NumberFormat("en-ZA", { style: "currency", currency: "ZAR", maximumFractionDigits: 0 }).format(n);

const docLabel = { verified: "Verified", pending: "Pending", missing: "Missing", expired: "Expired" };
const docIcon = (v) => (v === "verified" ? ICON.shieldOk : v === "pending" ? ICON.shieldQ : ICON.shieldWarn);

const severityTone = { High: "bad", Medium: "warn", Low: "neutral" };
const complaintStatusTone = { New: "warn", Investigating: "info", Resolved: "good", Dismissed: "neutral" };
const payStatusTone = { 
  Pending: "warn", 
  Approved: "info", 
  Paid: "good", 
  Denied: "bad" 
};

const commissionStatusTone = {
  Calculated: "warn",
  Approved: "info",
  Paid: "good",
  Rejected: "bad"
};
function badge(tone, label) {
  return `<span class="fd-badge ${tone}">${label}</span>`;
}

function escapeHtml(str) {
  return String(str).replace(/[&<>"']/g, (c) => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;",
  }[c]));
}

let toastTimer = null;
function flash(message) {
  const slot = document.getElementById("fd-toast-slot");
  slot.innerHTML = `<div class="fd-toast">${escapeHtml(message)}</div>`;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { slot.innerHTML = ""; }, 2600);
}

/* ---------- actions (mutate state, then re-render) ---------- */

const actions = {
    approveDriverCompensation(id) {
  const c = state.data.driverCompensation.find(
    (x) => x.id === id
  );

  if (!c) return;

  c.status = "Approved";

  flash(
    `${c.driverName}'s commission payment was approved.`
  );
},

rejectDriverCompensation(id) {
  const c = state.data.driverCompensation.find(
    (x) => x.id === id
  );

  if (!c) return;

  c.status = "Rejected";

  flash(
    `${c.driverName}'s commission payment was rejected.`
  );
},

payDriverCompensation(id) {
  const c = state.data.driverCompensation.find(
    (x) => x.id === id
  );

  if (!c) return;

  c.status = "Paid";

  flash(
    `${c.driverName}'s payment has been marked as paid.`
  );
},
  approveDriver(id) {
    const d = state.data.pendingDrivers.find((x) => x.id === id);
    if (!d) return;
    state.data.pendingDrivers = state.data.pendingDrivers.filter((x) => x.id !== id);
    state.data.processedDrivers.push({ id: d.id, name: d.name, decision: "approved", decidedAt: today(), reason: "" });
    flash(`${d.name} approved and onboarded.`);
  },
  rejectDriver(id, reason) {
    const d = state.data.pendingDrivers.find((x) => x.id === id);
    if (!d || !reason.trim()) return;
    state.data.pendingDrivers = state.data.pendingDrivers.filter((x) => x.id !== id);
    state.data.processedDrivers.push({ id: d.id, name: d.name, decision: "rejected", decidedAt: today(), reason: reason.trim() });
    flash(`${d.name}'s application was rejected.`);
    state.openForm = null;
  },
  assignComplaint(id) {
    const c = state.data.complaints.find((x) => x.id === id);
    if (!c) return;
    c.status = "Investigating";
    c.assignedTo = "You";
    flash("Complaint assigned to you.");
  },
  resolveComplaint(id, resolution) {
    const c = state.data.complaints.find((x) => x.id === id);
    if (!c || !resolution.trim()) return;
    c.status = "Resolved";
    c.resolution = resolution.trim();
    flash("Complaint marked resolved.");
    state.openForm = null;
  },
  dismissComplaint(id) {
    const c = state.data.complaints.find((x) => x.id === id);
    if (!c) return;
    c.status = "Dismissed";
    c.resolution = c.resolution || "Dismissed — no policy violation found.";
    flash("Complaint dismissed.");
  },
  approvePay(id) {
    const c = state.data.compensation.find((x) => x.id === id);
    if (!c) return;
    c.status = "Approved";
    flash("Compensation approved.");
  },
  denyPay(id, note) {
    const c = state.data.compensation.find((x) => x.id === id);
    if (!c || !note.trim()) return;
    c.status = "Denied";
    c.note = note.trim();
    flash("Compensation request denied.");
    state.openForm = null;
  },
  markPaid(id) {
    const c = state.data.compensation.find((x) => x.id === id);
    if (!c) return;
    c.status = "Paid";
    flash("Payout marked as paid.");
  },
};

function today() { return new Date().toISOString().slice(0, 10); }

/* ---------- counts used by the sidebar & KPIs ---------- */

function counts() {
  return {
    drivers: state.data.pendingDrivers.length,
    complaints: state.data.complaints.filter((c) => c.status === "New" || c.status === "Investigating").length,
    compensation: state.data.compensation.filter((c) => c.status === "Pending").length,
  };
}

/* ---------- sidebar nav ---------- */

const NAV = [
  { key: "overview", label: "Overview", icon: ICON.grid },
  { key: "drivers", label: "Driver approvals", icon: ICON.userCheck },
  { key: "complaints", label: "Complaints", icon: ICON.warning },
  { key: "compensation", label: "Driver reimbursement", icon: ICON.wallet },
  { key: "driver-compensation", label: "Driver Compensation", icon: ICON.wallet },
];

/* ---------- futuristic ladder sidebar nav ---------- */

function renderNav() {
    const c = counts();

    const html = NAV.map((n) => {
        const active = state.tab === n.key ? "active" : "";

        const countHtml =
            n.key !== "overview"
                ? `<span class="fd-count">${c[n.key]}</span>`
                : "";

        return `
            <button
                class="fd-navitem ${active}"
                data-nav="${n.key}"
                aria-current="${state.tab === n.key ? "page" : "false"}"
            >
                ${n.icon}
                <span class="fd-nav-label">${n.label}</span>
                ${countHtml}
            </button>
        `;
    }).join("");

    document.getElementById("fd-nav").innerHTML = html;
}

const TITLES = {
  overview: [
    "Overview",
    "Today's queue across approvals, complaints, and payouts"
  ],

  drivers: [
    "Driver approvals",
    "Review documents and decide who joins the fleet"
  ],

  complaints: [
    "Complaints",
    "Triage, investigate, and resolve rider reports"
  ],

  compensation: [
    "Driver reimbursement",
    "Review and settle driver reimbursement requests"
  ],

  "driver-compensation": [
    "Driver Compensation",
    "Calculate, review, and approve driver commission payments"
  ],
};
/* ---------- section renderers ---------- */

function renderOverview() {
  const d = state.data;
  const pendingCount = d.pendingDrivers.length;
  const openComplaints = d.complaints.filter((c) => c.status === "New" || c.status === "Investigating").length;
  const pendingPayout = d.compensation
    .filter((c) => c.status === "Pending" || c.status === "Approved")
    .reduce((s, c) => s + c.amount, 0);
  const resolvedThisWeek = d.complaints.filter((c) => c.status === "Resolved").length;

  const highSeverity = d.complaints.filter((c) => c.severity === "High" && c.status !== "Resolved" && c.status !== "Dismissed");
  const staleApprovals = d.pendingDrivers.filter((dr) => Object.values(dr.documents).some((v) => v === "missing" || v === "expired"));

  const kpis = `
    <div class="fd-kpis">
      <div class="fd-kpi">
        <div class="fd-kpi-top"><span class="fd-kpi-label">Awaiting approval</span>
          <span class="fd-kpi-icon" style="background:rgba(245,197,24,0.14); color:var(--signal);">${ICON.userCheck}</span></div>
        <div class="fd-kpi-value">${pendingCount}</div>
      </div>
      <div class="fd-kpi">
        <div class="fd-kpi-top"><span class="fd-kpi-label">Open complaints</span>
          <span class="fd-kpi-icon" style="background:rgba(255,107,94,0.14); color:var(--bad);">${ICON.warning}</span></div>
        <div class="fd-kpi-value">${openComplaints}</div>
      </div>
      <div class="fd-kpi">
        <div class="fd-kpi-top"><span class="fd-kpi-label">Pending payouts</span>
          <span class="fd-kpi-icon" style="background:rgba(94,176,255,0.14); color:var(--info);">${ICON.wallet}</span></div>
        <div class="fd-kpi-value">${currency(pendingPayout)}</div>
      </div>
      <div class="fd-kpi">
        <div class="fd-kpi-top"><span class="fd-kpi-label">Resolved this week</span>
          <span class="fd-kpi-icon" style="background:rgba(56,212,143,0.14); color:var(--good);">${ICON.check}</span></div>
        <div class="fd-kpi-value">${resolvedThisWeek}</div>
      </div>
    </div>`;

  let attention = `<div class="fd-section-label">Needs attention</div>`;
  if (highSeverity.length === 0 && staleApprovals.length === 0) {
    attention += `<div class="fd-empty">${ICON.badgeOk}<div class="fd-empty-title">Queue is clear</div>Nothing urgent right now.</div>`;
  } else {
    attention += highSeverity.map((c) => `
      <div class="fd-attn" data-nav="complaints">
        <div class="fd-attn-left">
          ${badge("bad", "High severity")}
          <span class="fd-mono" style="color:var(--muted); font-size:12px;">${c.id}</span>
          <span style="font-size:13px;">${escapeHtml(c.category)} — ${escapeHtml(c.driverName)}</span>
        </div>${ICON.arrowUp}
      </div>`).join("");
    attention += staleApprovals.map((dr) => `
      <div class="fd-attn" data-nav="drivers">
        <div class="fd-attn-left">
          ${badge("warn", "Document issue")}
          <span class="fd-mono" style="color:var(--muted); font-size:12px;">${dr.id}</span>
          <span style="font-size:13px;">${escapeHtml(dr.name)} — Driver application contains information that requires further verification.</span>
        </div>${ICON.arrowUp}
      </div>`).join("");
  }

  return kpis + attention;
}

function renderDrivers() {
  const q = state.query.toLowerCase();
  const list = state.data.pendingDrivers.filter((dr) =>
    dr.name.toLowerCase().includes(q) || dr.id.toLowerCase().includes(q) || dr.city.toLowerCase().includes(q)
  );

  let html = `
    <div class="fd-searchbar">
      ${ICON.search}
      <input id="fd-search-input" placeholder="Search name, ID, or city…" value="${escapeHtml(state.query)}">
    </div>`;

  if (list.length === 0) {
    html += `<div class="fd-empty">${ICON.userCheck}<div class="fd-empty-title">No pending applications</div>New driver sign-ups will appear here for review.</div>`;
  } else {
    html += list.map((dr) => {
      const docsOk = Object.values(dr.documents).every((v) => v === "verified");
      const docChips = Object.entries(dr.documents).map(([k, v]) =>
        `<span class="fd-docchip ${v}">${docIcon(v)} ${k.charAt(0).toUpperCase() + k.slice(1)}: ${docLabel[v]}</span>`
      ).join("");

      const rejecting = state.openForm && state.openForm.kind === "reject" && state.openForm.id === dr.id;
      const rejectForm = rejecting ? `
        <div class="fd-inlineform">
          <input id="fd-reason-input" placeholder="Reason for rejection (sent to driver)…">
          <button class="fd-btn danger" data-action="confirm-reject" data-id="${dr.id}">Confirm</button>
          <button class="fd-btn ghost" data-action="cancel-form">${ICON.x}</button>
        </div>` : "";

      return `
        <div class="fd-ticket">
          <div class="fd-ticket-stub">
            <div class="fd-ticket-id fd-mono">${dr.id}</div>
            <div class="fd-ticket-date fd-mono">${dr.submittedAt}</div>
          </div>
          <div class="fd-ticket-body">
            <div class="fd-ticket-head">
              <div>
                <div class="fd-ticket-name">${escapeHtml(dr.name)}</div>
                <div class="fd-ticket-meta">${escapeHtml(dr.city)} · ${dr.vehicle.year} ${escapeHtml(dr.vehicle.make)} ${escapeHtml(dr.vehicle.model)}</div>
              </div>
              ${badge(docsOk ? "good" : "warn", docsOk ? "Docs complete" : "Docs incomplete")}
            </div>
            <div class="fd-row">
              <span class="fd-detail">${ICON.phone} ${dr.phone}</span>
              <span class="fd-detail">${ICON.mail} ${dr.email}</span>
              <span class="fd-detail fd-mono">${ICON.car} ${dr.vehicle.plate}</span>
            </div>
            <div class="fd-doclist">${docChips}</div>
            <div class="fd-actions">
              <button class="fd-btn primary" data-action="approve-driver" data-id="${dr.id}">${ICON.check} Approve driver</button>
              <button class="fd-btn danger" data-action="toggle-reject" data-id="${dr.id}">${ICON.x} Reject</button>
            </div>
            ${rejectForm}
          </div>
        </div>`;
    }).join("");
  }

  if (state.data.processedDrivers.length > 0) {
    html += `<div class="fd-section-label">Recent decisions</div>`;
    html += state.data.processedDrivers.slice().reverse().map((dr) => `
      <div class="fd-attn">
        <div class="fd-attn-left">
          ${badge(dr.decision === "approved" ? "good" : "bad", dr.decision)}
          <span class="fd-mono" style="color:var(--muted); font-size:12px;">${dr.id}</span>
          <span style="font-size:13px;">${escapeHtml(dr.name)}</span>
          ${dr.reason ? `<span style="font-size:12px; color:var(--muted-2);">— ${escapeHtml(dr.reason)}</span>` : ""}
        </div>
        <span class="fd-mono" style="font-size:11px; color:var(--muted-2);">${dr.decidedAt}</span>
      </div>`).join("");
  }

  return html;
}

function renderComplaints() {
  const filter = state.filters.complaints;
  const chips = ["Open", "New", "Investigating", "Resolved", "Dismissed", "All"];
  let html = `<div class="fd-filters">` +
    chips.map((f) => `<button class="fd-chip ${filter === f ? "active" : ""}" data-action="filter-complaints" data-value="${f}">${f}</button>`).join("") +
    `</div>`;

  const list = state.data.complaints.filter((c) => {
    if (filter === "Open") return c.status === "New" || c.status === "Investigating";
    if (filter === "All") return true;
    return c.status === filter;
  });

  if (list.length === 0) {
    html += `<div class="fd-empty">${ICON.warning}<div class="fd-empty-title">Nothing here</div>No complaints match this filter.</div>`;
    return html;
  }

  html += list.map((c) => {
    const resolving = state.openForm && state.openForm.kind === "resolve" && state.openForm.id === c.id;
    const resolveForm = resolving ? `
      <div class="fd-inlineform">
        <textarea id="fd-resolution-input" placeholder="Resolution notes — what action was taken…"></textarea>
        <button class="fd-btn primary" data-action="confirm-resolve" data-id="${c.id}">Save</button>
      </div>` : "";

    const openActions = (c.status === "New" || c.status === "Investigating") ? `
      <div class="fd-actions">
        ${c.status === "New" ? `<button class="fd-btn signal" data-action="assign-complaint" data-id="${c.id}">${ICON.user} Assign to me</button>` : ""}
        <button class="fd-btn primary" data-action="toggle-resolve" data-id="${c.id}">${ICON.check} Resolve</button>
        <button class="fd-btn ghost" data-action="dismiss-complaint" data-id="${c.id}">${ICON.slash} Dismiss</button>
      </div>${resolveForm}` : "";

    return `
      <div class="fd-ticket">
        <div class="fd-ticket-stub">
          <div class="fd-ticket-id fd-mono">${c.id}</div>
          <div class="fd-ticket-date fd-mono">${c.filedAt}</div>
        </div>
        <div class="fd-ticket-body">
          <div class="fd-ticket-head">
            <div>
              <div class="fd-ticket-name">${escapeHtml(c.category)}</div>
              <div class="fd-ticket-meta">Driver: ${escapeHtml(c.driverName)} (${c.driverId}) · Filed by ${escapeHtml(c.riderName)}</div>
            </div>
            <div style="display:flex; gap:6px;">
              ${badge(severityTone[c.severity], c.severity)}
              ${badge(complaintStatusTone[c.status], c.status)}
            </div>
          </div>
          <div class="fd-desc">${escapeHtml(c.description)}</div>
          ${c.resolution ? `<div class="fd-desc" style="border-left:3px solid var(--good);"><strong style="color:var(--good);">Resolution:</strong> ${escapeHtml(c.resolution)}</div>` : ""}
          ${c.assignedTo && c.status !== "Resolved" && c.status !== "Dismissed" ? `<div class="fd-row"><span class="fd-detail">${ICON.user} Assigned to ${c.assignedTo}</span></div>` : ""}
          ${openActions}
        </div>
      </div>`;
  }).join("");

  return html;
}

function renderCompensation() {
  const filter = state.filters.compensation;
  const chips = ["Pending", "Approved", "Paid", "Denied", "All"];
  let html = `<div class="fd-filters">` +
    chips.map((f) => `<button class="fd-chip ${filter === f ? "active" : ""}" data-action="filter-compensation" data-value="${f}">${f}</button>`).join("") +
    `</div>`;

  const list = state.data.compensation.filter((c) => (filter === "All" ? true : c.status === filter));

  if (list.length > 0) {
    const total = list.reduce((s, c) => s + c.amount, 0);
    html += `<div style="font-size:12px; color:var(--muted); margin-bottom:14px;">${list.length} request${list.length !== 1 ? "s" : ""} · <span class="fd-mono">${currency(total)}</span> total</div>`;
  }

  if (list.length === 0) {
    html += `<div class="fd-empty">${ICON.wallet}<div class="fd-empty-title">Nothing here</div>No compensation requests match this filter.</div>`;
    return html;
  }

  html += list.map((c) => {
    const denying = state.openForm && state.openForm.kind === "deny" && state.openForm.id === c.id;
    const denyForm = denying ? `
      <div class="fd-inlineform">
        <input id="fd-deny-input" placeholder="Reason for denial…">
        <button class="fd-btn danger" data-action="confirm-deny" data-id="${c.id}">Confirm</button>
      </div>` : "";

    let actionsHtml = "";
    if (c.status === "Pending") {
      actionsHtml = `
        <div class="fd-actions">
          <button class="fd-btn primary" data-action="approve-pay" data-id="${c.id}">${ICON.check} Approve</button>
          <button class="fd-btn danger" data-action="toggle-deny" data-id="${c.id}">${ICON.x} Deny</button>
        </div>${denyForm}`;
    } else if (c.status === "Approved") {
      actionsHtml = `<div class="fd-actions"><button class="fd-btn signal" data-action="mark-paid" data-id="${c.id}">${ICON.banknote} Mark as paid</button></div>`;
    }

    return `
      <div class="fd-ticket">
        <div class="fd-ticket-stub">
          <div class="fd-ticket-id fd-mono">${c.id}</div>
          <div class="fd-ticket-date fd-mono">${c.requestedAt}</div>
        </div>
        <div class="fd-ticket-body">
          <div class="fd-ticket-head">
            <div>
              <div class="fd-ticket-name">${escapeHtml(c.type)}</div>
              <div class="fd-ticket-meta">${escapeHtml(c.driverName)} (${c.driverId})</div>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
              <span class="fd-amount">${currency(c.amount)}</span>
              ${badge(payStatusTone[c.status], c.status)}
            </div>
          </div>
          <div class="fd-desc">${escapeHtml(c.description)}</div>
          ${c.note ? `<div class="fd-desc" style="border-left:3px solid var(--muted-2);"><strong>Note:</strong> ${escapeHtml(c.note)}</div>` : ""}
          ${actionsHtml}
        </div>
      </div>`;
  }).join("");

  return html;
}
function renderDriverCompensation() {

    const list = state.data.driverCompensation;

    let html = `
        <div class="fd-section-label">Driver Commission Payments</div>

        <div class="fd-kpis">

            <div class="fd-kpi">
                <div class="fd-kpi-top">
                    <span class="fd-kpi-label">Total Payments</span>
                </div>
                <div class="fd-kpi-value">${list.length}</div>
            </div>

            <div class="fd-kpi">
                <div class="fd-kpi-top">
                    <span class="fd-kpi-label">Completed Rides</span>
                </div>
                <div class="fd-kpi-value">
                    ${list.reduce((sum, c) => sum + c.completedRides, 0)}
                </div>
            </div>

            <div class="fd-kpi">
                <div class="fd-kpi-top">
                    <span class="fd-kpi-label">Total Commission</span>
                </div>
                <div class="fd-kpi-value">
                    ${currency(
                        list.reduce((sum, c) => sum + c.finalCommission, 0)
                    )}
                </div>
            </div>

        </div>
    `;

    if (list.length === 0) {
        html += `
            <div class="fd-empty">
                ${ICON.wallet}
                <div class="fd-empty-title">No commission records</div>
                Driver commission payments will appear here.
            </div>
        `;

        return html;
    }

    html += list.map((c) => {

        const statusTone = {
            Calculated: "warn",
            Approved: "info",
            Paid: "good",
            Rejected: "bad"
        };

        let actionsHtml = "";

        if (c.status === "Calculated") {
            actionsHtml = `
                <div class="fd-actions">

                    <button
                        class="fd-btn primary"
                        data-action="approve-driver-compensation"
                        data-id="${c.id}">
                        ${ICON.check} Approve
                    </button>

                    <button
                        class="fd-btn danger"
                        data-action="reject-driver-compensation"
                        data-id="${c.id}">
                        ${ICON.x} Reject
                    </button>

                </div>
            `;
        }

        else if (c.status === "Approved") {
            actionsHtml = `
                <div class="fd-actions">

                    <button
                        class="fd-btn signal"
                        data-action="pay-driver-compensation"
                        data-id="${c.id}">
                        ${ICON.banknote} Mark as paid
                    </button>

                </div>
            `;
        }

        return `
            <div class="fd-ticket">

                <div class="fd-ticket-stub">
                    <div class="fd-ticket-id fd-mono">
                        ${c.id}
                    </div>

                    <div class="fd-ticket-date fd-mono">
                        ${c.period}
                    </div>
                </div>

                <div class="fd-ticket-body">

                    <div class="fd-ticket-head">

                        <div>
                            <div class="fd-ticket-name">
                                ${escapeHtml(c.driverName)}
                            </div>

                            <div class="fd-ticket-meta">
                                ${c.driverId} · ${c.frequency}
                            </div>
                        </div>

                        <div style="display:flex; align-items:center; gap:10px;">

                            <span class="fd-amount">
                                ${currency(c.finalCommission)}
                            </span>

                            ${badge(
                                statusTone[c.status],
                                c.status
                            )}

                        </div>

                    </div>

                    <div class="fd-row">

                        <span class="fd-detail">
                            Completed rides: 
                            <strong>${c.completedRides}</strong>
                        </span>

                        <span class="fd-detail">
                            Total fare:
                            <strong>${currency(c.totalRideValue)}</strong>
                        </span>

                        <span class="fd-detail">
                            Commission:
                            <strong>${Math.round(c.commissionRate * 100)}%</strong>
                        </span>

                    </div>

                    <div class="fd-desc">

                        <strong>Calculation</strong><br>

                        ${currency(c.totalRideValue)}
                        × ${Math.round(c.commissionRate * 100)}%
                        =
                        ${currency(c.baseCommission)}

                      ${c.penalties > 0 
    ? ` − Penalty ${currency(c.penalties)}`
    : "" 
}

${c.cancellation > 0 
    ? ` + Cancellation ${currency(c.cancellation)}`
    : "" 
}

                        <br><br>

                        <strong>Final payment:
                            ${currency(c.finalCommission)}
                        </strong>

                    </div>

                    ${actionsHtml}

                </div>

            </div>
        `;

    }).join("");

    return html;
}

/* ---------- master render ---------- */

function render() {
  renderNav();
  document.getElementById("fd-title").textContent = TITLES[state.tab][0];
  document.getElementById("fd-subtitle").textContent = TITLES[state.tab][1];
  document.getElementById("fd-date").textContent = new Date().toLocaleDateString("en-ZA", {
    weekday: "short", day: "2-digit", month: "short", year: "numeric",
  });

  const map = {
  overview: renderOverview,
  drivers: renderDrivers,
  complaints: renderComplaints,
  compensation: renderCompensation,
  "driver-compensation": renderDriverCompensation
};
  document.getElementById("fd-tab-content").innerHTML = map[state.tab]();

  // refocus the search input's caret at the end after a re-render, if it exists
  const search = document.getElementById("fd-search-input");
  if (search) {
    search.focus();
    search.setSelectionRange(search.value.length, search.value.length);
  }
}

/* ---------- event delegation ---------- */

document.addEventListener("click", (e) => {
  const navBtn = e.target.closest("[data-nav]");
  if (navBtn) {
    state.tab = navBtn.dataset.nav;
    state.openForm = null;
    render();
    return;
  }

  const el = e.target.closest("[data-action]");
  if (!el) return;
  const { action, id, value } = el.dataset;

   switch (action) {
    case "approve-driver": actions.approveDriver(id); break;
    case "toggle-reject": state.openForm = (state.openForm && state.openForm.id === id) ? null : { kind: "reject", id }; break;
    case "confirm-reject": {
      const input = document.getElementById("fd-reason-input");
      actions.rejectDriver(id, input ? input.value : "");
      break;
    }
    case "cancel-form": state.openForm = null; break;

    case "assign-complaint": actions.assignComplaint(id); break;
    case "toggle-resolve": state.openForm = (state.openForm && state.openForm.id === id) ? null : { kind: "resolve", id }; break;
    case "confirm-resolve": {
      const input = document.getElementById("fd-resolution-input");
      actions.resolveComplaint(id, input ? input.value : "");
      break;
    }
    case "dismiss-complaint": actions.dismissComplaint(id); break;
    case "filter-complaints": state.filters.complaints = value; break;

    case "approve-pay": actions.approvePay(id); break;
    case "toggle-deny": state.openForm = (state.openForm && state.openForm.id === id) ? null : { kind: "deny", id }; break;
    case "confirm-deny": {
      const input = document.getElementById("fd-deny-input");
      actions.denyPay(id, input ? input.value : "");
      break;
    }
    case "mark-paid": actions.markPaid(id); break;
    case "filter-compensation": state.filters.compensation = value; break;
    /* ---------- DRIVER COMPENSATION ---------- */

case "approve-driver-compensation":
  actions.approveDriverCompensation(id);
  break;

case "reject-driver-compensation":
  actions.rejectDriverCompensation(id);
  break;

case "pay-driver-compensation":
  actions.payDriverCompensation(id);
  break;


  }

  saveState();
  render();
});

document.addEventListener("input", (e) => {
  if (e.target.id === "fd-search-input") {
    state.query = e.target.value;
    render();
  }
});

/* ---------- boot ---------- */

loadState();
render();