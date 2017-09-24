<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Peter Nguyen -->
    <!-- CSCI 297 -->

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Assignment 3 - Write to File</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">

    <style>
        body {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
    </style>
</head>
<body>
<?php
// Open file
$filename = "csci297_nguyenp3_hw03";
$outFile = fopen($filename, "w") or die("Unable to open file!");

function longdate(int $timestamp): string
{
    return date("D M jS Y", $timestamp);
}

// Write next 7 day timestamps to file
for ($day = 0; $day < 7; $day++) {
    $timestamp = strtotime('+' . $day . 'day'); // Convert day offset to timestamp
    fwrite($outFile, longdate($timestamp) . "\n");
}

// Close file
fclose($outFile);
?>
<p>Done.</p>
<p><a href="read.php">Read from File</a></p>
</body>
</html>
