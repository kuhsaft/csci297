<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Make an Appointment</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="../hw05/calendar.css">
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
    if (isWeekend($dateTime)) {
        return false;
    }

    $diff = (new DateTime())->diff($dateTime);
    if ($diff->format("%R") === "" || $diff->format("%R") === "-") // Can't be in past
    {
        return false;
    }

    if ($diff->days > 21) {
        return false;
    }

    return true;
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

                    if (
                            array_key_exists($day, $data) &&
                            array_key_exists($time, $data[$day]) &&
                            is_null($data[$day][$time])
                    ) {
                        print "<div class='time-slot'>";
                        print "<input type='radio' id='${day}T${time}' name='datetime' value='${day}T${time}'/>";
                        print "<label for='${day}T${time}'>${label}</label>";
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
<div class="form-group">
    <label for="name">Name:</label>
    <input type="text" class="form-control" id="name" name='name' placeholder="Enter name" required>
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

function handlePost(array $times): array
{
    $hasError = false;

    if (!array_key_exists('name', $_POST) ||
        empty($_POST['name']) ||
        !is_string($_POST['name'])
    ) {
        printError('You must enter your name.');
        $hasError = true;
    }

    if (!array_key_exists('datetime', $_POST)) {
        printError('You must select a time.');
        $hasError = true;
    }

    if (!$hasError) {
        $datetime = DateTime::createFromFormat(DateTime::ISO8601, $_POST['datetime']);

        if ($datetime === false) {
            printError('Invalid date and time: '.$_POST['datetime'].'.');
        } else {
            $day = $datetime->format('Y-m-d');
            $time = $datetime->format('H:i:sO');


            if (!array_key_exists($day, $times)) {
                printError('Invalid day: '.$datetime->format("m/d").'.');
            } elseif (!array_key_exists($time, $times[$day])) {
                printError('Invalid time: '.$datetime->format("D M jS h:i A").'.');
            } elseif (!is_null($times[$day][$time])) {
                printError('Time already taken: '.$datetime->format("D M jS h:i A").'.');
            } else {
                $times[$day][$time] = $_POST['name'];
                printSuccess($datetime);
            }
        }
    }

    return $times;
}

?>
<div class="container">
    <h2 align="center" class="title">Make an Appointment</h2>
    <h4 align="center" class="subtitle">Select a Time</h4>
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $times = handlePost($times);
        file_put_contents($file, json_encode($times));
        printForm($times);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        printForm($times);
    } else {
        http_response_code(500);
        echo "<h1>HTTP METHOD NOT SUPPORTED</h1>";
    }
    ?>
</div>
</body>
</html>
