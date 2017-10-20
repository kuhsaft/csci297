<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Assignment 5</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="calendar.css">
</head>
<body>
<?php
function isWeekend(DateTime $dateTime): bool
{
    $weekday = $dateTime->format("w");
    if ($weekday === "0" || $weekday === "6")
    {
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

    if ($diff->days == 1 || $diff->days >= 21) {
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

function printForm(array $data)
{
    // Calendar Header
    print <<<HTML
<form method="post">
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

                    $timeDisabled = (array_key_exists($day, $data) && in_array($time, $data[$day])) ? "disabled" : "";

                    print "<div class='time-slot ${timeDisabled}'>";
                    print "<input type='checkbox' id='${day}T${time}' name='${day}[]' value='${time}' ${timeDisabled}/>";
                    print "<label for='${day}T${time}'>${label}</label>";
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
<button class="btn btn-success btn-block" type="submit">Add Time Slots</button>
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

function printDatesAdded(array $datesAdded)
{
    foreach ($datesAdded as $date => $times) {
        print "<div class='alert alert-success' role='alert'><strong>${date}</strong><br>";

        $message = '<strong>'.implode("</strong>, <strong>", $times).'</strong>';
        print "Added: ${message}";

        print '</div>';
    }
}

function handlePost(array $data): array
{
    $errors = [];
    $dateErrors = [];
    $datesAdded = [];

    if (empty($_POST)) {
        $errors[] = 'You must select a time.';
    }

    // Check if each day is valid
    foreach ($_POST as $day => $times) {
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

            $timesAlreadyAdded = [];

            // Check if each time for each day is valid
            foreach ($times as $time) {
                $datetime = DateTime::createFromFormat(DateTime::ISO8601, "${day}T${time}");
                if ($datetime === false) {
                    $dateErrors[$monthDay][] = 'Invalid time: '.$time;
                } elseif (!isTimeValid($datetime)) {
                    $dateErrors[$monthDay][] = 'Invalid time: '.$datetime->format("h:i A");
                } else {
                    // Date does not exist or time not added
                    if (array_key_exists($day, $data) && in_array($time, $data[$day])) {
                        $timesAlreadyAdded[] = $datetime->format("h:i A");
                    } else {
                        $data[$day][] = $time; // Add time
                        $datesAdded[$monthDay][] = $datetime->format("h:i A");
                    }
                }
            }

            if (!empty($timesAlreadyAdded)) {
                $message = '<strong>'.implode("</strong>, <strong>", $timesAlreadyAdded).'</strong>';
                $dateErrors[$monthDay][] = "Already added: ${message}";
            }
        }
    }

    printErrors($errors);
    printDateErrors($dateErrors);
    printDatesAdded($datesAdded);

    return $data;
}

?>
<div class="container">
    <h2 align="center" class="title">Add New Available Times for Advising</h2>
    <?php
    $file = sys_get_temp_dir()."/csci297_nguyenp3_hw05.dat";
    if (!file_exists($file)) { // Create file if does not exist
        if (file_put_contents($file, '') === false) {
            die("Error: cannot read time slots.");
        }
    }

    $data = json_decode(file_get_contents($file), true);
    if ($data == null) { // If file cannot be decoded
        $data = [];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = handlePost($data);
        file_put_contents($file, json_encode($data));
        printForm($data);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        printForm($data);
    } else {
        http_response_code(500);
        echo "<h1>HTTP METHOD NOT SUPPORTED</h1>";
    }
    ?>
</div>
</body>
</html>
