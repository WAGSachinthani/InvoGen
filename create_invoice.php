<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: index.php');
    exit();
}

// Connect to the database
include 'db.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_invoice'])) {
    $customer_name = $_POST['customer_name'];
    $date = $_POST['date'];
    $items = $_POST['items'];
    $warranties = $_POST['warranties'];
    $quantities = $_POST['quantities'];
    $unit_prices = $_POST['unit_prices'];
    $discounts = $_POST['discounts'];

    // Validate general invoice data
    if (empty($customer_name) || empty($date)) {
        $error = "Customer name and date are required.";
    } else {
        $conn->begin_transaction();

        try {
            // Insert invoice into the main invoices table
            $stmt = $conn->prepare("INSERT INTO invoices (customer_name, date, discount_total) VALUES (?, ?, ?)");
            $discount_total = array_sum($discounts); // Total discount from all items
            $stmt->bind_param("ssd", $customer_name, $date, $discount_total);
            $stmt->execute();
            $invoice_id = $stmt->insert_id;
            $stmt->close();

            // Initialize subtotal
            $subtotal = 0;

            // Loop through each item and insert into invoice_items table
            for ($i = 0; $i < count($items); $i++) {
                $item = $items[$i];
                $warranty = $warranties[$i];
                $quantity = max(0, $quantities[$i]); // Ensure quantity is not negative
                $unit_price = max(0, $unit_prices[$i]); // Ensure unit price is not negative
                $discount = max(0, $discounts[$i]); // Ensure discount is not negative

                if (empty($item) || empty($warranty) || $quantity <= 0 || $unit_price < 0) {
                    throw new Exception("Invalid data for item #" . ($i + 1));
                }

                $total_amount = ($unit_price * $quantity) - $discount;
                $subtotal += ($unit_price * $quantity); // Adding to subtotal

                $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, item, warranty, quantity, unit_price, discount, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issidds", $invoice_id, $item, $warranty, $quantity, $unit_price, $discount, $total_amount);
                $stmt->execute();
                $stmt->close();
            }

            // Update invoice with the subtotal and total amount due
            $total_amount_due = $subtotal - $discount_total;
            $stmt = $conn->prepare("UPDATE invoices SET total_amount = ? WHERE id = ?");
            $stmt->bind_param("di", $total_amount_due, $invoice_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $success = "Invoice added successfully.";
            header('Location: dashboard.php');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error adding invoice: " . $e->getMessage();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R&W Computer Systems | Create Invoice</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 85%;
            margin: 40px auto;
            padding: 20px;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 20px;
            font-size: 28px;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        p.success,
        p.error {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
        }

        p.success {
            color: #ffffff;
            background-color: #28a745;
        }

        p.error {
            color: #ffffff;
            background-color: #dc3545;
        }

        a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 10px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"] {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        input[type="number"] {
            text-align: right;
        }

        button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .items-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .items-table th {
            background-color: #007bff;
            color: #ffffff;
        }

        .item-row {
            background-color: #f9f9f9;
        }

        .item-row:hover {
            background-color: #f1f1f1;
        }

        .remove-btn {
            color: #dc3545;
            cursor: pointer;
            text-decoration: underline;
        }

        .add-btn {
            margin-top: 10px;
            padding: 8px 16px;
            background-color: #28a745;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .add-btn:hover {
            background-color: #218838;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
                margin: 20px;
            }

            h2 {
                font-size: 24px;
            }

            .items-table th,
            .items-table td {
                font-size: 14px;
                padding: 8px;
            }

            input[type="text"],
            input[type="date"],
            input[type="number"] {
                font-size: 14px;
            }

            button,
            .add-btn {
                font-size: 14px;
                padding: 8px 16px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Create New Invoice</h2>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="create_invoice.php" method="post">
            <label for="customer_name">Customer Name:</label>
            <input type="text" id="customer_name" name="customer_name" required>

            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th>Warranty (Months / Years)</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Discount Price</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody id="items">
                    <!-- Items will be dynamically added here -->
                </tbody>
            </table>

            <button type="button" class="add-btn" onclick="addRow()">Add Item</button>

            <label for="total_amount">Total Amount:</label>
            <input type="number" id="total_amount" name="total_amount" step="0.01" readonly>

            <input type="submit" name="add_invoice" value="Create Invoice">
        </form>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
    <script>
        // Function to calculate total amount for all rows
        function calculateTotal() {
            var rows = document.querySelectorAll('.item-row');
            var totalAmount = 0;

            rows.forEach(function(row) {
                var quantity = parseFloat(row.querySelector('.quantity').value) || 0;
                var unitPrice = parseFloat(row.querySelector('.unit_price').value) || 0;
                var discount = parseFloat(row.querySelector('.discount').value) || 0;

                // Ensure quantity, unit price, and discount are non-negative
                quantity = Math.max(0, quantity);
                unitPrice = Math.max(0, unitPrice);
                discount = Math.max(0, discount);

                totalAmount += (quantity * unitPrice) - discount;
            });

            // Update the total amount field
            document.getElementById('total_amount').value = totalAmount.toFixed(2);
        }

        // Function to add a new row
        function addRow() {
            var table = document.getElementById('items');
            var row = document.createElement('tr');
            row.className = 'item-row';
            row.innerHTML = `
                <td><input type="text" name="items[]" class="item" required></td>
                <td><input type="text" name="warranties[]" class="warranty" required></td>
                <td><input type="number" name="quantities[]" class="quantity" step="1" min="0" required></td>
                <td><input type="number" name="unit_prices[]" class="unit_price" step="0.01" min="0" required></td>
                <td><input type="number" name="discounts[]" class="discount" step="0.01" min="0" value="0"></td>
                <td><span class="remove-btn" onclick="removeRow(this)">Remove</span></td>
            `;
            table.appendChild(row);

            // Attach event listeners to new inputs for calculating total
            row.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', calculateTotal);
            });

            calculateTotal(); // Recalculate total after adding a new row
        }

        // Function to remove a row
        function removeRow(btn) {
            var row = btn.parentNode.parentNode;
            row.remove();
            calculateTotal(); // Recalculate total after removing a row
        }

        window.onload = function() {
            // Attach event listeners to initial inputs
            document.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', calculateTotal);
            });

            // Add initial row
            addRow();
        }
    </script>
</body>

</html>