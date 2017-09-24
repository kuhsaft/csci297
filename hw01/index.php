<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Peter Nguyen -->
    <!-- CSCI 297 -->

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Assignment 1</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">

    <style>
        body {
            padding-left: 2rem;
            padding-right: 2rem;
        }
    </style>
</head>
<body>
<div class="container">
    <div style="text-align: center;">
        <?php
        function longdate($timestamp)
        {
            return date("l F jS Y", $timestamp);
        }

        echo "<p>Hello World</p>";
        echo "<p>Today is " . longdate(time()) . "</p>";
        ?>
    </div>
</div>
</body>
</html>
