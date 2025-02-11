<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: index.php'); // Redirect to login page if not logged in
    exit();
}

// Connect to the database
include 'db.php';

// Check if an ID is provided
if (isset($_GET['id'])) {
    $invoiceId = intval($_GET['id']);

    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM invoices WHERE id = ?");
    $stmt->bind_param("i", $invoiceId);

    if ($stmt->execute()) {
        // Redirect to dashboard with success message
        header('Location: dashboard.php?msg=Invoice+deleted+successfully');
    } else {
        // Redirect to dashboard with error message
        header('Location: dashboard.php?msg=Error+deleting+invoice');
    }

    $stmt->close();
} else {
    // Redirect to dashboard if no ID is provided
    header('Location: dashboard.php?msg=No+invoice+ID+provided');
}

$conn->close();
?>
