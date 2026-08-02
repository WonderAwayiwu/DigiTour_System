<?php
// admin/export-csv.php - Export DigiTour Records to CSV
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$type = isset($_GET['type']) ? sanitize($_GET['type']) : 'bookings';
$filename = "digitour_" . $type . "_" . date('Y-m-d_H-i-s') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

if ($type === 'destinations') {
    fputcsv($output, ['ID', 'Title', 'Region', 'Category', 'Location Contact', 'Featured', 'Created At']);
    $rows = $pdo->query("SELECT id, title, region, category, location_contact, is_featured, created_at FROM destinations ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $row['is_featured'] = $row['is_featured'] ? 'Yes' : 'No';
        fputcsv($output, $row);
    }
} elseif ($type === 'hotels') {
    fputcsv($output, ['ID', 'Hotel Name', 'Destination ID', 'Price Per Night ($)', 'Room Capacity', 'Phone', 'Email', 'Created At']);
    $rows = $pdo->query("SELECT id, name, destination_id, price_per_night, room_capacity, contact_phone, contact_email, created_at FROM hotels ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
} elseif ($type === 'users') {
    fputcsv($output, ['ID', 'Full Name', 'Email', 'Phone', 'Role', 'Created At']);
    $rows = $pdo->query("SELECT id, full_name, email, phone, role, created_at FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
} else { // default bookings
    fputcsv($output, ['Booking ID', 'Tourist Name', 'Email', 'Phone', 'Hotel Name', 'Destination', 'Check-In', 'Check-Out', 'Guests', 'Total Cost ($)', 'Status', 'Booking Date']);
    $sql = "SELECT b.id, u.full_name AS tourist_name, u.email, u.phone, h.name AS hotel_name, d.title AS destination_name, b.check_in_date, b.check_out_date, b.guests_count, b.total_price, b.status, b.created_at FROM bookings b JOIN users u ON b.user_id = u.id JOIN hotels h ON b.hotel_id = h.id JOIN destinations d ON h.destination_id = d.id ORDER BY b.id DESC";
    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
}

fclose($output);
exit;
?>
