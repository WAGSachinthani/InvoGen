<?php
require_once('tcpdf/tcpdf.php');
include 'db.php';  // Connect to  database

// Get invoice ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid invoice ID.');
}
$invoice_id = $_GET['id'];

// Fetch invoice data
$query = "SELECT * FROM invoices WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

// Check if the invoice exists
if (!$invoice) {
    die('Invoice not found.');
}

// Create a new PDF document
$pdf = new TCPDF();

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('R&W Computer Systems');
$pdf->SetTitle('Invoice #' . $invoice_id);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Company logo
$logo = 'path_to__logo.png';  
$pdf->Image($logo, 15, 10, 30, '', 'PNG');

// Add title and company details
$html = '
<h1 style="text-align: center; font-weight: bold; font-size: 20px;">R&W Computer Systems</h1>

<p style="text-align: center; font-size: 12px;">
No.49, Suderis Silwa Mawatha, Horana.<br>
<b>Email:</b> r&wcomputersystems@gmail.com <br> <b>Phone:</b> 034-2265304 | 077-7289495
</p>
<hr>';

// Invoice heading with issued date and time
$invoice_date = date('Y-m-d');
$invoice_time = date('H:i:s');

$html .= '<h2 style="font-size: 16px;">Invoice # ' . $invoice_id . '</h2>';
$html .= '<p><strong>Date:</strong> ' . $invoice_date . '</p>';
$html .= '<p><strong>Time:</strong> ' . $invoice_time . '</p>';

// Customer details
$html .= '<h3>Bill To:</h3>';
$html .= '<p>Mr / Ms ' . $invoice['customer_name'] . '</p>';

// Company details (sender)
$html .= '<h3>From:</h3>';
$html .= '<p>R&W Computer Systems,<br>
No.49, Suderis Silwa Mawatha, Horana.<br>
';

// Fetch items for this invoice
$query = "SELECT * FROM invoice_items WHERE invoice_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$items = $stmt->get_result();

// Professionally styled table for invoice items
$html .= '<h3>Invoice Details</h3>';
$html .= '
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 22px;
        text-align: left;
        margin-top: 10px;
    }
    th {
        background: linear-gradient(135deg, #009688, #004d40);
        color: #ffffff;
        font-weight: bold;  
        font-size: 14px;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
       
    }
    td {
        font-size: 12px;
    }
    .total-row td {
        font-weight: bold;
        background-color: #f9f9f9;
    }
</style>';

$html .= '<table>
<tr><th><b>Description</b></th><th><b>Warranty (m/y)</b></th><th><b>Quantity</b></th><th><b>Unit Price (Rs.)</b></th><th><b>Subtotal (Rs.)</b></th></tr>';

$total_invoice_amount = 0;
while ($item = $items->fetch_assoc()) {
    $item_total = $item['unit_price'] * $item['quantity'];
    $total_invoice_amount += $item_total;

    $html .= '<tr>';
    $html .= '<td>' . $item['item'] . '</td>';
    $html .= '<td>' . $item['warranty'] . '</td>';
    $html .= '<td>' . $item['quantity'] . '</td>';
    $html .= '<td>' . number_format($item['unit_price'], 2) . '</td>';
    $html .= '<td>' . number_format($item_total, 2) . '</td>';
    $html .= '</tr>';
}

// summary and total rows
$html .= '
<tr class="total-row">
    <td colspan="4" style="text-align: right;">Subtotal</td>
    <td>' . number_format($total_invoice_amount, 2) . '</td>
</tr>';

$discount = 0;  //  calculate or fetch this value if available
$html .= '
<tr class="total-row">
    <td colspan="4" style="text-align: right;">Discount Price</td>
    <td>' . number_format($discount, 2) . '</td>
</tr>';

$html .= '
<tr class="total-row">
    <td colspan="4" style="text-align: right;">Total Amount Due</td>
    <td>' . number_format($total_invoice_amount - $discount, 2) . '</td>
</tr>';

$html .= '</table>';

// Styled terms and conditions
$html .= '<h3 style="border-bottom: 1px solid #ddd; font-size: 14px; margin-top: 20px;">Terms and Conditions</h3>';
$html .= '
<div style="background-color: #f7f7f7; padding: 10px; border: 1px solid #ddd; font-size: 11px; line-height: 1.5;">
    <p style="font-style: italic;">Warranty covers parts replacement only within the stated period.</p>
    <p style="font-style: italic;">Please make sure to keep this invoice safe for future reference regarding warranty claims.</p>

    </div>';



// Output the HTML content to the PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Output the PDF to the browser
$pdf->Output('invoice_' . $invoice_id . '.pdf', 'I');
?>
