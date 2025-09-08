<?php
session_start();

// Check if client is logged in
if (!isset($_SESSION['client']) || $_SESSION['user_type'] !== 'client') {
    header("Location: ../signin.php");
    exit();
}

require_once("../php/Client.php");
require_once("../php/Dao.php");

// Get client information
$client_email = $_SESSION['username'];
$client_info = null;

try {
    $pdo = Dao::getPDO();
    $stmt = $pdo->prepare("SELECT id, nom, prenom, adr, tele, email, image FROM client WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $client_email);
    $stmt->execute();
    $client_info = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

$active = array(0, 0, 0, 0);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile']) && $client_info) {
    $new_nom = trim($_POST['nom'] ?? $client_info['nom']);
    $new_prenom = trim($_POST['prenom'] ?? $client_info['prenom']);
    $new_email = trim($_POST['email'] ?? $client_info['email']);
    $new_tele = trim($_POST['tele'] ?? $client_info['tele']);
    $new_adr = trim($_POST['adr'] ?? $client_info['adr']);

    // Default to old image path in DB format (e.g., ./image/client/...) 
    $new_image_db = $client_info['image'];

    if (!empty($_FILES['image']['name'])) {
        $filename = basename($_FILES['image']['name']);
        $tempname = $_FILES['image']['tmp_name'];
        // Filesystem path from client/ folder
        $fs_target = "../image/client/" . $filename;
        // DB path stored consistently relative to project root
        $db_target = "./image/client/" . $filename;

        if (move_uploaded_file($tempname, $fs_target)) {
            // Delete old file if exists (translate DB path to filesystem path)
            $old_fs_path = '../' . ltrim($client_info['image'], './');
            if ($old_fs_path && file_exists($old_fs_path) && is_file($old_fs_path)) {
                @unlink($old_fs_path);
            }
            $new_image_db = $db_target;
        } else {
            exit('<h3> Failed to upload image!</h3>');
        }
    }

    // Update in DB
    Personne::modifier($client_info['id'], $new_nom, $new_prenom, $new_adr, $new_tele, $new_email, $new_image_db, 'client');

    // If email changed, refresh session username
    if ($new_email !== $client_email) {
        $_SESSION['username'] = $new_email;
        $client_email = $new_email;
    }

    // Reload client info
    try {
        $pdo = Dao::getPDO();
        $stmt = $pdo->prepare("SELECT id, nom, prenom, adr, tele, email, image FROM client WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $client_email);
        $stmt->execute();
        $client_info = $stmt->fetch(PDO::FETCH_ASSOC) ?: $client_info;
    } catch (PDOException $e) {
        error_log('Database error after update: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>My Profile - Haila Stock</title>
    
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/fav1.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .profile-card {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #f8f9fa;
            margin: 0 auto 20px;
            display: block;
        }
        .profile-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div id="global-loader">
        <div class="whirly-loader"></div>
    </div>

    <div class="main-wrapper">
        <?php require_once("header.php"); ?>
        <?php require_once("sidebar.php"); ?>

        <div class="page-wrapper">
            <div class="content">
                <div class="page-header">
                    <div class="add-item d-flex">
                        <div class="page-title">
                            <h4>My Profile</h4>
                        </div>
                    </div>
                    <ul class="table-top-head">
                        <li>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="profile-card">
                            <div class="text-center">
                                <?php if ($client_info && !empty($client_info['image'])): ?>
                                    <img src="../<?= ltrim(htmlspecialchars($client_info['image']), './') ?>" 
                                         class="profile-image" alt="Profile Picture" loading="lazy"
                                         onerror="this.src='../assets/img/user-placeholder.jpg'">
                                <?php else: ?>
                                    <img src="../assets/img/user-placeholder.jpg" 
                                         class="profile-image" alt="Profile Picture" loading="lazy">
                                <?php endif; ?>
                                
                                <h3 class="mb-1">
                                    <?= htmlspecialchars($client_info['prenom'] . ' ' . $client_info['nom']) ?>
                                </h3>
                                <p class="text-muted mb-4">Customer Account</p>
                            </div>

                            <div class="profile-info">
                                <h5 class="mb-3"><i class="fas fa-user"></i> Personal Information</h5>
                                
                                <div class="info-item">
                                    <span class="info-label">First Name:</span>
                                    <span class="info-value"><?= htmlspecialchars($client_info['prenom']) ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Last Name:</span>
                                    <span class="info-value"><?= htmlspecialchars($client_info['nom']) ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Email:</span>
                                    <span class="info-value"><?= htmlspecialchars($client_info['email']) ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Phone:</span>
                                    <span class="info-value"><?= htmlspecialchars($client_info['tele']) ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Address:</span>
                                    <span class="info-value"><?= htmlspecialchars($client_info['adr']) ?></span>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#editForm" aria-expanded="false" aria-controls="editForm">
                                    <i class="fas fa-edit"></i> Edit Profile
                                </button>
                                <a href="orders.php" class="btn btn-outline-primary">
                                    <i class="fas fa-shopping-bag"></i> View Orders
                                </a>
                            </div>
                        </div>
                        <div id="editForm" class="collapse">
                            <div class="profile-card">
                                <h5 class="mb-3"><i class="fas fa-user-cog"></i> Edit Profile</h5>
                                <form method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="update_profile" value="1" />
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>First Name</label>
                                                <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($client_info['prenom']) ?>" required />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Last Name</label>
                                                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($client_info['nom']) ?>" required />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($client_info['email']) ?>" required />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone</label>
                                                <input type="text" name="tele" class="form-control" value="<?= htmlspecialchars($client_info['tele']) ?>" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>Address</label>
                                                <input type="text" name="adr" class="form-control" value="<?= htmlspecialchars($client_info['adr']) ?>" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>Avatar</label>
                                                <input type="file" name="image" class="form-control" accept="image/*" />
                                            </div>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>

    <script>
        // Hide loader as soon as DOM is ready; images keep loading lazily
        $(function() {
            $('#global-loader').fadeOut('slow');
        });

        // Auto scroll to bottom when edit form is shown
        $('#editForm').on('shown.bs.collapse', function () {
            $('html, body').animate({
                scrollTop: $(this).offset().top
            }, 600);
        });
    </script>
</body>
</html>
