<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: index.php'); // Redirect to login page if not logged in
    exit();
}

// Optionally use the stored username
$username = $_SESSION['username'];

// Connect to the database
include 'db.php';

// Fetch total invoice count
$invoiceCountResult = $conn->query("SELECT COUNT(*) AS count FROM invoices");
$invoiceCount = $invoiceCountResult->fetch_assoc()['count'];

// Fetch all invoices
$allInvoicesResult = $conn->query("SELECT * FROM invoices ORDER BY created_at DESC");

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R&W Computer Systems | Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2, h3 {
            color: #333;
        }
        .dashboard-item {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .btn-view, .btn-create, .btn-logout, .btn-delete {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn-view {
            background-color: #007bff;
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .btn-create {
            background-color: #28a745;
        }
        .btn-logout {
            background-color: #ffc107;
            color: black;
        }
        .btn-logout:hover {
            background-color: #e0a800;
        }
        .action-buttons {
            margin-bottom: 20px;
        }
        .message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border: 1px solid #c3e6cb;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
    <script>
        function confirmDeletion(invoiceId) {
            if (confirm('Are you sure you want to delete this invoice?')) {
                window.location.href = 'delete_invoice.php?id=' + invoiceId;
            }
        }
    </script>
</head>
<body>
    <div class="dashboard-container">
        <h2>Welcome Admin !</h2>
        <p>This is your dashboard. Here you can view recent activity and manage your account.</p>

        <?php if (isset($_GET['msg'])): ?>
            <div class="message"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <!-- Total Invoices -->
        <div class="dashboard-item">
            <h3>Total Invoices</h3>
            <p><?php echo htmlspecialchars($invoiceCount); ?></p>
        </div>

        <!-- Action buttons -->
        <div class="action-buttons">
            <a href="create_invoice.php" class="btn-create">Create New Invoice</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>

        <!-- All Invoices -->
        <div class="dashboard-item">
            <h3>All Invoices</h3>
            <table>
                <thead>
                    <tr>
                        <th>Invoice No.</th>
                        <th>Customer Name</th>                       
                        <th>Total Amount</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($invoice = $allInvoicesResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($invoice['id']); ?></td>
                            <td><?php echo htmlspecialchars($invoice['customer_name']); ?></td>                          
                            <td>Rs.<?php echo htmlspecialchars(number_format($invoice['total_amount'], 2)); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($invoice['created_at']))); ?></td>
                            <td><?php echo htmlspecialchars(date('H:i:s', strtotime($invoice['created_at']))); ?></td>
                            <td>
                                <a href="view_invoice.php?id=<?php echo htmlspecialchars($invoice['id']); ?>" class="btn-view">View</a>
                                <button class="btn-delete" onclick="confirmDeletion(<?php echo htmlspecialchars($invoice['id']); ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</body>
</html>
