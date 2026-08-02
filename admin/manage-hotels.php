<?php
// admin/manage-hotels.php - Hotel CRUD & Relational Mapping Module with Multi-Image Gallery & Live Search
$admin_title = "Manage Hotels & Accommodations";
require_once __DIR__ . '/includes/admin-header.php';

$msg = '';
$error = '';
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle Delete Hotel
if ($action === 'delete' && $edit_id > 0) {
    $del = $pdo->prepare("DELETE FROM hotels WHERE id = ?");
    if ($del->execute([$edit_id])) {
        $msg = "Hotel accommodation deleted successfully.";
    }
    $action = 'list';
}

// Handle Delete Secondary Hotel Gallery Image
if ($action === 'delete_img') {
    $img_id = isset($_GET['img_id']) ? (int)$_GET['img_id'] : 0;
    if ($img_id > 0) {
        $del_img = $pdo->prepare("DELETE FROM hotel_images WHERE id = ?");
        $del_img->execute([$img_id]);
        $msg = "Hotel gallery image removed.";
    }
    $action = 'edit';
}

// Handle Add / Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_hotel'])) {
    $destination_id = (int)$_POST['destination_id'];
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price_per_night = (float)$_POST['price_per_night'];
    $room_capacity = (int)$_POST['room_capacity'];
    $contact_phone = sanitize($_POST['contact_phone']);
    $contact_email = sanitize($_POST['contact_email']);

    if ($destination_id <= 0 || empty($name) || $price_per_night <= 0) {
        $error = "Please select a Destination, enter Hotel Name, and specify Price per Night.";
    } else {
        // Main Image Upload Handling
        $image = '';
        $upload_dir = __DIR__ . '/../assets/uploads/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_name = 'hotel_' . time() . '_' . rand(100, 999) . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_name)) {
                $image = $new_name;
            }
        }

        $target_hotel_id = 0;
        if ($edit_id > 0) {
            // Update
            if (!empty($image)) {
                $upd = $pdo->prepare("UPDATE hotels SET destination_id = ?, name = ?, description = ?, price_per_night = ?, room_capacity = ?, contact_phone = ?, contact_email = ?, image = ? WHERE id = ?");
                $upd->execute([$destination_id, $name, $description, $price_per_night, $room_capacity, $contact_phone, $contact_email, $image, $edit_id]);
            } else {
                $upd = $pdo->prepare("UPDATE hotels SET destination_id = ?, name = ?, description = ?, price_per_night = ?, room_capacity = ?, contact_phone = ?, contact_email = ? WHERE id = ?");
                $upd->execute([$destination_id, $name, $description, $price_per_night, $room_capacity, $contact_phone, $contact_email, $edit_id]);
            }
            $target_hotel_id = $edit_id;
            $msg = "Hotel details updated successfully!";
            $action = 'list';
        } else {
            // Insert
            $ins = $pdo->prepare("INSERT INTO hotels (destination_id, name, description, price_per_night, room_capacity, contact_phone, contact_email, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([$destination_id, $name, $description, $price_per_night, $room_capacity, $contact_phone, $contact_email, $image]);
            $target_hotel_id = (int)$pdo->lastInsertId();
            $msg = "New hotel accommodation added successfully!";
            $action = 'list';
        }

        // Multiple Secondary Gallery Images Upload Handling
        if ($target_hotel_id > 0 && isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
            $g_ins = $pdo->prepare("INSERT INTO hotel_images (hotel_id, image_path) VALUES (?, ?)");
            foreach ($_FILES['gallery_images']['name'] as $idx => $g_name) {
                if ($_FILES['gallery_images']['error'][$idx] === UPLOAD_ERR_OK) {
                    $g_ext = pathinfo($g_name, PATHINFO_EXTENSION);
                    $g_new_name = 'hotel_gal_' . time() . '_' . rand(1000, 9999) . '_' . $idx . '.' . $g_ext;
                    if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$idx], $upload_dir . $g_new_name)) {
                        $g_ins->execute([$target_hotel_id, $g_new_name]);
                    }
                }
            }
        }
    }
}

// Fetch Hotel data and secondary gallery if editing
$edit_hotel = null;
$edit_gallery_images = [];
if (($action === 'edit' || $action === 'delete_img') && $edit_id > 0) {
    $e_stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
    $e_stmt->execute([$edit_id]);
    $edit_hotel = $e_stmt->fetch();

    if ($edit_hotel) {
        $g_stmt = $pdo->prepare("SELECT * FROM hotel_images WHERE hotel_id = ? ORDER BY id ASC");
        $g_stmt->execute([$edit_id]);
        $edit_gallery_images = $g_stmt->fetchAll();
    }
}

// Fetch All Destinations for dropdown
$dest_options = $pdo->query("SELECT id, title, region FROM destinations ORDER BY title ASC")->fetchAll();

// Fetch All Hotels for listing
$all_hotels = $pdo->query("SELECT h.*, d.title AS destination_name, d.region FROM hotels h JOIN destinations d ON h.destination_id = d.id ORDER BY h.id DESC")->fetchAll();
?>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> <?= $msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit' || $action === 'delete_img'): ?>
    <!-- ADD / EDIT HOTEL FORM -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
            <h5 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-hotel text-warning me-2"></i> <?= ($edit_id > 0) ? 'Edit Hotel Details' : 'Add New Hotel' ?>
            </h5>
            <a href="manage-hotels.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back to List</a>
        </div>

        <div class="card-body">
            <form action="manage-hotels.php<?= ($edit_id > 0) ? '?id=' . $edit_id : '' ?>" method="POST" enctype="multipart/form-data">
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Map To Destination Site <span class="text-danger">*</span></label>
                        <select name="destination_id" class="form-select" required>
                            <option value="">-- Select Destination --</option>
                            <?php foreach ($dest_options as $do): 
                                $sel = ($edit_hotel && $edit_hotel['destination_id'] == $do['id']) ? 'selected' : '';
                            ?>
                                <option value="<?= $do['id'] ?>" <?= $sel ?>><?= sanitize($do['title']) ?> (<?= sanitize($do['region']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Hotel / Resort Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. The Rainforest Lodge" required value="<?= $edit_hotel ? sanitize($edit_hotel['name']) : '' ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Hotel Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Describe amenities, view, room features..."><?= $edit_hotel ? sanitize($edit_hotel['description']) : '' ?></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Price per Night ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="price_per_night" class="form-control" placeholder="40.00" required value="<?= $edit_hotel ? $edit_hotel['price_per_night'] : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Room Capacity</label>
                        <input type="number" name="room_capacity" class="form-control" placeholder="2" value="<?= $edit_hotel ? $edit_hotel['room_capacity'] : 2 ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Contact Phone</label>
                        <input type="text" name="contact_phone" class="form-control" placeholder="+233 24 000 0000" value="<?= $edit_hotel ? sanitize($edit_hotel['contact_phone']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" placeholder="hotel@example.com" value="<?= $edit_hotel ? sanitize($edit_hotel['contact_email']) : '' ?>">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Main Hotel Cover Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <?php if ($edit_hotel && !empty($edit_hotel['image'])): ?>
                            <small class="text-muted d-block mt-1">Current file: <?= sanitize($edit_hotel['image']) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Multiple Carousel / Secondary Gallery Images</label>
                        <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted d-block mt-1">Select one or multiple images for hotel room gallery.</small>
                    </div>
                </div>

                <!-- EXISTING GALLERY IMAGES PREVIEW (If Editing) -->
                <?php if (!empty($edit_gallery_images)): ?>
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold mb-3"><i class="fa-solid fa-images text-warning me-2"></i> Uploaded Room Gallery Images (<?= count($edit_gallery_images) ?>)</h6>
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach ($edit_gallery_images as $g_img): 
                                $g_url = get_image_url($g_img['image_path'], '../' . $g_img['image_path']);
                            ?>
                                <div class="position-relative text-center border rounded p-1 bg-white" style="width: 110px;">
                                    <img src="<?= $g_url ?>" class="rounded mb-1" style="width: 100px; height: 75px; object-fit: cover;" alt="Hotel Gallery">
                                    <br>
                                    <a href="manage-hotels.php?action=delete_img&id=<?= $edit_id ?>&img_id=<?= $g_img['id'] ?>" class="btn btn-xs btn-danger w-100 py-0" style="font-size:11px;" onclick="return confirm('Remove this image from hotel gallery?');">
                                        <i class="fa-solid fa-trash me-1"></i> Delete
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" name="save_hotel" class="btn btn-digitour-gold fw-bold">
                        <i class="fa-solid fa-save me-1"></i> Save Hotel Details
                    </button>
                    <a href="manage-hotels.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- HOTELS TABLE LISTING -->
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="fw-bold text-dark mb-0">
            <i class="fa-solid fa-hotel text-warning me-2"></i> Hotels (<span id="hotelCount"><?= count($all_hotels) ?></span>)
        </h5>
        <div class="admin-toolbar-actions">
            <div class="input-group input-group-sm admin-search">
                <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                <input type="text" id="hotelSearchInput" class="form-control" placeholder="Search hotel, destination…" onkeyup="filterHotelsTable()">
            </div>
            <a href="export-csv.php?type=hotels" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-file-csv me-1"></i> CSV</a>
            <a href="manage-hotels.php?action=add" class="btn btn-sm btn-digitour-gold"><i class="fa-solid fa-plus me-1"></i> Add</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="hotelsTable">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Hotel</th>
                    <th>Destination</th>
                    <th>Price/Night</th>
                    <th>Capacity</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="hotelTableBody">
                <?php if (empty($all_hotels)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No hotels registered yet. Click Add above.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($all_hotels as $h): ?>
                        <tr>
                            <td>
                                <img src="<?= get_hotel_main_image($h['name'], $h['image']) ?>" class="admin-thumb" alt="">
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?= sanitize($h['name']) ?></strong>
                                <small class="text-secondary"><?= dt_short_desc($h['description'], 60) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="fa-solid fa-location-dot me-1"></i><?= sanitize($h['destination_name']) ?>
                                </span>
                            </td>
                            <td><strong class="text-success">$<?= number_format((float)$h['price_per_night'], 2) ?></strong></td>
                            <td><span class="badge bg-light text-dark border"><?= (int)$h['room_capacity'] ?></span></td>
                            <td><small><?= sanitize($h['contact_phone']) ?></small></td>
                            <td>
                                <div class="admin-actions">
                                    <a href="manage-hotels.php?action=edit&id=<?= (int)$h['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                    <a href="manage-hotels.php?action=delete&id=<?= (int)$h['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this hotel?');"><i class="fa-solid fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterHotelsTable() {
    let input = document.getElementById('hotelSearchInput').value.toLowerCase().trim();
    let rows = document.querySelectorAll('#hotelTableBody tr');
    let visibleCount = 0;
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        if (text.indexOf(input) > -1) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    let countSpan = document.getElementById('hotelCount');
    if (countSpan) countSpan.innerText = visibleCount;
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
