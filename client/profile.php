<?php
session_start();

// Check if client is logged in
if (!isset($_SESSION['client']) || $_SESSION['user_type'] !== 'client') {
    header("Location: ../signin.php");
    exit();
}

require_once("../php/Client.php");

// Get client information
$client_email = $_SESSION['username'];
$client_info = null;

try {
    $pdo = new PDO("mysql:host=localhost;dbname=stock2025", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM client WHERE email = :email");
    $stmt->bindParam(':email', $client_email);
    $stmt->execute();
    $client_info = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

$active = array(0, 0, 0, 0);
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
                                         class="profile-image" alt="Profile Picture"
                                         onerror="this.src='../assets/img/user-placeholder.jpg'">
                                <?php else: ?>
                                    <img src="../assets/img/user-placeholder.jpg" 
                                         class="profile-image" alt="Profile Picture">
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
                                <button class="btn btn-primary" onclick="editProfile()">
                                    <i class="fas fa-edit"></i> Edit Profile
                                </button>
                                <a href="orders.php" class="btn btn-outline-primary">
                                    <i class="fas fa-shopping-bag"></i> View Orders
                                </a>
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
        // Hide global loader when page is loaded
        $(window).on('load', function() {
            $('#global-loader').fadeOut('slow');
        });

        function editProfile() {
            alert('Profile editing feature will be available soon!');
        }
    </script>
</body>
</html>
