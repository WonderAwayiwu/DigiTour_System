<?php
// admin/manage-destinations.php - Destination CRUD Module with Multi-Image Gallery & Live Search
$admin_title = "Manage Tourist Destinations";
require_once __DIR__ . '/includes/admin-header.php';

$msg = '';
$error = '';
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'list';
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle Delete Destination
if ($action === 'delete' && $edit_id > 0) {
    $del = $pdo->prepare("DELETE FROM destinations WHERE id = ?");
    if ($del->execute([$edit_id])) {
        $msg = "Destination deleted successfully.";
    }
    $action = 'list';
}

// Handle Delete Secondary Gallery Image
if ($action === 'delete_img') {
    $img_id = isset($_GET['img_id']) ? (int)$_GET['img_id'] : 0;
    if ($img_id > 0) {
        $del_img = $pdo->prepare("DELETE FROM destination_images WHERE id = ?");
        $del_img->execute([$img_id]);
        $msg = "Gallery image removed successfully.";
    }
    $action = 'edit';
}

// Handle Add / Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_destination'])) {
    $title = sanitize($_POST['title']);
    $region = sanitize($_POST['region']);
    $category = sanitize($_POST['category']);
    $description = trim($_POST['description']);
    $history = trim($_POST['history']);
    $location_contact = sanitize($_POST['location_contact']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    if (empty($title) || empty($region) || empty($category) || empty($description)) {
        $error = "Please fill in all required fields (Title, Region, Category, Description).";
    } else {
        // Main Image Upload Handling
        $image_main = '';
        $upload_dir = __DIR__ . '/../assets/uploads/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

        if (isset($_FILES['image_main']) && $_FILES['image_main']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image_main']['name'], PATHINFO_EXTENSION);
            $new_name = 'dest_' . time() . '_' . rand(100, 999) . '.' . $ext;
            if (move_uploaded_file($_FILES['image_main']['tmp_name'], $upload_dir . $new_name)) {
                $image_main = $new_name;
            }
        }

        $target_dest_id = 0;
        if ($edit_id > 0) {
            // Update
            if (!empty($image_main)) {
                $upd = $pdo->prepare("UPDATE destinations SET title = ?, region = ?, category = ?, description = ?, history = ?, location_contact = ?, image_main = ?, is_featured = ? WHERE id = ?");
                $upd->execute([$title, $region, $category, $description, $history, $location_contact, $image_main, $is_featured, $edit_id]);
            } else {
                $upd = $pdo->prepare("UPDATE destinations SET title = ?, region = ?, category = ?, description = ?, history = ?, location_contact = ?, is_featured = ? WHERE id = ?");
                $upd->execute([$title, $region, $category, $description, $history, $location_contact, $is_featured, $edit_id]);
            }
            $target_dest_id = $edit_id;
            $msg = "Destination updated successfully!";
            $action = 'list';
        } else {
            // Insert
            $ins = $pdo->prepare("INSERT INTO destinations (title, region, category, description, history, location_contact, image_main, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([$title, $region, $category, $description, $history, $location_contact, $image_main, $is_featured]);
            $target_dest_id = (int)$pdo->lastInsertId();
            $msg = "New destination added successfully!";
            $action = 'list';
        }

        // Multiple Secondary Gallery Images Upload Handling
        if ($target_dest_id > 0 && isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
            $g_ins = $pdo->prepare("INSERT INTO destination_images (destination_id, image_path) VALUES (?, ?)");
            foreach ($_FILES['gallery_images']['name'] as $idx => $g_name) {
                if ($_FILES['gallery_images']['error'][$idx] === UPLOAD_ERR_OK) {
                    $g_ext = pathinfo($g_name, PATHINFO_EXTENSION);
                    $g_new_name = 'dest_gal_' . time() . '_' . rand(1000, 9999) . '_' . $idx . '.' . $g_ext;
                    if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$idx], $upload_dir . $g_new_name)) {
                        $g_ins->execute([$target_dest_id, $g_new_name]);
                    }
                }
            }
        }
    }
}

// Fetch Destination data and secondary gallery if editing
$edit_dest = null;
$edit_gallery_images = [];
if (($action === 'edit' || $action === 'delete_img') && $edit_id > 0) {
    $e_stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
    $e_stmt->execute([$edit_id]);
    $edit_dest = $e_stmt->fetch();

    if ($edit_dest) {
        $g_stmt = $pdo->prepare("SELECT * FROM destination_images WHERE destination_id = ? ORDER BY id ASC");
        $g_stmt->execute([$edit_id]);
        $edit_gallery_images = $g_stmt->fetchAll();
    }
}

// Fetch all destinations for listing
$all_destinations = $pdo->query("SELECT d.*, COUNT(h.id) AS hotel_count FROM destinations d LEFT JOIN hotels h ON d.id = h.destination_id GROUP BY d.id ORDER BY d.id DESC")->fetchAll();
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
    <!-- ADD / EDIT FORM CARD -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
            <h5 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-pen-to-square text-success me-2"></i> <?= ($action === 'edit' || $action === 'delete_img') ? 'Edit Destination' : 'Add New Destination' ?>
            </h5>
            <a href="manage-destinations.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back to List</a>
        </div>

        <div class="card-body">
            <form action="manage-destinations.php<?= ($edit_id > 0) ? '?id=' . $edit_id : '' ?>" method="POST" enctype="multipart/form-data">
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Attraction Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Kakum Canopy Walkway" required value="<?= $edit_dest ? sanitize($edit_dest['title']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Region <span class="text-danger">*</span></label>
                        <select name="region" class="form-select" required>
                            <?php 
                            $regions = ['Central Region', 'Greater Accra', 'Savannah Region', 'Western Region', 'Eastern Region', 'Volta Region', 'Ashanti Region', 'Northern Region', 'Upper East Region', 'Upper West Region', 'Oti Region', 'Bono Region', 'Bono East Region', 'North East Region', 'Western North Region', 'Ahafo Region'];
                            foreach ($regions as $r):
                                $sel = ($edit_dest && $edit_dest['region'] === $r) ? 'selected' : '';
                                echo "<option value='$r' $sel>$r</option>";
                            endforeach;
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <?php 
                            $categories = ['Nature & Wildlife', 'History', 'Beach', 'Culture & Heritage', 'Nature & Waterways', 'History & Culture'];
                            foreach ($categories as $c):
                                $sel = ($edit_dest && $edit_dest['category'] === $c) ? 'selected' : '';
                                echo "<option value='$c' $sel>$c</option>";
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Comprehensive Overview Article (HTML Supported) <span class="text-danger">*</span></label>
                    <small class="text-muted d-block mb-1">Use HTML tags (e.g. &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;blockquote&gt;) to format the national article.</small>
                    <textarea name="description" class="form-control font-monospace fs-6" rows="8" placeholder="Comprehensive overview of the site..." required><?= $edit_dest ? htmlspecialchars($edit_dest['description']) : '' ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Exhaustive History & Cultural Legacy Article (HTML Supported)</label>
                    <small class="text-muted d-block mb-1">Detailed history, academic research notes, origins, and cultural significance.</small>
                    <textarea name="history" class="form-control font-monospace fs-6" rows="10" placeholder="Historical background and cultural significance..."><?= $edit_dest ? htmlspecialchars($edit_dest['history']) : '' ?></textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Location & Contact Info</label>
                        <input type="text" name="location_contact" class="form-control" placeholder="e.g. Cape Coast-Twifo Praso Road | +233 33 213 2260" value="<?= $edit_dest ? sanitize($edit_dest['location_contact']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Main Cover Image</label>
                        <input type="file" name="image_main" class="form-control" accept="image/*">
                        <?php if ($edit_dest && !empty($edit_dest['image_main'])): ?>
                            <small class="text-muted d-block mt-1">Current: <?= sanitize($edit_dest['image_main']) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Multiple Carousel / Secondary Images</label>
                        <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted d-block mt-1">Select one or multiple images for the carousel.</small>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" <?= ($edit_dest && $edit_dest['is_featured'] == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="is_featured">Featured Site</label>
                        </div>
                    </div>
                </div>

                <!-- EXISTING GALLERY IMAGES PREVIEW (If Editing) -->
                <?php if (!empty($edit_gallery_images)): ?>
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold mb-3"><i class="fa-solid fa-images text-warning me-2"></i> Uploaded Carousel Gallery Images (<?= count($edit_gallery_images) ?>)</h6>
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach ($edit_gallery_images as $g_img): 
                                $g_url = get_image_url($g_img['image_path'], '../' . $g_img['image_path']);
                            ?>
                                <div class="position-relative text-center border rounded p-1 bg-white" style="width: 110px;">
                                    <img src="<?= $g_url ?>" class="rounded mb-1" style="width: 100px; height: 75px; object-fit: cover;" alt="Gallery">
                                    <br>
                                    <a href="manage-destinations.php?action=delete_img&id=<?= $edit_id ?>&img_id=<?= $g_img['id'] ?>" class="btn btn-xs btn-danger w-100 py-0" style="font-size:11px;" onclick="return confirm('Remove this image from carousel gallery?');">
                                        <i class="fa-solid fa-trash me-1"></i> Delete
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" name="save_destination" class="btn btn-digitour-primary fw-bold">
                        <i class="fa-solid fa-save me-1"></i> Save Destination & Gallery
                    </button>
                    <a href="manage-destinations.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- DESTINATIONS TABLE LISTING -->
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="fw-bold text-dark mb-0">
            <i class="fa-solid fa-list text-success me-2"></i> Destinations (<span id="destCount"><?= count($all_destinations) ?></span>)
        </h5>
        <div class="admin-toolbar-actions">
            <div class="input-group input-group-sm admin-search">
                <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                <input type="text" id="destSearchInput" class="form-control" placeholder="Search title, region…" onkeyup="filterDestinationsTable()">
            </div>
            <a href="export-csv.php?type=destinations" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-file-csv me-1"></i> CSV</a>
            <a href="manage-destinations.php?action=add" class="btn btn-sm btn-digitour-primary"><i class="fa-solid fa-plus me-1"></i> Add</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="destinationsTable">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Attraction</th>
                    <th>Region</th>
                    <th>Category</th>
                    <th>Hotels</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="destTableBody">
                <?php if (empty($all_destinations)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No destinations available. Click Add above.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($all_destinations as $d): ?>
                        <tr>
                            <td>
                                <img src="<?= get_destination_main_image($d['title'], $d['image_main']) ?>" class="admin-thumb" alt="">
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?= sanitize($d['title']) ?></strong>
                                <small class="text-secondary"><?= dt_short_desc($d['description'], 65) ?></small>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= sanitize($d['region']) ?></span></td>
                            <td><span class="badge bg-success-subtle text-success"><?= sanitize($d['category']) ?></span></td>
                            <td><span class="badge bg-warning text-dark"><i class="fa-solid fa-hotel me-1"></i><?= (int)$d['hotel_count'] ?></span></td>
                            <td>
                                <?php if ($d['is_featured']): ?>
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-star"></i></span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <a href="manage-destinations.php?action=edit&id=<?= (int)$d['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                    <a href="manage-destinations.php?action=delete&id=<?= (int)$d['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this destination? Linked hotels will also be removed.');"><i class="fa-solid fa-trash"></i></a>
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
function filterDestinationsTable() {
    let input = document.getElementById('destSearchInput').value.toLowerCase().trim();
    let rows = document.querySelectorAll('#destTableBody tr');
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
    let countSpan = document.getElementById('destCount');
    if (countSpan) countSpan.innerText = visibleCount;
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
