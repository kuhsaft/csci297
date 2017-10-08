<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Assignment 5</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">

    <style type="text/css">
        body {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .title {
            margin-bottom: 2rem;
            font-weight: 600;
        }

        section {
            margin-bottom: 3rem;
        }

        section h3 {
            margin-bottom: 1rem;
        }

        table.calendar tbody tr {
            height: 6em;
        }

        table.calendar thead tr th {
            width: 10em;
        }

        table.calendar tbody tr td {
            padding: 0;
            height: 100%;
        }

        table.calendar tbody tr td.disabled {
            color: #555;
            background: #888;
            pointer-events: none;
        }

        table.calendar tbody tr td.today {
            font-weight: 600;
            color: #000;
        }

        table.calendar tbody tr td:not(:last-child) {
            border-right: 1px solid #e9ecef;
        }

        table.calendar tbody tr td input[type=radio] {
            display: none;
        }

        table.calendar tbody tr td label {
            padding: .75rem;
            display: block;
            width: 100%;
            height: 100%;
        }

        table.calendar tbody tr td:not(.disabled) input[type="radio"]:not(:checked) ~ label:hover {
            cursor: pointer;
            background: #eee;
            font-weight: 600;
        }

        table.calendar tbody tr td:not(.disabled) input[type="radio"]:checked ~ label {
            background: #FFEB3B;
            font-weight: 800;
        }
    </style>
</head>
<body>
<?php
function isDateValid(DateTime $dateTime): bool
{
    $weekday = $dateTime->format("w");
    if ($weekday === "0" || $weekday === "6") // Can't be weekend
    {
        return false;
    }

    $diff = (new DateTime())->diff($dateTime);
    if ($diff->format("%R") === "-") // Can't be in past
    {
        return false;
    }

    if (intval($diff->format("%a") > 21)) {
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

function printForm()
{
    // Calendar Header
    print <<<HTML
<form method="post">
        <section>
            <h3>Select a Day</h3>
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
    $date->sub(new DateInterval("P".date("w")."D")); // Get the first day of the week

    $numWeeks = (date("w") === "0") ? 3 : 4; // Only show 3 weeks if current day is Sunday

    for ($i = 0; $i < $numWeeks; ++$i) {
        print "<tr>";
        for ($j = 0; $j < 7; ++$j) {
            $isDisabled = isDateValid($date) ? "" : "disabled";
            $isToday = ($date->format("Y-m-d") === date("Y-m-d")) ? "today" : "";
            $timestamp = $date->format("Y-m-d");

            print "<td class=\"${isDisabled} ${isToday}\">";
            print "<input type=\"radio\" id=\"${timestamp}\" name=\"date\" value=\"${timestamp}\"/>";
            print "<label for=\"${timestamp}\">".$date->format("m/d")."</label>";
            print "</td>";
            $date->add(new DateInterval("P1D"));
        }
        print "</tr>";
    }

    print <<<HTML
</tbody>
</table>
</section>
<section>
    <h3>Pick Times</h3>
    <div class="row">
        <div class="col">
HTML;

    $time = new DateTime();
    $time->setTime(8, 0);

    // 8:00 AM to 11:30 AM
    for ($i = 0; $i < 8; ++$i) {
        $label = $time->format("h:i A");
        $timestamp = $time->format("H:i:00");

        print "<div class=\"form-check\">";
        print "<label class=\"form-check-label\">";
        print "<input class=\"form-check-input\" type=\"checkbox\" name=\"time[]\" value=\"${timestamp}\">";
        print "&nbsp;${label}";
        print "</label>";
        print "</div>";

        $time->add(new DateInterval("P0DT0H30M")); // Add 30 minutes
    }

    print <<<HTML
        </div>
        <div class="col">
HTML;

    // 12:00 PM to 6:00 PM
    for ($i = 0; $i < 13; ++$i) {
        $label = $time->format("h:i A");
        $timestamp = $time->format("H:i:00");

        print "<div class=\"form-check\">";
        print "<label class=\"form-check-label\">";
        print "<input class=\"form-check-input\" type=\"checkbox\" name=\"time[]\" value=\"${timestamp}\">";
        print "&nbsp;${label}";
        print "</label>";
        print "</div>";

        $time->add(new DateInterval("P0DT0H30M")); // Add 30 minutes
    }

    print <<<HTML
        </div>
    </div>
</section>
<section>
    <button class="btn btn-success btn-block" type="submit">Add Time Slots</button>
</section>
</form>
HTML;
}

function printErrors(array $errors)
{
    foreach ($errors as $error) {
        print "<div class=\"alert alert-danger\" role=\"alert\">${error}</div>";
    }
}

function printTimesAlreadyAdded(DateTime $dateTime, array $times)
{
    $numAdded = count($times);
    if ($numAdded == 0) {
        return;
    }

    if ($numAdded == 1) {
        $message = "Time already added: ";
    } else {
        $message = "Times already added: ";
    }

    $date = $dateTime->format("m/d");
    $message .= implode(", ", $times);

    print "<div class=\"alert alert-danger\" role=\"alert\"><strong>${date}</strong><br>${message}.</div>";
}

function printTimesAdded(DateTime $dateTime, array $times)
{
    $numAdded = count($times);
    if ($numAdded == 0) {
        return;
    }

    if ($numAdded == 1) {
        $message = "Time added: ";
    } else {
        $message = "Times added: ";
    }

    $date = $dateTime->format("m/d");
    $message .= implode(", ", $times);

    print "<div class=\"alert alert-success\" role=\"alert\"><strong>${date}</strong><br>${message}.</div>";
}

function handlePost()
{
    $errors = [];

    // Guards
    // Check date input
    $dateTime = new DateTime($_POST['date']);
    if (is_null($_POST['date'])) {
        $errors[] = "You must select a date.";
    } elseif (!isDateValid($dateTime)) {
        $errors[] = "Invalid date: ${_POST['date']}";
    }

    // Check time input
    if (is_null($_POST['time']) || empty($_POST['time'])) {
        $errors[] = "You must select at least one time.";
    } else {
        foreach ($_POST['time'] as $time) {
            // Time value cannot be blank
            if ($time === "") {
                $errors[] = "Invalid time.";
                continue;
            }

            // Check if valid time format
            $timeDateTime = DateTime::createFromFormat("Y-m-d H:i:s", "1970-01-01 ${time}");
            if ($timeDateTime === false) {
                $errors[] = "Invalid time format: ${time}.";
                continue;
            }

            // Check if valid time
            $formattedTime = $timeDateTime->format("h:i A");
            if (!isTimeValid($timeDateTime)) {
                $errors[] = "Invalid time: ${formattedTime}.";
            }
        }
    }

    // Do not add values if there is an error
    if (!empty($errors)) {
        printErrors($errors);

        return;
    }

    // Add dates to file as JSON
    $file = sys_get_temp_dir()."/csci297_nguyenp3_hw05.dat";
    $json = json_decode(file_get_contents($file), true);

    $formatTime = function (string $date, string $time): string {
        $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", "${date} ${time}");

        return $dateTime->format("h:i A");
    };

    $timesAlreadyAdded = [];
    $timesAdded = [];

    // Date not in file
    if (is_null($json[$_POST['date']])) {
        // Add date with all times
        $json[$_POST['date']] = $_POST['time'];

        foreach ($_POST['time'] as $time) {
            $timesAdded[] = $formatTime($_POST['date'], $time);
        }
    } else {
        foreach ($_POST['time'] as $time) {
            $formattedTime = $formatTime($_POST['date'], $time);

            if (in_array($time, $json[$_POST['date']])) { // Time already added for date
                $timesAlreadyAdded[] = $formattedTime;
            } else {
                $json[$_POST['date']][] = $time;
                $timesAdded[] = $formattedTime;
            }
        }
    }

    printTimesAlreadyAdded($dateTime, $timesAlreadyAdded);
    printTimesAdded($dateTime, $timesAdded);

    file_put_contents($file, json_encode($json));
}

?>
<div class="container">
    <h2 align="center" class="title">Add New Available Times for Advising</h2>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handlePost();
        printForm();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        printForm();
    } else {
        http_response_code(500);
        echo "<h1>HTTP METHOD NOT SUPPORTED</h1>";
    }
    ?>
</div>
</body>
</html>
