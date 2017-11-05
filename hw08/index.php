<?php
ini_set('display_errors', 1);
include_once('config.inc.php');

function printError(string $error)
{
    print "<div class='alert alert-danger' role='alert'>${error}</div>";
}

function handlePost(PDO $DbConn)
{
    if ($_POST['action'] === 'delete') {
        $stmt = $DbConn->prepare("DELETE FROM warehouse WHERE WhNumb = ?");

        try {
            $stmt->execute([$_POST['WhNumb']]);
        } catch (PDOException $e) {
            printError($e->getMessage());
        }
    } elseif ($_POST['action'] === 'insert') {
        $stmt = $DbConn->prepare(
            "INSERT INTO warehouse (WhNumb, City, Floors) VALUES (:WhNumb, :City, :Floors)"
        );

        try {
            $stmt->execute(
                [
                    'WhNumb' => $_POST['WhNumb'],
                    'City' => $_POST['City'],
                    'Floors' => $_POST['Floors'],
                ]
            );
        } catch (PDOException $e) {
            printError($e->getMessage());
        }
    } else {
        printError("Unknown action: {$_POST['action']}");
    }
}

function printContent(PDO $DbConn)
{
    print <<<HTML
<table class="table" style="margin: auto">
    <thead class="thead-inverse">
    <tr class="head">
        <th colspan="4"
            style="text-align: center; color: white; background-color: black;">Existing
            Warehouses
        </th>
    </tr>
    <tr class="subhead">
        <th>WhNumb</th>
        <th>City</th>
        <th>Floors</th>
        <th style="width: 10em"></th>
    </tr>
    </thead>
    <tbody>
HTML;
    // List warehouses
    $stmt = $DbConn->query("SELECT * FROM warehouse;");
    foreach ($stmt as $row) {
        print <<<HTML
<tr>
    <td>{$row['WhNumb']}</td>
    <td>{$row['City']}</td>
    <td>{$row['Floors']}</td>
    <td>
        <form method="post">
            <input type='hidden' name='WhNumb' value='{$row['WhNumb']}'>
            <input type='hidden' name='action' value='delete'>
            <button class="btn btn-danger btn-block" type="submit">Delete</button>
        </form>
    </td>
</tr>
HTML;
    }

    print <<<HTML
    </tbody>
</table>

<hr>

<form method="post">
    <h4 class="title">New Warehouse Info:</h4>

    <div class="form-group mb-sm-4">
        <label for="WhNumb">WhNumb</label>
        <input type="text" class="form-control" name="WhNumb" id="WhNumb" required>
    </div>
    <div class="form-group mb-sm-4">
        <label for="City">City</label>
        <input type="text" class="form-control" name="City" id="City" required>
    </div>
    <div class="form-group mb-sm-4">
        <label for="Floors">Floors</label>
        <input type="text" class="form-control" name="Floors" id="Floors" required>
    </div>

    <input type='hidden' name='action' value='insert'>
    <input type="submit" class="btn btn-success" value="Add Record">
</form>

<hr>
HTML;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Assignment 8</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css"
          integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb"
          crossorigin="anonymous">

    <style>
        body {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        table tr.subhead > th {
            background-color: #eaeaea;
        }
    </style>
</head>
<body>
<div class="container">
    <?php
    $DbConn = new PDO(
        "mysql:host=deltona.birdnest.org;dbname={$config['dbname']};charset=utf8mb4",
        $config['dbuser'],
        $config['dbpass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handlePost($DbConn);
        printContent($DbConn);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        printContent($DbConn);
    } else {
        http_response_code(500);
        echo "<h1>HTTP METHOD NOT SUPPORTED</h1>";
    }
    ?>
</div>
</body>
</html>
