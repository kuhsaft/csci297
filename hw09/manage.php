<?php
ini_set('display_errors', 1);
include_once('config.inc.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Manage Appointment Times</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="calendar.css">
</head>
<body>
<?php
function getAppointmentTimes(PDO $dbConn): array
{
    $result = [];

    $stmt = $dbConn->query("SELECT date, time, name FROM appointments");
    while ($row = $stmt->fetch()) {
        $result[$row['date']][$row['time']] = $row['name'];
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
    if ($diff->format("%R") === "" || $diff->format("%R") === "-") { // Can't be in past
        return false;
    } elseif ($diff->days > 21) {
        return false;
    }

    return true;
}

function isTimeValid(DateTime $dateTime): bool
{
    $hour = intval($dateTime->format("H"));
    if ($hour >= 8 && $hour <= 18) // 8 AM to 6 PM
    {
        return true;
    }

    return false;
}

function printForm(PDO $dbConn)
{
    $data = getAppointmentTimes($dbConn);

    // Calendar Header
    print <<<HTML
<form method="post" onsubmit="return confirmForm();">
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
    $date->sub(new DateInterval("P".date("w")."D")); // Get the first day of the week

    $numWeeks = (date("w") === "0") ? 3 : 4; // Only show 3 weeks if current day is Sunday

    for ($i = 0; $i < $numWeeks; ++$i) { // For each week
        print "<tr>";
        for ($j = 0; $j < 7; ++$j) { // For each day in week
            $isDisabled = !isDateValid($date) ? "disabled" : "";
            $isToday = ($date->format("Y-m-d") === date("Y-m-d")) ? "today" : "";

            print "<td class=\"${isDisabled} ${isToday}\">";
            print "<label class='month'>".$date->format("m/d")."</label>"; // Month label

            if (!isWeekend($date)) { // Only show times if valid day
                $date->setTime(8, 0, 0);

                // 8:00 AM to 6:00 PM
                for ($k = 0; $k < 21; ++$k) {
                    $day = $date->format("Y-m-d");
                    $timeOffset = $date->format("O");
                    $time = $date->format("H:i:s");

                    $timeEnabled = (array_key_exists($day, $data) && array_key_exists(
                            $time,
                            $data[$day]
                        )) ? true : false;
                    $time .= $timeOffset;

                    $timeSlotClass = 'time-slot'.($timeEnabled ? ' enabled' : '');
                    $name = $timeEnabled ? "remove[${day}][]" : "insert[${day}][]";
                    $label = $date->format("h:i A");
                    $id = $date->format("F jS") . " at " . $label;

                    print "<div class='$timeSlotClass'>";
                    print "<input type='checkbox' id='$id' name='$name' value='$time' hidden/>";
                    print "<label for='$id'>$label</label>";
                    print "</div>";

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
<button class="btn btn-success btn-block" type="submit">Submit</button>
</form>
HTML;
}

function printErrors(array $errors)
{
    foreach ($errors as $error) {
        print "<div class='alert alert-danger' role='alert'>${error}</div>";
    }
}

function printDateErrors(array $dateErrors)
{
    foreach ($dateErrors as $date => $errors) {
        print "<div class='alert alert-danger' role='alert'><strong>${date}</strong>";

        foreach ($errors as $error) {
            print "<br>${error}";
        }

        print '</div>';
    }
}

function insertAppointments(PDO $dbConn, array $times)
{
    try {
        $dbConn->beginTransaction();
        $stmt = $dbConn->prepare("INSERT INTO appointments (date, time) VALUES (:date, :time)");

        foreach ($times as $datetime) {
            $day = $datetime->format('Y-m-d');
            $time = $datetime->format('H:i:s');
            $stmt->execute(['date' => $day, 'time' => $time]);
        }
        $dbConn->commit();
    } catch (Exception $e) {
        $dbConn->rollback();
        throw $e;
    }
}

function removeAppointments(PDO $dbConn, array $times)
{
    $qMarks = implode(',', array_fill(0, count($times), '(?, ?)'));

    try {
        $stmt = $dbConn->prepare("DELETE FROM appointments WHERE (date, time) IN ($qMarks)");

        $i = 0;
        foreach ($times as $datetime) {
            $day = $datetime->format('Y-m-d');
            $time = $datetime->format('H:i:s');
            $stmt->bindValue(++$i, $day);
            $stmt->bindValue(++$i, $time);
        }
        $stmt->execute();
    } catch (Exception $e) {
        throw $e;
    }
}

function handlePost(PDO $dbConn)
{
    $errors = [];
    $dateErrors = [];
    $datesToAdd = [];
    $datesToRemove = [];

    if (empty($_POST)) {
        $errors[] = 'You must select a time.';
    }

    // Check if each day is valid
    foreach ($_POST as $operation => $date) {
        foreach ($date as $day => $times) {

            $date = DateTime::createFromFormat('Y-m-d', $day);
            $date->setTime(0, 0, 0);

            if ($date === false) {
                $errors[] = 'Invalid day: '.$day.'.';
            } elseif (!isDateValid($date)) {
                $errors[] = 'Invalid day: '.$date->format("m/d").'.';
            } else {
                $monthDay = $date->format("m/d");

                if (is_null($times) || empty($times)) {
                    $errors[] = 'You must select a time.';
                }

                // Check if each time for each day is valid
                foreach ($times as $time) {
                    $datetime = DateTime::createFromFormat(DateTime::ISO8601, "${day}T${time}");
                    if ($datetime === false) {
                        $dateErrors[$monthDay][] = 'Invalid time: '.$time;
                    } elseif (!isTimeValid($datetime)) {
                        $dateErrors[$monthDay][] = 'Invalid time: '.$datetime->format("h:i A");
                    } else {
                        if ($operation == 'insert') {
                            $datesToAdd[] = $datetime;
                        } elseif ($operation == 'remove') {
                            $datesToRemove[] = $datetime;
                        }
                    }
                }
            }
        }
    }

    if (!empty($datesToAdd)) {
        insertAppointments($dbConn, $datesToAdd);
    }

    if (!empty($datesToRemove)) {
        removeAppointments($dbConn, $datesToRemove);
    }

    printErrors($errors);
    printDateErrors($dateErrors);
}

?>

<div class="container">
    <h2 align="center" class="title">Manage Available Times for Advising</h2>
    <h4 align="center" class="subtitle">Select Times to Add/Remove</h4>
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
<script type="application/javascript" src="manage.js"></script>
</body>
</html>
