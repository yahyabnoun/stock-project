
<style>
    .logo{
        max-width:80px;
        display: block;
    }
    
    .header-right {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .header-right .dropdown-toggle {
        color: #333;
        text-decoration: none;
        padding: 10px;
        border-radius: 50%;
        transition: background-color 0.3s ease;
    }
    
    .header-right .dropdown-toggle:hover {
        background-color: #f8f9fa;
        color: #007bff;
    }
    
    .header-right .dropdown-menu {
        min-width: 180px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border: none;
        border-radius: 8px;
    }
    
    .header-right .dropdown-item {
        padding: 10px 15px;
        color: #333;
        transition: background-color 0.3s ease;
    }
    
    .header-right .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #007bff;
    }
    
    .header-right .dropdown-item i {
        margin-right: 8px;
        width: 16px;
    }
</style>
    

<div class="header">

    <div class="header-left active">
        <a href="index.php" class="logo" >
            <img src="../assets/img/logo1.jpg" alt="" > 
            <br>
        </a>

        <a href="index.php" class="logo-small">
            <img src="../assets/img/logo-small.png" alt="">
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




    <div class="header-right">
        <div class="dropdown">
            <a href="javascript:void(0);" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle fa-2x"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="profile.php">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a class="dropdown-item" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

</div>