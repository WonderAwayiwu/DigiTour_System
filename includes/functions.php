<?php
// includes/functions.php - Global Helper & Security Functions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize User Input to prevent XSS attacks
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Extract clean plain-text short description snippet stripped of HTML tags
 */
function dt_short_desc($description, $length = 100) {
    if (empty($description)) {
        return '';
    }
    $clean = strip_tags($description);
    $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $clean = preg_replace('/\s+/', ' ', $clean);
    $clean = trim($clean);
    return sanitize(mb_strimwidth($clean, 0, $length, '…'));
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if logged in user is Admin
 */
function is_admin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

/**
 * Require login for protected routes
 */
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php?msg=" . urlencode("Please log in to continue."));
        exit;
    }
}

/**
 * Require admin privileges
 */
function require_admin() {
    if (!is_admin()) {
        header("Location: ../login.php?msg=" . urlencode("Access denied. Admin privileges required."));
        exit;
    }
}

/**
 * Calculate total nights between check-in and check-out
 */
function calculate_nights($check_in, $check_out) {
    $date1 = new DateTime($check_in);
    $date2 = new DateTime($check_out);
    $diff = $date1->diff($date2);
    return max(1, $diff->days);
}

/**
 * Render star ratings (1 to 5)
 */
function render_stars($rating) {
    $output = '';
    $rating = max(1, min(5, (int)$rating));
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $output .= '<i class="fa fa-star text-warning"></i>';
        } else {
            $output .= '<i class="far fa-star text-muted"></i>';
        }
    }
    return $output;
}

/**
 * Format Status Badges
 */
function status_badge($status) {
    switch (strtolower($status)) {
        case 'confirmed':
        case 'approved':
        case 'resolved':
            return '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa fa-check-circle me-1"></i> ' . ucfirst($status) . '</span>';
        case 'replied':
            return '<span class="badge bg-info-subtle text-info border border-info-subtle"><i class="fa fa-reply me-1"></i> Replied</span>';
        case 'pending':
        case 'new':
            return '<span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="fa fa-clock me-1"></i> ' . ucfirst($status) . '</span>';
        case 'cancelled':
        case 'rejected':
            return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="fa fa-times-circle me-1"></i> ' . ucfirst($status) . '</span>';
        default:
            return '<span class="badge bg-secondary-subtle text-secondary"><i class="fa fa-info-circle me-1"></i> ' . ucfirst($status) . '</span>';
    }
}

/**
 * Ensure inquiries table supports reply workflow (safe on existing DBs).
 */
function dt_ensure_inquiry_schema(PDO $pdo) {
    static $done = false;
    if ($done) return;
    $done = true;

    try {
        $cols = $pdo->query("SHOW COLUMNS FROM inquiries")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('admin_reply', $cols, true)) {
            $pdo->exec("ALTER TABLE inquiries ADD COLUMN admin_reply TEXT NULL AFTER message");
        }
        if (!in_array('replied_at', $cols, true)) {
            $pdo->exec("ALTER TABLE inquiries ADD COLUMN replied_at TIMESTAMP NULL DEFAULT NULL AFTER admin_reply");
        }
        // Expand status enum for Resolved workflow
        $pdo->exec("ALTER TABLE inquiries MODIFY COLUMN status ENUM('New','Replied','Resolved') DEFAULT 'New'");
    } catch (Throwable $e) {
        // Keep admin usable even if migration fails on locked hosts
    }
}

/**
 * Detect script execution context to adjust relative paths for Admin vs Frontend
 */
function dt_admin_prefix() {
    static $prefix = null;
    if ($prefix === null) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $normalized = str_replace('\\', '/', $script);
        if (strpos($normalized, '/admin/') !== false || strpos($normalized, '/admin') !== false) {
            $prefix = '../';
        } else {
            $prefix = '';
        }
    }
    return $prefix;
}

/**
 * Format Currency (GHS ₵, USD $, EUR €)
 */
function dt_format_currency($amount, $currency = 'USD') {
    $amount = (float)$amount;
    switch (strtoupper($currency)) {
        case 'GHS':
            return '₵' . number_format($amount * 15.5, 2);
        case 'EUR':
            return '€' . number_format($amount * 0.92, 2);
        case 'USD':
        default:
            return '$' . number_format($amount, 2);
    }
}

/**
 * Get Image Path with default placeholder fallback
 */
function get_image_url($image_name, $default = 'assets/img/destination-placeholder.jpg') {
    $prefix = dt_admin_prefix();
    if (!empty($image_name)) {
        if (strpos($image_name, 'http://') === 0 || strpos($image_name, 'https://') === 0) {
            return $image_name;
        }
        if (file_exists(__DIR__ . '/../assets/uploads/' . $image_name)) {
            return $prefix . 'assets/uploads/' . $image_name;
        }
    }
    return (strpos($default, 'http') === 0) ? $default : $prefix . $default;
}

/**
 * Static single-pass directory scanner map (instant O(1) memory lookup)
 */
function dt_get_image_map($dir) {
    static $maps = [];
    $key = rtrim(str_replace('\\', '/', $dir), '/');
    if (isset($maps[$key])) {
        return $maps[$key];
    }

    $map = ['exact' => [], 'files' => []];
    if (is_dir($dir)) {
        $handle = opendir($dir);
        if ($handle) {
            while (($f = readdir($handle)) !== false) {
                if ($f === '.' || $f === '..') continue;
                $full = $dir . $f;
                if (is_file($full)) {
                    $map['files'][] = $f;
                    $dotPos = strrpos($f, '.');
                    $baseName = ($dotPos !== false) ? substr($f, 0, $dotPos) : $f;
                    $lowerBase = strtolower(trim($baseName));
                    if (!isset($map['exact'][$lowerBase])) {
                        $map['exact'][$lowerBase] = $f;
                    }
                }
            }
            closedir($handle);
        }
    }
    $maps[$key] = $map;
    return $map;
}

function dt_list_image_files($dir) {
    $map = dt_get_image_map($dir);
    return $map['files'];
}

/**
 * Ultra-fast O(1) main image resolve — zero disk stat overhead
 */
function get_destination_main_image($title, $db_image = '') {
    $prefix = dt_admin_prefix();
    
    if (!empty($db_image)) {
        if (strpos($db_image, 'http://') === 0 || strpos($db_image, 'https://') === 0) {
            return $db_image;
        }
        if (file_exists(__DIR__ . '/../assets/uploads/' . $db_image)) {
            return $prefix . 'assets/uploads/' . $db_image;
        }
    }

    $title = trim((string)$title);
    if ($title === '') {
        return 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?auto=format&fit=crop&w=800&q=80';
    }

    $base_dir = __DIR__ . '/../sources/images/all_tourist_sites/';
    $rel_prefix = $prefix . 'sources/images/all_tourist_sites/';
    $map = dt_get_image_map($base_dir);
    $lowerTitle = strtolower($title);

    if (isset($map['exact'][$lowerTitle])) {
        return $rel_prefix . rawurlencode($map['exact'][$lowerTitle]);
    }

    // Prefix match lookup
    foreach ($map['files'] as $f) {
        if (stripos($f, $title) === 0) {
            return $rel_prefix . rawurlencode($f);
        }
    }

    return 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?auto=format&fit=crop&w=800&q=80';
}

/**
 * Get all available image URLs for a destination (detail pages / galleries / carousels)
 */
function get_destination_images($title, $db_image = '', $destination_id = 0) {
    global $pdo;
    $images = [];
    $prefix = dt_admin_prefix();
    $base_dir = __DIR__ . '/../sources/images/all_tourist_sites/';
    $rel_prefix = $prefix . 'sources/images/all_tourist_sites/';
    $title = trim((string)$title);

    if (!empty($db_image)) {
        if (strpos($db_image, 'http://') === 0 || strpos($db_image, 'https://') === 0) {
            $images[] = $db_image;
        } elseif (file_exists(__DIR__ . '/../assets/uploads/' . $db_image)) {
            $images[] = $prefix . 'assets/uploads/' . $db_image;
        }
    }

    if ($destination_id > 0 && isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT image_path FROM destination_images WHERE destination_id = ? ORDER BY id ASC");
            $stmt->execute([(int)$destination_id]);
            $db_gallery = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($db_gallery as $g_img) {
                if (!empty($g_img)) {
                    if (strpos($g_img, 'http://') === 0 || strpos($g_img, 'https://') === 0) {
                        $images[] = $g_img;
                    } elseif (file_exists(__DIR__ . '/../assets/uploads/' . $g_img)) {
                        $images[] = $prefix . 'assets/uploads/' . $g_img;
                    } else {
                        $images[] = $g_img;
                    }
                }
            }
        } catch (Exception $e) {
            // Ignore DB error if table not ready
        }
    }

    if ($title !== '') {
        $files = dt_list_image_files($base_dir);
        $main = [];
        $nums = [];
        foreach ($files as $f) {
            if (stripos($f, $title) === 0) {
                if (preg_match('/^' . preg_quote($title, '/') . '\.[a-z0-9]+$/i', $f)) {
                    $main[] = $f;
                } elseif (preg_match('/^' . preg_quote($title, '/') . ' -(\d+)\./i', $f, $m)) {
                    $nums[(int)$m[1]] = $f;
                }
            }
        }
        ksort($nums);
        foreach (array_merge($main, array_values($nums)) as $f) {
            $images[] = $rel_prefix . rawurlencode($f);
        }
    }

    if (empty($images)) {
        $images[] = 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?auto=format&fit=crop&w=800&q=80';
    }

    return array_values(array_unique($images));
}

/**
 * Ultra-fast hotel main image
 */
function get_hotel_main_image($hotel_name, $db_image = '') {
    $prefix = dt_admin_prefix();
    
    if (!empty($db_image)) {
        if (strpos($db_image, 'http://') === 0 || strpos($db_image, 'https://') === 0) {
            return $db_image;
        }
        if (file_exists(__DIR__ . '/../assets/uploads/' . $db_image)) {
            return $prefix . 'assets/uploads/' . $db_image;
        }
    }

    $hotel_name = trim((string)$hotel_name);
    if ($hotel_name === '') {
        return 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80';
    }

    $base_dir = __DIR__ . '/../sources/images/all_tourist_sites/Hotels/';
    $rel_prefix = $prefix . 'sources/images/all_tourist_sites/Hotels/';
    $map = dt_get_image_map($base_dir);
    $lowerName = strtolower($hotel_name);

    if (isset($map['exact'][$lowerName])) {
        return $rel_prefix . rawurlencode($map['exact'][$lowerName]);
    }

    foreach ($map['files'] as $f) {
        if (stripos($f, $hotel_name) === 0) {
            return $rel_prefix . rawurlencode($f);
        }
    }

    return 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80';
}

/**
 * Get all hotel images (detail / carousels)
 */
function get_hotel_images($hotel_name, $db_image = '', $hotel_id = 0) {
    global $pdo;
    $images = [];
    $prefix = dt_admin_prefix();
    $base_dir = __DIR__ . '/../sources/images/all_tourist_sites/Hotels/';
    $rel_prefix = $prefix . 'sources/images/all_tourist_sites/Hotels/';
    $hotel_name = trim((string)$hotel_name);

    if (!empty($db_image)) {
        if (strpos($db_image, 'http://') === 0 || strpos($db_image, 'https://') === 0) {
            $images[] = $db_image;
        } elseif (file_exists(__DIR__ . '/../assets/uploads/' . $db_image)) {
            $images[] = $prefix . 'assets/uploads/' . $db_image;
        }
    }

    if ($hotel_id > 0 && isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT image_path FROM hotel_images WHERE hotel_id = ? ORDER BY id ASC");
            $stmt->execute([(int)$hotel_id]);
            $db_gallery = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($db_gallery as $g_img) {
                if (!empty($g_img)) {
                    if (strpos($g_img, 'http://') === 0 || strpos($g_img, 'https://') === 0) {
                        $images[] = $g_img;
                    } elseif (file_exists(__DIR__ . '/../assets/uploads/' . $g_img)) {
                        $images[] = $prefix . 'assets/uploads/' . $g_img;
                    } else {
                        $images[] = $g_img;
                    }
                }
            }
        } catch (Exception $e) {
            // Ignore DB error
        }
    }

    if ($hotel_name !== '') {
        $files = dt_list_image_files($base_dir);
        $main = [];
        $nums = [];
        foreach ($files as $f) {
            if (stripos($f, $hotel_name) === 0) {
                if (preg_match('/^' . preg_quote($hotel_name, '/') . '\.[a-z0-9]+$/i', $f)) {
                    $main[] = $f;
                } elseif (preg_match('/^' . preg_quote($hotel_name, '/') . ' -(\d+)\./i', $f, $m)) {
                    $nums[(int)$m[1]] = $f;
                }
            }
        }
        ksort($nums);
        foreach (array_merge($main, array_values($nums)) as $f) {
            $images[] = $rel_prefix . rawurlencode($f);
        }
    }

    if (empty($images)) {
        $images[] = get_hotel_main_image($hotel_name, $db_image);
    }

    return array_values(array_unique($images));
}

/** DigiTour admin contact (Ghana) */
if (!defined('DT_ADMIN_PHONE_LOCAL')) {
    define('DT_ADMIN_PHONE_LOCAL', '0549326089');
    define('DT_ADMIN_PHONE_E164', '233549326089'); // +233 without leading 0
    define('DT_ADMIN_WHATSAPP', 'https://wa.me/233549326089');
    define('DT_ADMIN_TEL', 'tel:+233549326089');
}

/**
 * Safely render formatted HTML articles / plain-text with paragraph blocks
 */
function dt_render_article($content) {
    if (empty($content)) return '';
    
    // Check if content already contains HTML block tags
    if (preg_match('/<(p|h[1-6]|ul|ol|blockquote|div|table)/i', $content)) {
        $allowed_tags = '<h3><h4><h5><h6><p><ul><ol><li><blockquote><strong><em><a><br><table><tr><td><th><div><span><hr>';
        return strip_tags($content, $allowed_tags);
    }
    
    // Otherwise, convert double linebreaks into paragraphs and single linebreaks into <br>
    $paragraphs = explode("\n\n", trim($content));
    $html = '';
    foreach ($paragraphs as $p) {
        $p = trim($p);
        if ($p !== '') {
            $html .= '<p class="article-paragraph">' . nl2br(htmlspecialchars($p, ENT_QUOTES, 'UTF-8')) . '</p>';
        }
    }
    return $html;
}

/**
 * Bento layout shape + accent for mixed destination/hotel grids
 */
function dt_bento_meta($index) {
    $shapes = ['wide', 'circle', 'std', 'tall', 'hero', 'circle', 'std', 'square'];
    $accents = ['gold', 'teal', 'coral', 'navy', 'sunset', 'gold', 'teal', 'coral'];
    return [
        'shape' => $shapes[$index % count($shapes)],
        'accent' => $accents[$index % count($accents)],
    ];
}
?>

