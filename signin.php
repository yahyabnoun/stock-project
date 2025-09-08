<?php
session_start();
require_once("../stock2025/php/Admin.php");
require_once("../stock2025/php/Client.php");
// Traitement de la connexion
if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';

    // Logique de vérification des identifiants (à adapter selon votre base de données)

    if ($user_type === 'admin') {
        // Vérification pour admin

        $admin_result = Admin::estAdmin($username, $password);

        if (is_array($admin_result)) {
            $_SESSION['admin'] = $admin_result;
            $_SESSION['user_type'] = 'admin';
            $_SESSION['username'] = $username;
            header("Location: index.php"); // Redirection vers le tableau de bord admin
            exit();
        }
    } elseif ($user_type === 'client') {
        // Vérification pour client

        $client = Client::authenticateSimple($username, $password);

        if ($client) {
            $_SESSION['client'] = true;
            $_SESSION['user_type'] = 'client';
            $_SESSION['username'] = $username;
            header("Location: client/index.php"); // Redirection vers le tableau de bord client
            exit();
        }
    }

    $error_message = "Identifiants incorrects ou type d'utilisateur invalide.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Haila Stock</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/fav1.jpg">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        
        .user-type-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .user-type-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-weight: 500;
        }
        
        .user-type-btn.active {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }
        
        .user-type-btn:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        
        .user-type-btn i {
            display: block;
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            height: 50px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 0 15px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            color: white;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Connexion</h2>
                <p>Choisissez votre type de compte et connectez-vous</p>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?= $error_message ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <div class="user-type-buttons">
                    <div class="user-type-btn" data-type="admin">
                        <i class="fas fa-user-shield"></i>
                        <span>Administrateur</span>
                    </div>
                    <div class="user-type-btn" data-type="client">
                        <i class="fas fa-user"></i>
                        <span>Client</span>
                    </div>
                </div>
                
                <input type="hidden" name="user_type" id="userType" value="">
                
                <div class="form-group">
                    <input type="text" class="form-control" name="username" placeholder="Nom d'utilisateur" required>
                </div>
                
                <div class="form-group">
                    <input type="password" class="form-control" name="password" placeholder="Mot de passe" required>
                </div>
                
                <button type="submit" class="btn btn-login" id="submitBtn" disabled>
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>
            
            <div class="mt-4 text-center">
                <small class="text-muted">
                    <strong>Comptes de test :</strong><br>
                    Admin: admin / admin123<br>
                    Client: client / client123
                </small>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Gestion des boutons de type d'utilisateur
            $('.user-type-btn').click(function() {
                $('.user-type-btn').removeClass('active');
                $(this).addClass('active');
                
                const userType = $(this).data('type');
                $('#userType').val(userType);
                $('#submitBtn').prop('disabled', false);
                
                // Mise à jour du texte du bouton
                if (userType === 'admin') {
                    $('#submitBtn').html('<i class="fas fa-user-shield"></i> Connexion Administrateur');
                } else {
                    $('#submitBtn').html('<i class="fas fa-user"></i> Connexion Client');
                }
            });
            
            // Validation du formulaire
            $('#loginForm').submit(function(e) {
                if (!$('#userType').val()) {
                    e.preventDefault();
                    alert('Veuillez sélectionner un type d\'utilisateur');
                }
            });
        });
    </script>
</body>
</html>