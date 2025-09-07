<?php
session_start();
// Normalize admin session data to avoid accessing array offsets on bool/null
$adminData = (isset($_SESSION['admin']) && is_array($_SESSION['admin'])) ? $_SESSION['admin'] : null;
$adminImage = ($adminData && !empty($adminData['image'])) ? $adminData['image'] : 'assets/img/img-01.jpg';
$adminNom = $adminData['nom'] ?? '';
$adminPrenom = $adminData['prenom'] ?? '';
$adminId = $adminData['id'] ?? '';
if (isset($_GET['id'])) {
    require_once("../stock2025/php/Admin.php");
    extract($_GET);
    Admin::supprimer($id, "admin");
    header("location: logout.php");
}
?>
<style>
    .logo{
        max-width:80px;
        display: block;

    }
    /* Dropdown overrides for admin menu */
    .dropdown-menu.menu-drop-user{left:auto;right:0;min-width:260px;padding:12px;border:1px solid #eee;box-shadow:0 8px 24px rgba(0,0,0,0.08);}
    .profileset{display:flex;align-items:center;gap:12px;}
    .user-img img{width:40px;height:40px;object-fit:cover;border-radius:50%;}
    .profilesets h6{margin:0;font-size:14px;font-weight:600;}
    .profilesets h5{margin:2px 0 0;font-size:12px;color:#6c757d;}
    .menu-drop-user .dropdown-item{display:flex;align-items:center;gap:8px;padding:8px 10px;font-size:14px;}
    .menu-drop-user hr{margin:8px 0;}
    </style>
    

<div class="header">

    <div class="header-left active">
        <a href="index.php" class="logo" >
            <img src="assets/img/logo1.jpg" alt="" > 
            <br>
        </a>
    
        <a href="index.php" class="logo-small">
            <img src="assets/img/logo-small.png" alt="">
        </a>
        <a id="toggle_btn" href="javascript:void(0);">
        </a>
    </div>
    <br>

    <a id="mobile_btn" class="mobile_btn" href="#sidebar">
        <span class="bar-icon">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </a>

    <ul class="nav user-menu">
        <li class="nav-item dropdown has-arrow main-drop">
            <a href="javascript:void(0);" class="dropdown-toggle nav-link userset" data-bs-toggle="dropdown">
                <span class="user-img">
                    <img style="object-fit:cover;margin-top:0px;" src="<?= htmlspecialchars($adminImage) ?>" alt="">
                    <span class="status online"></span>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-end menu-drop-user">
                <div class="profilename">
                    <div class="profileset">
                        <span class="user-img">
                            <img src="<?= htmlspecialchars($adminImage) ?>" alt="">
                            <span class="status online"></span>
                        </span>
                        <div class="profilesets">
                            <h6>
                                <?= htmlspecialchars(trim($adminNom . " " . $adminPrenom)) ?>
                            </h6>
                            <h5>Admin</h5>
                        </div>
                    </div>
                    <hr class="m-0">
                    <a class="dropdown-item" href="profile.php"> <i class="me-2" data-feather="user"></i> My
                        Profile</a>
                    <hr class="m-0">
                    <a class="dropdown-item logout pb-0" href="header.php?id=<?= htmlspecialchars($adminId) ?>">
                        <img style="width:20px;" src="assets/img/icons/delete.svg" class="me-2" alt="delete">
                        Delete my account
                    </a>
                    <a class="dropdown-item logout pb-0" href="logout.php">
                        <img style="width:20px;" src="assets/img/icons/log-out.svg" class="me-2" alt="logout">
                        Logout
                    </a>
                </div>
            </div>
        </li>
    </ul>


    <div class="dropdown mobile-user-menu">
        <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
            aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
        <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="profile.html">My Profile</a>
            <a class="dropdown-item" href="signin.php">Logout</a>
        </div>
    </div>

</div>