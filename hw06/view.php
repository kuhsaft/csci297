<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Make an Appointment</title>

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
    if ($diff->format("%R") === "" || $diff->format("%R") === "-") // Can't be in past
    {
        return false;
    }

    if ($diff->days == 0 || $diff->days > 21) {
        return false;
    }

    return true;
}

function printCalendar(array $data)
{
    // Calendar Header
    print <<<HTML
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
                    $label = $date->format("h:i A");
                    $day = $date->format("Y-m-d");
                    $time = $date->format("H:i:sO");

                    if (
                        array_key_exists($day, $data) &&
                        array_key_exists($time, $data[$day]) &&
                        !is_null($data[$day][$time])
                    ) {
                        $name = $data[$day][$time];

                        print "<div class='time-slot'>";
                        print "<label for='${day}T${time}'>${label}<br><strong>${name}</strong></label>";
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
<div class="container">
    <h2 align="center" class="title">Upcoming Appointments</h2>
    <?php
    $file = sys_get_temp_dir()."/csci297_nguyenp3_hw06.dat";
    if (!file_exists($file)) { // Create file if does not exist
        if (file_put_contents($file, '') === false) {
            die("Error: cannot read time slots.");
        }
    }

    $times = json_decode(file_get_contents($file), true);
    if ($times == null) { // If file cannot be decoded
        $times = [];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        printCalendar($times);
    } else {
        http_response_code(500);
        echo "<h1>HTTP METHOD NOT SUPPORTED</h1>";
    }
    ?>
</div>
</body>
</html>
