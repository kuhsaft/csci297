<?php
define("PBJ_PRICE", 2.00);
define("HAM_PRICE", 3.50);
define("TURKEY_PRICE", 3.00);
define("GRILLED_PRICE", 3.50);
define("TAX", 0.09);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Peter Nguyen -->
    <!-- CSCI 297 -->

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Assignment 4</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">

    <style>
        body {
            padding: 2rem;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 align="center">Joe's Sandwich Shop</h2>
    <?php
    function showForm(
        string $name,
        string $address,
        int $pbj,
        int $ham,
        int $turkey,
        int $grilled,
        array $errors
    ) {
        $constants = get_defined_constants();
        $pbj_price = number_format($constants['PBJ_PRICE'], 2);
        $ham_price = number_format($constants['HAM_PRICE'], 2);
        $turkey_price = number_format($constants['TURKEY_PRICE'], 2);
        $grilled_price = number_format($constants['GRILLED_PRICE'], 2);

        $errorMessages = "";

        // Create DOM nodes for error messages
        foreach ($errors as $error) {
            $errorMessages .= <<<HTML
<div class="alert alert-danger" role="alert">${error}</div>
HTML;
        }

        print <<<HTML
<h3 align="center">Online Order Form</h3>
<div class="row">
    <div class="col">
        ${errorMessages}
    </div>
</div>
<div class="row">
    <div class="col-5">
        <form method="post">
            <div class="form-group">
                <label>Name</label>
                <input type="text" class="form-control" name="name" placeholder="Your Name" value="${name}" required>
            </div>
            <div class="form-group mb-sm-4">
                <label>Address</label>
                <input type="text" class="form-control" name="address" placeholder="Your Address" value="${address}" required>
            </div>

            <div class="form-group mb-sm-4">
            <label>Sandwiches</label>
                <div class="input-group mb-sm-2">
                    <input type="number" class="form-control" name="PBJ" value="${pbj}" required>
                    <span class="input-group-addon">Peanut Butter and Jelly - $${pbj_price}</span>
                </div>
                <div class="input-group mb-sm-2">
                    <input type="number" min="0" class="form-control" name="Ham" value="${ham}" required>
                    <span class="input-group-addon">Ham and Cheese - $${ham_price}</span>
                </div>
                <div class="input-group mb-sm-2">
                    <input type="number" min="0" class="form-control" name="Turkey" value="${turkey}" required>
                    <span class="input-group-addon">Turkey - $${turkey_price}</span>
                </div>
                <div class="input-group mb-sm-4">
                    <input type="number" min="0" class="form-control" name="Grilled" value="${grilled}" required>
                    <span class="input-group-addon">Grilled Cheese - $${grilled_price}</span>
                </div>
            </div>

            <input type="submit" class="btn btn-success" value="Send Me Some Eats">
        </form>
    </div>
</div>
HTML;
    }

    // Shows a new empty form
    function showNewForm()
    {
        showForm("", "", 0, 0, 0, 0, []);
    }

    // Returns HTML for item row
    function item(string $name, int $quantity, float $price): string
    {
        $price_formatted = number_format($price, 2);
        $total_formatted = number_format($quantity * $price, 2);

        return <<<HTML
<tr>
    <td class="col-md-9"><em>${name}</em></td>
    <td class="col-md-1" style="text-align: center"> ${quantity}</td>
    <td class="col-md-1 text-center">$${price_formatted}</td>
    <td class="col-md-1 text-center">$${total_formatted}</td>
</tr>
HTML;

    }

    function showReceipt(
        string $name,
        string $address,
        int $pbj,
        int $ham,
        int $turkey,
        int $grilled
    ) {
        $constants = get_defined_constants();
        $pbj_price = $constants['PBJ_PRICE'];
        $ham_price = $constants['HAM_PRICE'];
        $turkey_price = $constants['TURKEY_PRICE'];
        $grilled_price = $constants['GRILLED_PRICE'];
        $tax_percent = $constants['TAX'];

        $date = date("m/d/Y", time());
        $rows = "";

        if ($pbj > 0) {
            $rows .= item("PBJ Sandwich", $pbj, $pbj_price);
        }
        if ($ham > 0) {
            $rows .= item("Ham Sandwich", $ham, $ham_price);
        }
        if ($turkey > 0) {
            $rows .= item("Turkey Sandwich", $turkey, $turkey_price);
        }
        if ($grilled > 0) {
            $rows .= item("Grilled Sandwich", $grilled, $grilled_price);
        }

        $subtotal = ($pbj * $pbj_price) + ($ham * $ham_price) + ($turkey * $turkey_price) + ($grilled * $grilled_price);
        $subtotal_formatted = number_format($subtotal, 2);

        $tax = $subtotal * $tax_percent;
        $tax_formatted = number_format($tax, 2);

        $total = $subtotal + $tax;
        $total_formatted = number_format($total, 2);

        print <<<HTML
<h3 align="center">Receipt</h3>
<div class="row">
    <div class="col">
        <address>
            <strong>Billed To:</strong><br>
            ${name}<br>
            ${address}
        </address>
    </div>
    <div class="col text-right">
        <address>
		    <strong>Order Date:</strong><br>
			${date}
		</address>
    </div>
</div>
<div class="row">
    <table class="table table-hover">
        <thead>
        <tr>
            <th>Item</th>
            <th>Quantity</th>
            <th class="text-center">Price</th>
            <th class="text-center">Total</th>
        </tr>
        </thead>
        <tbody>
        ${rows}
        <tr>
            <td></td>
            <td></td>
            <td class="text-right">
                <p><strong>Subtotal: </strong></p>
                <p><strong>Tax: </strong></p>
            </td>
            <td class="text-center">
                <p><strong>$${subtotal_formatted}</strong></p>
                <p><strong>$${tax_formatted}</strong></p>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td class="text-right">
                <h4><strong>Total: </strong>
            </td>
            <td class="text-center text-danger">
                <h4><strong>$${total_formatted}</strong>
            </td>
        </tr>
        </tbody>
    </table>
</div>
HTML;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Show a new empty form on GET request
        showNewForm();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize name and address, trim numerical fields
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
        $pbj = trim($_POST['PBJ']);
        $ham = trim($_POST['Ham']);
        $turkey = trim($_POST['Turkey']);
        $grilled = trim($_POST['Grilled']);

        $errors = [];

        // Check if any fields are empty
        if ($name === "") {
            array_push($errors, "Name cannot be empty.");
        }
        if ($address === "") {
            array_push($errors, "Address cannot be empty.");
        }
        if ($pbj === "") {
            array_push($errors, "Number of PBJ sandwiches cannot be empty.");
        }
        if ($ham === "") {
            array_push($errors, "Number of ham sandwiches cannot be empty.");
        }
        if ($turkey === "") {
            array_push($errors, "Number of turkey sandwiches cannot be empty.");
        }
        if ($grilled === "") {
            array_push($errors, "Number of grilled sandwiches cannot be empty.");
        }

        // Convert numerical values
        $pbj = filter_var($pbj, FILTER_VALIDATE_INT);
        if (!is_int($pbj) || $pbj < 0) {
            array_push($errors, "Invalid number of PBJ sandwiches.");
        }
        $ham = filter_var($ham, FILTER_VALIDATE_INT);
        if (!is_int($ham) || $ham < 0) {
            array_push($errors, "Invalid number of PBJ sandwiches.");
        }
        $turkey = filter_var($turkey, FILTER_VALIDATE_INT);
        if (!is_int($turkey) || $turkey < 0) {
            array_push($errors, "Invalid number of PBJ sandwiches.");
        }
        $grilled = filter_var($grilled, FILTER_VALIDATE_INT);
        if (!is_int($grilled) || $grilled < 0) {
            array_push($errors, "Invalid number of PBJ sandwiches.");
        }

        // Customer must order at least one item
        if ((intval($pbj) + intval($ham) + intval($turkey) + intval($grilled)) === 0) {
            array_push($errors, "You must order at least one item.");
        }

        if (sizeof($errors) !== 0) {
            // Show the form with errors if any error exists
            showForm($name, $address, $pbj, $ham, $turkey, $grilled, $errors);
        } else {
            // Show the receipt if the form is valid
            showReceipt($name, $address, $pbj, $ham, $turkey, $grilled);
        }
    } else {
        http_response_code(500);
        echo "<h1>HTTP METHOD NOT SUPPORTED</h1>";
    }
    ?>
</div>
</body>
</html>
