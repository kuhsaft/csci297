<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Peter Nguyen -->
    <!-- CSCI 297 -->

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Assignment 2</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">

    <style>
        body {
            padding: 2rem;
        }
    </style>
</head>
<body>
<p>Hello World. The next 7 days are:</p>
<ul>
    <?php
    function longdate(int $timestamp): string
    {
        return date("D M jS Y", $timestamp);
    }

    // Loop through 7 days
    for ($day = 0; $day < 7; $day++) {
        $timestamp = strtotime('+' . $day . 'day'); // Convert day offset to timestamp
        echo "<li>" . longdate($timestamp) . "</li>";
    }
    ?>
</ul>
</body>
</html>
