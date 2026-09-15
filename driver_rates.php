<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

/* =========================================================
   DRIVER SESSION
   ========================================================= */

$driverID = $_SESSION['driver_id'] ?? null;

/* =========================================================
   DRIVER INFORMATION
   ========================================================= */

$driverName = "Driver";

if ($driverID !== null) {

    $driverStmt = $conn->prepare("
        SELECT DriverFname, DriverLname
        FROM driver
        WHERE DriverID = ?
    ");

    if ($driverStmt) {

        $driverStmt->bind_param("i", $driverID);
        $driverStmt->execute();

        $driverResult = $driverStmt->get_result();

        if ($driverResult && $driverResult->num_rows > 0) {

            $driver = $driverResult->fetch_assoc();

            $driverName = trim(
                ($driver['DriverFname'] ?? '') . ' ' .
                ($driver['DriverLname'] ?? '')
            );

        }

        $driverStmt->close();
    }
}

/* =========================================================
   RATING VARIABLES
   ========================================================= */

$averageRating = 0;
$totalRatings = 0;

$ratingBreakdown = [
    5 => 0,
    4 => 0,
    3 => 0,
    2 => 0,
    1 => 0
];

$reviews = [];

/* =========================================================
   CHECK IF RATING TABLE EXISTS
   ========================================================= */

$ratingTableExists = false;

$tableCheck = $conn->query("
    SHOW TABLES LIKE 'driver_ratings'
");

if ($tableCheck && $tableCheck->num_rows > 0) {
    $ratingTableExists = true;
}

/* =========================================================
   OVERALL RATING
   ========================================================= */

if ($ratingTableExists && $driverID !== null) {

    $averageStmt = $conn->prepare("
        SELECT
            AVG(rating) AS average_rating,
            COUNT(*) AS total_ratings
        FROM driver_ratings
        WHERE DriverID = ?
    ");

    if ($averageStmt) {

        $averageStmt->bind_param("i", $driverID);
        $averageStmt->execute();

        $averageResult = $averageStmt->get_result();

        if ($averageResult) {

            $averageData = $averageResult->fetch_assoc();

            $averageRating = (float)(
                $averageData['average_rating'] ?? 0
            );

            $totalRatings = (int)(
                $averageData['total_ratings'] ?? 0
            );
        }

        $averageStmt->close();
    }

    /* =====================================================
       RATING BREAKDOWN
       ===================================================== */

    $breakdownStmt = $conn->prepare("
        SELECT
            rating,
            COUNT(*) AS rating_count
        FROM driver_ratings
        WHERE DriverID = ?
        GROUP BY rating
        ORDER BY rating DESC
    ");

    if ($breakdownStmt) {

        $breakdownStmt->bind_param("i", $driverID);
        $breakdownStmt->execute();

        $breakdownResult = $breakdownStmt->get_result();

        if ($breakdownResult) {

            while ($row = $breakdownResult->fetch_assoc()) {

                $ratingValue = (int)$row['rating'];
                $ratingCount = (int)$row['rating_count'];

                if (
                    $ratingValue >= 1 &&
                    $ratingValue <= 5
                ) {
                    $ratingBreakdown[$ratingValue] = $ratingCount;
                }
            }
        }

        $breakdownStmt->close();
    }

    /* =====================================================
       RECENT REVIEWS
       ===================================================== */

    $reviewStmt = $conn->prepare("
        SELECT
            dr.rating,
            dr.comment,
            dr.rated_at,
            dr.studentID,
            dr.staff_id,
            dr.passenger_type,
            s.StudentFname,
            s.StudentLname,
            st.staff_name,
            st.surname
        FROM driver_ratings dr
        LEFT JOIN student s
            ON dr.studentID = s.studentID
        LEFT JOIN staff st
            ON dr.staff_id = st.staff_id
        WHERE dr.DriverID = ?
        ORDER BY dr.rated_at DESC
        LIMIT 10
    ");

    if ($reviewStmt) {

        $reviewStmt->bind_param("i", $driverID);
        $reviewStmt->execute();

        $reviewResult = $reviewStmt->get_result();

        if ($reviewResult) {

            while ($row = $reviewResult->fetch_assoc()) {
                $reviews[] = $row;
            }
        }

        $reviewStmt->close();
    }
}

/* =========================================================
   HELPER FUNCTIONS
   ========================================================= */

function getReviewerName($review)
{
    $passengerType = $review['passenger_type'] ?? 'student';

    /* STUDENT */

    if ($passengerType === 'student') {

        $firstName = trim(
            $review['StudentFname'] ?? ''
        );

        $lastName = trim(
            $review['StudentLname'] ?? ''
        );

        $name = trim(
            $firstName . ' ' . $lastName
        );

        if ($name !== '') {
            return $name;
        }
    }

    /* STAFF */

    if ($passengerType === 'staff') {

        $firstName = trim(
            $review['staff_name'] ?? ''
        );

        $lastName = trim(
            $review['surname'] ?? ''
        );

        $name = trim(
            $firstName . ' ' . $lastName
        );

        if ($name !== '') {
            return $name;
        }
    }

    return 'Passenger';
}


function getInitials($name)
{
    $name = trim($name);

    if ($name === '') {
        return 'P';
    }

    $parts = preg_split('/\s+/', $name);

    if (count($parts) === 1) {

        return strtoupper(
            substr($parts[0], 0, 1)
        );
    }

    return strtoupper(
        substr($parts[0], 0, 1) .
        substr($parts[count($parts) - 1], 0, 1)
    );
}


function formatReviewDate($date)
{
    if (empty($date)) {
        return '';
    }

    $timestamp = strtotime($date);

    if (!$timestamp) {
        return htmlspecialchars($date);
    }

    return date('d M Y', $timestamp);
}

/* =========================================================
   PAGE
   ========================================================= */

$pageTitle = "My Rates";

include 'header.php';

?>

<style>

.rates-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 30px 20px 50px;
}

/* =========================================================
   BACK BUTTON
   ========================================================= */

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #0c2d4d;
    color: #ffffff;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 22px;
    transition: background 0.2s ease;
}

.back-button:hover {
    background: #185fa5;
    color: #ffffff;
}

.back-arrow {
    font-size: 17px;
    line-height: 1;
}

/* =========================================================
   PAGE HEADER
   ========================================================= */

.rates-header {
    margin-bottom: 25px;
}

.rates-header h1 {
    margin: 0 0 6px;
    font-size: 30px;
    color: #0b1f3a;
}

.rates-header p {
    margin: 0;
    color: #6b7280;
    font-size: 15px;
}

/* =========================================================
   RATING SUMMARY
   ========================================================= */

.rating-summary {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 20px;
    margin-bottom: 25px;
}

.rating-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 25px;
    box-shadow: 0 4px 14px rgba(11, 31, 58, 0.05);
}

/* =========================================================
   OVERALL RATING
   ========================================================= */

.overall-rating {
    text-align: center;
}

.overall-rating h2 {
    margin: 0 0 10px;
    color: #0b1f3a;
    font-size: 16px;
}

.rating-number {
    font-size: 52px;
    font-weight: 700;
    color: #0b1f3a;
    line-height: 1;
    margin-bottom: 10px;
}

.stars {
    color: #d4a72c;
    font-size: 22px;
    letter-spacing: 2px;
    margin-bottom: 8px;
}

.rating-count {
    color: #6b7280;
    font-size: 14px;
}

/* =========================================================
   RATING BREAKDOWN
   ========================================================= */

.breakdown-card h2 {
    margin: 0 0 20px;
    color: #0b1f3a;
    font-size: 18px;
}

.breakdown-row {
    display: grid;
    grid-template-columns: 55px 1fr 40px;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.breakdown-label {
    font-size: 14px;
    color: #374151;
}

.progress {
    height: 8px;
    background: #edf0f3;
    border-radius: 20px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: #d4a72c;
    border-radius: 20px;
}

.breakdown-count {
    font-size: 13px;
    color: #6b7280;
    text-align: right;
}

/* =========================================================
   FEEDBACK CARD
   ========================================================= */

.feedback-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(11, 31, 58, 0.05);
    overflow: hidden;
}

.feedback-header {
    padding: 22px 25px;
    border-bottom: 1px solid #e5e7eb;
}

.feedback-header h2 {
    margin: 0 0 5px;
    color: #0b1f3a;
    font-size: 20px;
}

.feedback-header p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

/* =========================================================
   REVIEW
   ========================================================= */

.review {
    padding: 22px 25px;
    border-bottom: 1px solid #e5e7eb;
}

.review:last-child {
    border-bottom: none;
}

.review-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.reviewer {
    display: flex;
    align-items: center;
    gap: 12px;
}

.reviewer-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #0b1f3a;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
}

.reviewer-name {
    color: #172033;
    font-weight: 600;
    font-size: 15px;
}

.review-date {
    color: #9ca3af;
    font-size: 13px;
    margin-top: 2px;
}

.review-rating {
    color: #d4a72c;
    font-size: 16px;
    letter-spacing: 1px;
}

.review-comment {
    margin: 14px 0 0 54px;
    color: #4b5563;
    font-size: 14px;
    line-height: 1.6;
}

/* =========================================================
   EMPTY STATE
   ========================================================= */

.empty-feedback {
    padding: 55px 25px;
    text-align: center;
}

.empty-feedback h3 {
    margin: 0 0 8px;
    color: #0b1f3a;
    font-size: 18px;
}

.empty-feedback p {
    max-width: 520px;
    margin: 0 auto;
    color: #6b7280;
    font-size: 14px;
    line-height: 1.6;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 750px) {

    .rating-summary {
        grid-template-columns: 1fr;
    }

    .rates-page {
        padding: 25px 15px 40px;
    }

    .rates-header h1 {
        font-size: 26px;
    }

    .review-top {
        align-items: flex-start;
    }

    .review-comment {
        margin-left: 0;
    }

    .review-rating {
        font-size: 14px;
    }
}

</style>


<div class="rates-page">

    <!-- BACK BUTTON -->

    <a href="driver_dashboard.php" class="back-button">

        <span class="back-arrow">
            ←
        </span>

        <span>
            Back to Driver Dashboard
        </span>

    </a>


    <!-- PAGE HEADER -->

    <div class="rates-header">

        <h1>
            My Rates
        </h1>

        <p>
            View your passenger ratings and feedback.
        </p>

    </div>


    <!-- RATING SUMMARY -->

    <div class="rating-summary">


        <!-- OVERALL RATING -->

        <div class="rating-card overall-rating">

            <h2>
                Overall Rating
            </h2>

            <div class="rating-number">

                <?= number_format($averageRating, 1) ?>

            </div>


            <div class="stars">

                <?php

                $roundedRating = (int)round($averageRating);

                if ($roundedRating < 0) {
                    $roundedRating = 0;
                }

                if ($roundedRating > 5) {
                    $roundedRating = 5;
                }

                for ($i = 1; $i <= 5; $i++) {

                    if ($i <= $roundedRating) {
                        echo '★';
                    } else {
                        echo '☆';
                    }

                }

                ?>

            </div>


            <div class="rating-count">

                <?= $totalRatings ?>

                <?= $totalRatings == 1 ? 'rating' : 'ratings' ?>

            </div>

        </div>


        <!-- RATING BREAKDOWN -->

        <div class="rating-card breakdown-card">

            <h2>
                Rating Breakdown
            </h2>

            <?php for ($rating = 5; $rating >= 1; $rating--): ?>

                <?php

                $count = $ratingBreakdown[$rating] ?? 0;

                $percentage = 0;

                if ($totalRatings > 0) {

                    $percentage =
                        ($count / $totalRatings) * 100;

                }

                ?>

                <div class="breakdown-row">

                    <div class="breakdown-label">

                        <?= $rating ?> star

                    </div>

                    <div class="progress">

                        <div
                            class="progress-bar"
                            style="width: <?= min(100, max(0, $percentage)) ?>%;"
                        ></div>

                    </div>

                    <div class="breakdown-count">

                        <?= $count ?>

                    </div>

                </div>

            <?php endfor; ?>

        </div>

    </div>


    <!-- PASSENGER FEEDBACK -->

    <div class="feedback-card">


        <div class="feedback-header">

            <h2>
                Passenger Feedback
            </h2>

            <p>
                Recent ratings and comments from passengers.
            </p>

        </div>


        <?php if (empty($reviews)): ?>


            <!-- EMPTY STATE -->

            <div class="empty-feedback">

                <h3>
                    No passenger feedback yet
                </h3>

                <p>
                    Once passengers rate your completed rides,
                    their ratings and comments will appear here.
                </p>

            </div>


        <?php else: ?>


            <!-- REVIEWS -->

            <?php foreach ($reviews as $review): ?>

                <?php

                $reviewerName = getReviewerName($review);

                $initials = getInitials($reviewerName);

                $rating = (int)(
                    $review['rating'] ?? 0
                );

                if ($rating < 0) {
                    $rating = 0;
                }

                if ($rating > 5) {
                    $rating = 5;
                }

                $comment = trim(
                    $review['comment'] ?? ''
                );

                ?>


                <div class="review">


                    <div class="review-top">


                        <!-- REVIEWER -->

                        <div class="reviewer">

                            <div class="reviewer-avatar">

                                <?= htmlspecialchars($initials) ?>

                            </div>


                            <div>

                                <div class="reviewer-name">

                                    <?= htmlspecialchars($reviewerName) ?>

                                </div>


                                <div class="review-date">

                                    <?= formatReviewDate(
                                        $review['rated_at'] ?? ''
                                    ) ?>

                                </div>

                            </div>

                        </div>


                        <!-- REVIEW RATING -->

                        <div class="review-rating">

                            <?php

                            for ($i = 1; $i <= 5; $i++) {

                                if ($i <= $rating) {
                                    echo '★';
                                } else {
                                    echo '☆';
                                }

                            }

                            ?>

                        </div>

                    </div>


                    <!-- COMMENT -->

                    <?php if ($comment !== ''): ?>

                        <div class="review-comment">

                            <?= nl2br(
                                htmlspecialchars($comment)
                            ) ?>

                        </div>

                    <?php endif; ?>


                </div>

            <?php endforeach; ?>


        <?php endif; ?>


    </div>

</div>

</main>

</body>

</html>