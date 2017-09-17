<!DOCTYPE html>
<html>
<head>
    <title>Assignment 2</title>
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
