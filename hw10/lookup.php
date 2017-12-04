<?php
ini_set('display_errors', 1);
include_once('config.inc.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Appointment Lookup</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="../hw09/calendar.css">
</head>
<body>
<?php
function printForm()
{
    print <<<HTML
<h2 align="center" class="title">Find Your Appointment</h2>
<form method="post">
<h2>Enter your information:</h2>
<div class="form-group">
    <label for="email">Email:</label>
    <input type="email" class="form-control" id="email" name="email" placeholder="student@winthrop.edu" required>
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
    print "<div class='alert alert-success' role='alert'>";
    print "You have an appointment on " . $datetime->format("l, F jS") . " at " . $datetime->format("h:i A") . ".";
    print '</div>';
}

function findAppointment(PDO $dbConn, string $email)
{
    // Check if email already exists
    $email = strtolower($email);
    $query = "SELECT date, time FROM appointments WHERE DATE(CONCAT(date, ' ', time)) > NOW() AND email = ?";

    $stmt = $dbConn->prepare($query);
    $stmt->execute([$email]);
    $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

    if (is_array($fetch)) { // Appointment found
        $date = $fetch['date'];
        $time = $fetch['time'];
        $tz = date("O");
        $dateTime = DateTime::createFromFormat(DateTime::ISO8601, "${date}T${time}${tz}");

        printSuccess($dateTime);
    } else {
        printError('No appointment found with that email.');
    }
}

function handlePost(PDO $dbConn)
{
    if (!array_key_exists('email', $_POST) ||
        empty($_POST['email']) ||
        !is_string($_POST['email'])
    ) {
        printError('You must enter your email.');
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        printError('Invalid email format.');
    } else {
        findAppointment($dbConn, $_POST['email']);
    }
}

?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand" href="index.html">Scheduling</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="signup.php">Register</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="lookup.php">Lookup <span class="sr-only">(current)</span></a>
            </li>
        </ul>
    </div>
</nav>
<div class="container">
    <?php
    $dbConn = new PDO(
        "mysql:host={$config['dbhost']};dbname={$config['dbname']};charset=utf8mb4",
        $config['dbuser'],
        $config['dbpass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handlePost($dbConn);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
        printForm();
    } else {
        http_response_code(500);
        echo "<h1>HTTP METHOD NOT SUPPORTED</h1>";
    }
    ?>
</div>

<script
        src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g="
        crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"
        integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js"
        integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ"
        crossorigin="anonymous"></script>
<script type="application/javascript" src="manage.js"></script>
</body>
</html>
