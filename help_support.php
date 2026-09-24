<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userType = '';
$userName = 'User';
$homePage = 'driver_dashboard.php';

if (!empty($_SESSION['student_id'])) {

    $userType = 'student';
    $userName = $_SESSION['student_name'] ?? 'Student';
    $homePage = 'student_dashboard.php';

} elseif (!empty($_SESSION['staff_id'])) {

    $userType = 'staff';
    $userName = $_SESSION['staff_name'] ?? 'Staff';
    $homePage = 'staff_dashboard.php';

} elseif (!empty($_SESSION['driver_id'])) {

    $userType = 'driver';
    $userName = $_SESSION['driver_name'] ?? 'Driver';
    $homePage = 'driver_dashboard.php';
}

$pageTitle = "Help & Support";

include 'header.php';

?>

<style>

:root {
    --navy: #0b1f3a;
    --navy-light: #17365f;
    --gold: #d4a72c;
    --gold-dark: #b8860b;
    --orange: #e39b24;
    --card: #ffffff;
    --border: #e5e7eb;
    --ink: #172033;
    --muted: #6b7280;
    --bg: #f5f7fa;
}

.help-page {
    min-height: calc(100vh - 80px);
    background: var(--bg);
    padding: 35px 20px 60px;
}

.help-container {
    max-width: 1050px;
    margin: 0 auto;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    background: var(--navy);
    color: #ffffff;
    text-decoration: none;
    padding: 10px 17px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 24px;
    border: 1px solid var(--navy);
    transition: all 0.2s ease;
}

.back-button:hover {
    background: var(--navy-light);
    border-color: var(--gold);
    color: #ffffff;
    transform: translateY(-1px);
}

.back-arrow {
    font-size: 17px;
    line-height: 1;
}

.page-heading {
    margin-bottom: 25px;
}

.page-heading h1 {
    margin: 0 0 7px;
    font-size: 32px;
    color: var(--navy);
    font-weight: 800;
}

.page-heading h1 span {
    color: var(--gold-dark);
}

.page-heading p {
    margin: 0;
    color: var(--muted);
    font-size: 15px;
}

.help-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(11, 31, 58, 0.06);
    margin-bottom: 18px;
}

.expand-header {
    width: 100%;
    border: none;
    background: #ffffff;
    padding: 20px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    text-align: left;
    transition: background 0.2s ease;
}

.expand-header:hover {
    background: #fafbfc;
}

.expand-header.active {
    border-left: 4px solid var(--gold);
    padding-left: 18px;
}

.expand-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.section-icon {
    width: 43px;
    height: 43px;
    border-radius: 10px;
    background: #fff6df;
    color: var(--gold-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    font-weight: 800;
    flex-shrink: 0;
    border: 1px solid #f3dfaa;
}

.section-title {
    color: var(--navy);
    font-size: 17px;
    font-weight: 750;
}

.section-subtitle {
    color: var(--muted);
    font-size: 13px;
    margin-top: 3px;
}

.expand-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--navy);
    font-size: 18px;
    transition: transform 0.25s ease;
    flex-shrink: 0;
}

.expand-header.active .expand-icon {
    transform: rotate(180deg);
    background: #fff6df;
    color: var(--gold-dark);
}

.expand-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    border-top: 0 solid var(--border);
}

.expand-content.open {
    border-top: 1px solid var(--border);
}

.content-inner {
    padding: 22px;
}

.help-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.help-option {
    border: 1px solid var(--border);
    border-radius: 10px;
    background: #ffffff;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.help-option:hover {
    border-color: var(--gold);
    background: #fffdf7;
    box-shadow: 0 3px 10px rgba(212, 167, 44, 0.08);
}

.help-option.active {
    border-color: var(--gold);
}

.help-option-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.help-option-name {
    font-weight: 700;
    color: var(--navy);
}

.help-option-arrow {
    color: var(--gold-dark);
    font-size: 18px;
    font-weight: 700;
}

.help-option-description {
    color: var(--muted);
    font-size: 13px;
    margin-top: 6px;
}

.sub-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.sub-content.open {
    margin-top: 10px;
}

.sub-content-inner {
    padding: 13px;
    background: #f8fafc;
    border-left: 3px solid var(--gold);
    border-radius: 8px;
    color: #4b5563;
    font-size: 14px;
    line-height: 1.6;
}

.faq-item {
    border-bottom: 1px solid var(--border);
}

.faq-item:last-child {
    border-bottom: none;
}

.faq-question {
    width: 100%;
    border: none;
    background: transparent;
    padding: 17px 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-align: left;
    cursor: pointer;
    color: var(--navy);
    font-weight: 650;
    font-size: 14px;
    transition: color 0.2s ease;
}

.faq-question:hover {
    color: var(--gold-dark);
}

.faq-question.active {
    color: var(--gold-dark);
}

.faq-arrow {
    font-size: 18px;
    color: var(--muted);
    transition: transform 0.2s ease;
}

.faq-question.active .faq-arrow {
    transform: rotate(180deg);
    color: var(--gold-dark);
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.faq-answer-inner {
    padding: 0 4px 17px;
    color: var(--muted);
    font-size: 14px;
    line-height: 1.6;
}

.contact-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 15px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid var(--border);
    transition: border-color 0.2s ease;
}

.contact-item:hover {
    border-color: var(--gold);
}

.contact-icon {
    width: 40px;
    height: 40px;
    border-radius: 9px;
    background: #fff6df;
    color: var(--gold-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
}

.contact-details strong {
    display: block;
    color: var(--navy);
    font-size: 14px;
}

.contact-details span {
    display: block;
    color: var(--muted);
    font-size: 13px;
    margin-top: 2px;
}

.form-group {
    margin-bottom: 17px;
}

.form-group label {
    display: block;
    margin-bottom: 7px;
    color: var(--navy);
    font-size: 14px;
    font-weight: 650;
}

.form-group select,
.form-group textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 12px 13px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: #ffffff;
    color: var(--ink);
    font-family: inherit;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212, 167, 44, 0.12);
}

.form-group textarea {
    min-height: 120px;
    resize: vertical;
}

.submit-button {
    border: none;
    background: var(--navy);
    color: #ffffff;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s ease;
}

.submit-button:hover {
    background: var(--navy-light);
    box-shadow: 0 3px 8px rgba(11, 31, 58, 0.15);
}

.support-note {
    background: #fffaf0;
    border: 1px solid #f0dfac;
    border-left: 4px solid var(--gold);
    border-radius: 9px;
    padding: 13px 15px;
    color: #705b20;
    font-size: 13px;
    margin-bottom: 18px;
    line-height: 1.5;
}

@media (max-width: 700px) {

    .help-page {
        padding: 25px 14px 45px;
    }

    .page-heading h1 {
        font-size: 27px;
    }

    .help-options {
        grid-template-columns: 1fr;
    }

    .expand-header {
        padding: 17px;
    }

    .expand-header.active {
        padding-left: 13px;
    }

    .content-inner {
        padding: 17px;
    }

    .section-title {
        font-size: 15px;
    }

    .section-subtitle {
        font-size: 12px;
    }
}

</style>

<div class="help-page">

    <div class="help-container">

        <a
            href="<?php echo htmlspecialchars($homePage); ?>"
            class="back-button"
        >
            <span class="back-arrow">←</span>
            <span>Back to Dashboard</span>
        </a>

        <div class="page-heading">

            <h1>
                Help <span>&</span> Support
            </h1>

            <p>
                Find answers, get assistance and report problems with CampusCab.
            </p>

        </div>

        <div class="help-card">

            <button
                type="button"
                class="expand-header"
            >

                <div class="expand-left">

                    <div class="section-icon">
                        ?
                    </div>

                    <div>

                        <div class="section-title">
                            How can we help?
                        </div>

                        <div class="section-subtitle">
                            Choose a topic to find more information
                        </div>

                    </div>

                </div>

                <div class="expand-icon">
                    ⌄
                </div>

            </button>

            <div class="expand-content">

                <div class="content-inner">

                    <div class="help-options">

                        <div class="help-option expandable-option">

                            <div class="help-option-top">

                                <span class="help-option-name">
                                    Ride Requests
                                </span>

                                <span class="help-option-arrow">
                                    +
                                </span>

                            </div>

                            <div class="help-option-description">
                                Information about requesting and managing rides.
                            </div>

                            <div class="sub-content">

                                <div class="sub-content-inner">

                                    To request a ride, open
                                    <strong>CampusCab Rides</strong>
                                    from the Services menu. Select your pickup
                                    location and destination, check the estimated
                                    fare and send your request. You can view the
                                    status of your ride from
                                    <strong>My Rides</strong>.

                                </div>

                            </div>

                        </div>

                        <div class="help-option expandable-option">

                            <div class="help-option-top">

                                <span class="help-option-name">
                                    Parcel Delivery
                                </span>

                                <span class="help-option-arrow">
                                    +
                                </span>

                            </div>

                            <div class="help-option-description">
                                Learn how to send and receive parcels.
                            </div>

                            <div class="sub-content">

                                <div class="sub-content-inner">

                                    Use <strong>CampusCab Send</strong> to request
                                    parcel delivery. Enter the parcel details,
                                    pickup location, destination and recipient
                                    information before submitting the request.
                                    Your parcel status can be monitored from
                                    your parcel activity page.

                                </div>

                            </div>

                        </div>

                        <div class="help-option expandable-option">

                            <div class="help-option-top">

                                <span class="help-option-name">
                                    Location & GPS
                                </span>

                                <span class="help-option-arrow">
                                    +
                                </span>

                            </div>

                            <div class="help-option-description">
                                Problems selecting pickup or destination locations.
                            </div>

                            <div class="sub-content">

                                <div class="sub-content-inner">

                                    CampusCab uses your selected pickup and
                                    destination locations to estimate the distance,
                                    travel time and fare. Make sure your browser
                                    has permission to access your location when
                                    using the GPS option.

                                </div>

                            </div>

                        </div>

                        <div class="help-option expandable-option">

                            <div class="help-option-top">

                                <span class="help-option-name">
                                    Fares & Estimates
                                </span>

                                <span class="help-option-arrow">
                                    +
                                </span>

                            </div>

                            <div class="help-option-description">
                                Understand your estimated ride or parcel fare.
                            </div>

                            <div class="sub-content">

                                <div class="sub-content-inner">

                                    The estimated fare is calculated using the
                                    distance between your pickup and destination.
                                    The amount shown before submitting your request
                                    is an estimate and may depend on the CampusCab
                                    pricing configuration.

                                </div>

                            </div>

                        </div>

                        <div class="help-option expandable-option">

                            <div class="help-option-top">

                                <span class="help-option-name">
                                    Account & Profile
                                </span>

                                <span class="help-option-arrow">
                                    +
                                </span>

                            </div>

                            <div class="help-option-description">
                                Help with your CampusCab account information.
                            </div>

                            <div class="sub-content">

                                <div class="sub-content-inner">

                                    Your profile contains your registered
                                    CampusCab information. If any of your details
                                    are incorrect, contact CampusCab support
                                    for assistance.

                                </div>

                            </div>

                        </div>

                        <div class="help-option expandable-option">

                            <div class="help-option-top">

                                <span class="help-option-name">
                                    Ratings
                                </span>

                                <span class="help-option-arrow">
                                    +
                                </span>

                            </div>

                            <div class="help-option-description">
                                Learn about rating your CampusCab experience.
                            </div>

                            <div class="sub-content">

                                <div class="sub-content-inner">

                                    After completing a ride, passengers may be
                                    able to rate their driver and leave feedback.
                                    Ratings help CampusCab monitor service quality
                                    and improve the experience for passengers
                                    and drivers.

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="help-card">

            <button
                type="button"
                class="expand-header"
            >

                <div class="expand-left">

                    <div class="section-icon">
                        FAQ
                    </div>

                    <div>

                        <div class="section-title">
                            Frequently Asked Questions
                        </div>

                        <div class="section-subtitle">
                            Common questions about CampusCab
                        </div>

                    </div>

                </div>

                <div class="expand-icon">
                    ⌄
                </div>

            </button>

            <div class="expand-content">

                <div class="content-inner">

                    <div class="faq-item">

                        <button
                            type="button"
                            class="faq-question"
                        >

                            How do I request a ride?

                            <span class="faq-arrow">
                                ⌄
                            </span>

                        </button>

                        <div class="faq-answer">

                            <div class="faq-answer-inner">

                                Open the CampusCab ride request page,
                                select your pickup location and destination,
                                review the estimated fare and send your request.
                                You can then monitor the request from My Rides.

                            </div>

                        </div>

                    </div>

                    <div class="faq-item">

                        <button
                            type="button"
                            class="faq-question"
                        >

                            How do I cancel a ride?

                            <span class="faq-arrow">
                                ⌄
                            </span>

                        </button>

                        <div class="faq-answer">

                            <div class="faq-answer-inner">

                                A ride can be cancelled when the request is still
                                eligible for cancellation. Open your ride details
                                and use the cancellation option if it is available.

                            </div>

                        </div>

                    </div>

                    <div class="faq-item">

                        <button
                            type="button"
                            class="faq-question"
                        >

                            How do I know when a driver accepts my request?

                            <span class="faq-arrow">
                                ⌄
                            </span>

                        </button>

                        <div class="faq-answer">

                            <div class="faq-answer-inner">

                                Your ride status changes when a driver accepts
                                the request. You can check the current status
                                from your My Rides page. You will also receive
                                a CampusCab notification.

                            </div>

                        </div>

                    </div>

                    <div class="faq-item">

                        <button
                            type="button"
                            class="faq-question"
                        >

                            How do I request parcel delivery?

                            <span class="faq-arrow">
                                ⌄
                            </span>

                        </button>

                        <div class="faq-answer">

                            <div class="faq-answer-inner">

                                Open CampusCab Send from the Services menu.
                                Enter the parcel description, size, recipient
                                information, pickup location and destination,
                                then submit the request.

                            </div>

                        </div>

                    </div>

                    <div class="faq-item">

                        <button
                            type="button"
                            class="faq-question"
                        >

                            What happens after I submit a request?

                            <span class="faq-arrow">
                                ⌄
                            </span>

                        </button>

                        <div class="faq-answer">

                            <div class="faq-answer-inner">

                                Your request is initially placed in a Requested
                                state. A driver can then accept the request.
                                Once accepted, the ride or parcel status will
                                update as the driver progresses.

                            </div>

                        </div>

                    </div>

                    <div class="faq-item">

                        <button
                            type="button"
                            class="faq-question"
                        >

                            What should I do if I have a problem with my driver?

                            <span class="faq-arrow">
                                ⌄
                            </span>

                        </button>

                        <div class="faq-answer">

                            <div class="faq-answer-inner">

                                If you experience a problem during a ride,
                                contact CampusCab support and provide the ride
                                details and a description of the problem.

                            </div>

                        </div>

                    </div>

                    <div class="faq-item">

                        <button
                            type="button"
                            class="faq-question"
                        >

                            Can I rate my driver?

                            <span class="faq-arrow">
                                ⌄
                            </span>

                        </button>

                        <div class="faq-answer">

                            <div class="faq-answer-inner">

                                Yes. After a completed ride, the rating option
                                can be used to provide feedback about your
                                CampusCab experience.

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="help-card">

            <button
                type="button"
                class="expand-header"
            >

                <div class="expand-left">

                    <div class="section-icon">
                        ☎
                    </div>

                    <div>

                        <div class="section-title">
                            Contact Support
                        </div>

                        <div class="section-subtitle">
                            Get in touch with the CampusCab support team
                        </div>

                    </div>

                </div>

                <div class="expand-icon">
                    ⌄
                </div>

            </button>

            <div class="expand-content">

                <div class="content-inner">

                    <div class="support-note">

                        For urgent issues relating to an active ride, provide
                        your ride ID when contacting support so the request
                        can be identified quickly.

                    </div>

                    <div class="contact-list">

                        <div class="contact-item">

                            <div class="contact-icon">
                                ☎
                            </div>

                            <div class="contact-details">

                                <strong>
                                    Phone Support
                                </strong>

                                <span>
                                    043 000 2026
                                </span>

                            </div>

                        </div>

                        <div class="contact-item">

                            <div class="contact-icon">
                                ✉
                            </div>

                            <div class="contact-details">

                                <strong>
                                    Email Support
                                </strong>

                                <span>
                                    support@campuscab.co.za
                                </span>

                            </div>

                        </div>

                        <div class="contact-item">

                            <div class="contact-icon">
                                ⏰
                            </div>

                            <div class="contact-details">

                                <strong>
                                    Support Hours
                                </strong>

                                <span>
                                    Weekdays: 07:00 - 00:00
                                </span>

                                <span>
                                    Weekends: 07:00 - 02:00
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="help-card">

            <button
                type="button"
                class="expand-header"
            >

                <div class="expand-left">

                    <div class="section-icon">
                        !
                    </div>

                    <div>

                        <div class="section-title">
                            Report a Problem
                        </div>

                        <div class="section-subtitle">
                            Tell us about an issue you experienced
                        </div>

                    </div>

                </div>

                <div class="expand-icon">
                    ⌄
                </div>

            </button>

            <div class="expand-content">

                <div class="content-inner">

                    <form method="POST" action="">

                        <div class="form-group">

                            <label for="problem_type">
                                Problem Type
                            </label>

                            <select
                                name="problem_type"
                                id="problem_type"
                                required
                            >

                                <option value="">
                                    Select a problem
                                </option>

                                <option value="Ride">
                                    Ride problem
                                </option>

                                <option value="Parcel">
                                    Parcel delivery problem
                                </option>

                                <option value="GPS">
                                    GPS / Location problem
                                </option>

                                <option value="Account">
                                    Account problem
                                </option>

                                <option value="Driver">
                                    Driver-related problem
                                </option>

                                <option value="Technical">
                                    Technical problem
                                </option>

                                <option value="Other">
                                    Other
                                </option>

                            </select>

                        </div>

                        <div class="form-group">

                            <label for="problem_description">
                                Describe the problem
                            </label>

                            <textarea
                                name="problem_description"
                                id="problem_description"
                                placeholder="Please describe what happened..."
                                required
                            ></textarea>

                        </div>

                        <button
                            type="submit"
                            class="submit-button"
                        >
                            Submit Report
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script>

document.querySelectorAll('.expand-header').forEach(function(button) {

    button.addEventListener('click', function() {

        const content = this.nextElementSibling;
        const isOpen = this.classList.contains('active');

        document.querySelectorAll('.expand-header').forEach(function(otherButton) {

            otherButton.classList.remove('active');

            const otherContent = otherButton.nextElementSibling;

            otherContent.style.maxHeight = null;
            otherContent.classList.remove('open');

        });

        if (!isOpen) {

            this.classList.add('active');

            content.classList.add('open');

            content.style.maxHeight =
                content.scrollHeight + "px";

        }

    });

});

document.querySelectorAll('.expandable-option').forEach(function(option) {

    option.addEventListener('click', function() {

        const subContent =
            this.querySelector('.sub-content');

        const arrow =
            this.querySelector('.help-option-arrow');

        const isOpen =
            subContent.classList.contains('open');

        document.querySelectorAll('.expandable-option').forEach(function(other) {

            if (other !== option) {

                const otherContent =
                    other.querySelector('.sub-content');

                const otherArrow =
                    other.querySelector('.help-option-arrow');

                otherContent.classList.remove('open');
                otherContent.style.maxHeight = null;
                other.classList.remove('active');

                if (otherArrow) {
                    otherArrow.textContent = '+';
                }

            }

        });

        if (!isOpen) {

            subContent.classList.add('open');

            subContent.style.maxHeight =
                subContent.scrollHeight + "px";

            arrow.textContent = '−';

            this.classList.add('active');

        } else {

            subContent.classList.remove('open');

            subContent.style.maxHeight = null;

            arrow.textContent = '+';

            this.classList.remove('active');

        }

        const mainContent =
            this.closest('.expand-content');

        if (mainContent) {

            setTimeout(function() {

                mainContent.style.maxHeight =
                    mainContent.scrollHeight + "px";

            }, 50);

        }

    });

});

document.querySelectorAll('.faq-question').forEach(function(question) {

    question.addEventListener('click', function() {

        const answer =
            this.nextElementSibling;

        const isOpen =
            this.classList.contains('active');

        document.querySelectorAll('.faq-question').forEach(function(other) {

            if (other !== question) {

                other.classList.remove('active');

                const otherAnswer =
                    other.nextElementSibling;

                otherAnswer.style.maxHeight = null;

            }

        });

        if (!isOpen) {

            this.classList.add('active');

            answer.style.maxHeight =
                answer.scrollHeight + "px";

        } else {

            this.classList.remove('active');

            answer.style.maxHeight = null;

        }

        const mainContent =
            this.closest('.expand-content');

        if (mainContent) {

            setTimeout(function() {

                mainContent.style.maxHeight =
                    mainContent.scrollHeight + "px";

            }, 50);

        }

    });

});

const reportForm =
    document.querySelector('.help-card:last-child form');

if (reportForm) {

    reportForm.addEventListener('submit', function(event) {

        event.preventDefault();

        alert(
            "Your report has been recorded for demonstration purposes. " +
            "A support database can be connected to this form later."
        );

    });

}

</script>

