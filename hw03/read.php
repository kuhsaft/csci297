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
<p>
    <?php
    // Open file
    $filename = "csci297_nguyenp3_hw03";
    $inFile = fopen($filename, "r") or die("Unable to open file!");

    // Read and output file
    $contents = fread($inFile, filesize($filename));
    echo nl2br($contents);

    // Close file
    fclose($inFile);
    ?>
</p>
</body>
</html>
