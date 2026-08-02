<?php
// includes/currency-switcher.php — Currency selector (GHS / USD / EUR)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['set_curr'])) {
    $curr = strtoupper(sanitize($_GET['set_curr']));
    if (in_array($curr, ['USD', 'GHS', 'EUR'])) {
        $_SESSION['currency'] = $curr;
    }
    $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: " . $redirect);
    exit;
}

$current_currency = $_SESSION['currency'] ?? 'USD';
$currency_symbols = ['USD' => '$ USD', 'GHS' => '₵ GHS', 'EUR' => '€ EUR'];
?>

<div class="dropdown d-inline-block">
    <button class="btn btn-nav-currency btn-sm dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa-solid fa-coins me-1"></i><?= $currency_symbols[$current_currency] ?? '$ USD' ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
        <li><h6 class="dropdown-header text-uppercase small fw-bold">Select Currency</h6></li>
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between <?= ($current_currency === 'USD') ? 'active fw-bold' : '' ?>" href="includes/currency-switcher.php?set_curr=USD">
                <span>USD ($)</span> <small class="text-muted">Base</small>
            </a>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between <?= ($current_currency === 'GHS') ? 'active fw-bold' : '' ?>" href="includes/currency-switcher.php?set_curr=GHS">
                <span>GHS (₵)</span> <small class="text-muted">~15.5/$</small>
            </a>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between <?= ($current_currency === 'EUR') ? 'active fw-bold' : '' ?>" href="includes/currency-switcher.php?set_curr=EUR">
                <span>EUR (€)</span> <small class="text-muted">~0.92/$</small>
            </a>
        </li>
    </ul>
</div>
