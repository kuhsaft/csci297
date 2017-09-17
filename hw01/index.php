<!DOCTYPE html>
<html>
<head>
    <title>Assignment 1</title>
<body>
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
</body>
</html>
