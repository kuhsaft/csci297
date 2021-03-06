<?php
ini_set('display_errors', 1);
include_once('config.inc.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Make an Appointment</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="../hw09/calendar.css">
</head>
<body>
<?php
function getAvailableTimes(PDO $dbConn): array
{
    $result = [];

    $query = "SELECT date, time FROM appointments WHERE DATE(CONCAT(date, ' ', time)) > NOW() AND email IS NULL";

    $stmt = $dbConn->query($query);
    while ($row = $stmt->fetch()) {
        $result[$row['date']][$row['time']] = null;
    }

    return $result;
}

function isWeekend(DateTime $dateTime): bool
{
    $weekday = $dateTime->format("w");
    if ($weekday === "0" || $weekday === "6") {
        return true;
    }

    return false;
}

function isDateValid(DateTime $dateTime): bool
{
    if (isWeekend($dateTime)) {
        return false;
    }

    $diff = (new DateTime())->diff($dateTime);
    if ($diff->format("%R") === "-") // Can't be in past
    {
        return false;
    }

    if ($diff->days > 21) {
        return false;
    }

    return true;
}

function printForm(PDO $dbConn)
{
    $data = getAvailableTimes($dbConn);

    // Calendar Header
    print <<<HTML
<h2 align="center" class="title">Make an Appointment</h2>
<form method="post">
    <h2>Select a time:</h2>
    <table class="table table-responsive calendar">
        <thead class="thead-inverse">
        <tr>
            <th>Sunday</th>
            <th>Monday</th>
            <th>Tuesday</th>
            <th>Wednesday</th>
            <th>Thursday</th>
            <th>Friday</th>
            <th>Saturday</th>
        </tr>
        </thead>
        <tbody>
HTML;

    // Calendar body
    $date = new DateTime();
    $date->setTime(0, 0, 0);
    $date->sub(new DateInterval("P" . date("w") . "D")); // Get the first day of the week

    $numWeeks = (date("w") === "0") ? 3 : 4; // Only show 3 weeks if current day is Sunday

    for ($i = 0; $i < $numWeeks; ++$i) { // For each week
        print "<tr>";
        for ($j = 0; $j < 7; ++$j) { // For each day in week
            $isDisabled = !isDateValid($date) ? "disabled" : "";
            $isToday = ($date->format("Y-m-d") === date("Y-m-d")) ? "today" : "";

            print "<td class=\"${isDisabled} ${isToday}\">";
            print "<label class='month'>" . $date->format("m/d") . "</label>"; // Month label

            if (!isWeekend($date)) { // Only show times if valid day
                $date->setTime(8, 0, 0);

                // 8:00 AM to 6:00 PM
                for ($k = 0; $k < 21; ++$k) {
                    $day = $date->format("Y-m-d");
                    $time = $date->format("H:i:s");

                    if (
                        array_key_exists($day, $data) &&
                        array_key_exists($time, $data[$day])
                    ) {
                        $value = "${day}T${time}" . $date->format("O");
                        $label = $date->format("h:i A");

                        print "<div class='time-slot'>";
                        print "<input type='radio' id='$value' name='datetime' value='$value' hidden/>";
                        print "<label for='$value'>$label</label>";
                        print "</div>";
                    }

                    $date->add(new DateInterval("P0DT0H30M")); // Add 30 minutes
                }
            }

            print "</td>";

            $date->add(new DateInterval("P1D"));
        }
        print "</tr>";
    }

    print <<<HTML
</tbody>
</table>
<hr>
<h2>Enter your information:</h2>
<div class="form-group">
    <label for="email">Email:</label>
    <input type="email" class="form-control" id="email" name="email" placeholder="student@winthrop.edu" required>
</div>
<div class="form-group">
    <label for="name">Name:</label>
    <input type="text" class="form-control" id="name" name='name' placeholder="Winthrop Student" required>
</div>
<button class="btn btn-success btn-block" type="submit">Submit</button>
</form>
HTML;
}

function printError(string $error)
{
    print "<div class='alert alert-danger' role='alert'>${error}</div>";
}

function printSuccess(DateTime $datetime)
{
    $datetimeFormatted = $datetime->format("D M jS h:i A");

    print "<div class='alert alert-success' role='alert'>";
    print "You have successfully signed up for ${datetimeFormatted}.";
    print '</div>';
}

function makeAppointment(PDO $dbConn, DateTime $dateTime, string $email, string $name)
{
    // Check if email already exists
    $email = strtolower($email);
    $query = "SELECT COUNT(*) FROM appointments WHERE DATE(CONCAT(date, ' ', time)) > NOW() AND email = ?";

    $stmt = $dbConn->prepare($query);
    $stmt->execute([$email]);
    if (intval($stmt->fetchColumn()) > 0) {
        printError('An appointment already exists with that email.');
        return;
    }

    // Try to create appointment
    $stmt = $dbConn->prepare("UPDATE appointments SET email = :email, name = :name WHERE date = :date AND time = :time");
    $day = $dateTime->format('Y-m-d');
    $time = $dateTime->format('H:i:s');
    $stmt->execute(['email' => $email, 'name' => $name, 'date' => $day, 'time' => $time]);

    if ($stmt->rowCount() == 0) {
        printError('The time is not valid: ' . $dateTime->format("D M jS h:i A") . '.');
    } else {
        printSuccess($dateTime);
    }
}

function handlePost(PDO $dbConn)
{
    $hasError = false;

    if (!array_key_exists('email', $_POST) ||
        empty($_POST['email']) ||
        !is_string($_POST['email'])
    ) {
        printError('You must enter your email.');
        $hasError = true;
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        printError('Invalid email format.');
        $hasError = true;
    } else {
        $email = $_POST['email'];
    }

    if (!array_key_exists('name', $_POST) ||
        empty($_POST['name']) ||
        !is_string($_POST['name'])
    ) {
        printError('You must enter your name.');
        $hasError = true;
    } else {
        $name = $_POST['name'];
    }

    if (!array_key_exists('datetime', $_POST)) {
        printError('You must select a time.');
        $hasError = true;
    }

    if (!$hasError) {
        $datetime = DateTime::createFromFormat(DateTime::ISO8601, $_POST['datetime']);

        if ($datetime === false) {
            printError('Invalid date and time: ' . $_POST['datetime'] . '.');
        } elseif (!isDateValid($datetime)) {
            printError('Invalid time: ' . $datetime->format("D M jS h:i A") . '.');
        } else {
            makeAppointment($dbConn, $datetime, $email, $name);
        }
    }
}

?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand" href="index.html">Scheduling</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class="nav-link" href="signup.php">Register <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="lookup.php">Lookup</a>
            </li>
        </ul>
    </div>
</nav>
<div class="container">
    <?php
    $dbConn = new PDO(
        "mysql:host={$config['dbhost']};dbname={$config['dbname']};charset=utf8mb4",
        $config['dbuser'],
        $config['dbpass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handlePost($dbConn);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
        printForm($dbConn);
    } else {
        http_response_code(500);
        echo "<h1>HTTP METHOD NOT SUPPORTED</h1>";
    }
    ?>
</div>

<script
        src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g="
        crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"
        integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js"
        integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ"
        crossorigin="anonymous"></script>
<script type="application/javascript" src="manage.js"></script>
</body>
</html>
