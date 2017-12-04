<?php
ini_set('display_errors', 1);
include_once('config.inc.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Upcoming Appointments</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="calendar.css">

    <style type="text/css">
        table.calendar td:not(.disabled) .time-slot > label {
            border-top: dotted 1px #000;
            border-bottom: dotted 1px #000;
        }
        table.calendar td:not(.disabled) .time-slot > label:hover {
            background: #eee;
        }
    </style>
</head>
<body>
<?php
function getAppointments(PDO $dbConn): array
{
    $result = [];

    $stmt = $dbConn->query("SELECT date, time, name FROM appointments WHERE name IS NOT NULL");
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
    if (isWeekend($dateTime))
    {
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

function printCalendar(PDO $dbConn)
{
    $data = getAppointments($dbConn);

    // Calendar Header
    print <<<HTML
<h2 align="center" class="title">Upcoming Appointments</h2>
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
                    $time = $date->format("H:i:s");

                    $id = "${day}T${time}";
                    $label = $date->format("h:i A");

                    if (
                        array_key_exists($day, $data) &&
                        array_key_exists($time, $data[$day]) &&
                        !is_null($data[$day][$time])
                    ) {
                        $name = $data[$day][$time];

                        print "<div class='time-slot'>";
                        print "<label for='$id'>$label<br><strong>${name}</strong></label>";
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
HTML;
}

?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand" href="index.html">Scheduling Admin</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="manage.php">Manage</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="view.php">View <span class="sr-only">(current)</span></a>
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

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        printCalendar($dbConn);
    } else {
        http_response_code(500);
        echo "<h1>HTTP METHOD NOT SUPPORTED</h1>";
    }
    ?>
</div>
</body>
</html>
