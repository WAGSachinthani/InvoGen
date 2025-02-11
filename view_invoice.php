<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: index.php'); // Redirect to login page if not logged in
    exit();
}

// Connect to the database
include 'db.php';

// Get the invoice ID from the query string
$invoiceId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the invoice details
$invoiceResult = $conn->query("SELECT * FROM invoices WHERE id = $invoiceId");

if ($invoiceResult->num_rows == 0) {
    die('Invoice not found.');
}

$invoice = $invoiceResult->fetch_assoc();

// Fetch items related to this invoice
$itemsResult = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $invoiceId");

if ($itemsResult->num_rows == 0) {
    die('No items found for this invoice.');
}

// Format the date and time
$invoiceDateTime = new DateTime($invoice['created_at']);
$dateFormatted = $invoiceDateTime->format('Y-m-d');
$timeFormatted = $invoiceDateTime->format('H:i:s');

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R&W Computer Systems | Invoice #<?php echo htmlspecialchars($invoice['id']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .invoice-header, .invoice-footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .invoice-header img {
            max-width: 150px;
        }
        .invoice-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .invoice-body {
            margin-bottom: 20px;
        }
        .invoice-body p {
            margin: 5px 0;
        }
        .invoice-details {
            margin-bottom: 20px;
        }
        .invoice-details h2 {
            margin: 0 0 10px;
        }
        .info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .info div {
            width: 48%;
        }
        .items table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items th, .items td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items th {
            background-color: #f4f4f4;
        }
        .summary {
            text-align: right;
        }
        .summary table {
            width: 100%;
            border: none;
        }
        .summary td {
            padding: 4px;
        }
        .terms {
            margin-top: 20px;
            font-size: 14px;
        }
        .terms p {
            margin: 0 0 10px;
        }
        .thank-you {
            text-align: center;
            font-size: 18px;
            margin-top: 30px;
            color:#333;
        }
        .button {
            text-decoration: none;
            color: white;
            background-color: #007bff;
            padding: 10px 15px;
            border-radius: 5px;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <img src="logo.png" alt="Company Logo">
            <h1>R&W Computer Systems</h1>
            <h2>Invoice #<?php echo htmlspecialchars($invoice['id']); ?></h2>
            <p>Issued Date: <?php echo htmlspecialchars($dateFormatted); ?> at <?php echo htmlspecialchars($timeFormatted); ?></p>
        </div>

        <!-- Buyer and Seller Information -->
        <div class="info">
            <div>
                <h2>Bill To:</h2>
                <p><b>Mr / Ms</b> <?php echo htmlspecialchars($invoice['customer_name']); ?></p>
            </div>
            <div>
                <h2>From:</h2>
                <p>R&W Computer Systems</p>
                <p>No.49, Suderis Silwa Mawatha, Horana.</p>
                <p>Email: R&WComputerSystems@gmail.com</p>
                <p>Phone: 034-2265304 | 077-7289495</p>
            </div>
        </div>

        <!-- Itemized List -->
        <div class="items">
            <h2>Invoice Details</h2>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Warranty (m/y)</th>
                        <th>Quantity</th>
                        <th>Unit Price Rs.</th>
                        <th>Subtotal Rs.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($item = $itemsResult->fetch_assoc()):
                        $subtotal = $item['quantity'] * $item['unit_price'];
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['item']); ?></td>
                            <td><?php echo htmlspecialchars($item['warranty']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>Rs.<?php echo htmlspecialchars(number_format($item['unit_price'], 2)); ?></td>
                            <td>Rs.<?php echo htmlspecialchars(number_format($subtotal, 2)); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="summary">
            <h2>Summary</h2>
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td>Rs.<?php echo htmlspecialchars(number_format($invoice['total_amount'], 2)); ?></td>
                </tr>
                <tr>
                    <td>Discount Price:</td>
                    <td>Rs.<?php echo htmlspecialchars(number_format($invoice['discount_total'], 2)); ?></td>
                </tr>
                <tr>
                    <td><strong>Total Amount Due:</strong></td>
                    <td><strong>Rs.<?php echo htmlspecialchars(number_format($invoice['total_amount'] - $invoice['discount_total'], 2)); ?></strong></td>
                </tr>
            </table>
        </div>

        <!-- Terms and Conditions -->
        <div class="terms">
            <h2>Terms and Conditions</h2>
            <p>Warranty covers parts replacement only within the stated period.</p>
            <p>Please make sure to keep this invoice safe for future reference regarding warranty claims.</p>
        </div>

        <!-- Thank You Note -->
        <div class="thank-you">
           
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <a href="generate_pdf.php?id=<?php echo $invoice['id']; ?>" class="button">Download PDF</a>
            <a href="dashboard.php" class="button">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
