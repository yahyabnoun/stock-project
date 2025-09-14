<?php
session_start();
?>
<?php if (isset($_SESSION['admin'])): ?>
<?php
  require_once("../stock2025/php/Supplier.php");
  $active = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, "active", 0, 0);
  if (isset($_POST['submit'])) {
    extract($_POST);
    
    // Server-side validation
    $errors = [];
    
    // Check if all required fields are filled
    if (empty($nom)) $errors[] = "First name is required";
    if (empty($prenom)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($tele)) $errors[] = "Phone number is required";
    if (empty($adr)) $errors[] = "Address is required";
    if (empty($_FILES["image"]["name"])) $errors[] = "Avatar image is required";
    
    // Email validation
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = "Please enter a valid email address";
    }
    
    // Moroccan phone number validation
    if (!empty($tele) && !preg_match('/^(\+212|0)[5-7][0-9]{8}$/', $tele)) {
      $errors[] = "Please enter a valid Moroccan phone number (e.g., +212612345678 or 0612345678)";
    }
    
    // If there are validation errors, display them
    if (!empty($errors)) {
      echo "<div class='alert alert-danger'><ul>";
      foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
      }
      echo "</ul></div>";
    } else {
      // Proceed with file upload and supplier creation
      $filename = $_FILES["image"]["name"];
      $tempname = $_FILES["image"]["tmp_name"];
      $image = "./image/supplier/" . $filename;

      if (move_uploaded_file($tempname, $image)) {
        $Supplier = new Supplier($nom, $prenom, $adr, $tele, $email, $image);
        $Supplier->Ajouter("fournisseur");
        echo "<div class='alert alert-success'>Supplier added successfully!</div>";
      } else {
        echo "<div class='alert alert-danger'>Failed to upload image!</div>";
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
  <meta name="description" content="POS - Bootstrap Admin Template" />
  <meta name="keywords"
    content="admin, estimates, bootstrap, business, corporate, creative, invoice, html5, responsive, Projects" />
  <meta name="author" content="Dreamguys - Bootstrap Admin Template" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Add Supplier</title>

  <link rel="shortcut icon" type="image/x-icon" href="assets/img/fav1.jpg" />

  <link rel="stylesheet" href="assets/css/bootstrap.min.css" />

  <link rel="stylesheet" href="assets/css/animate.css" />

  <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css" />

  <link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css" />

  <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css" />
  <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css" />

  <link rel="stylesheet" href="assets/css/style.css" />
  <style>
    @media (min-width: 992px) {
      .col-lg-3 {
        flex: 0 0 auto;
        width: 33%;
      }

      .col-lg-9 {
        flex: 0 0 auto;
        width: 67%;
      }
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
          <div class="page-title">
            <h4>Add Supplier</h4>
            <h6>Add New Supplier</h6>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <form class="row" method="post" action="addsupplier.php" enctype="multipart/form-data">
              <div class="col-lg-3 col-sm-6 col-12">
                <div class="form-group">
                  <label>Supplier last name <span class="text-danger">*</span></label>
                  <input type="text" name="prenom" class="form-control" required />
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 col-12">
                <div class="form-group">
                  <label>Supplier first name <span class="text-danger">*</span></label>
                  <input type="text" name="nom" class="form-control" required />
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 col-12">
                <div class="form-group">
                  <label>E-mail <span class="text-danger">*</span></label>
                  <input type="email" name="email" class="form-control" required pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Please enter a valid email address" />
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 col-12">
                <div class="form-group">
                  <label>Phone <span class="text-danger">*</span></label>
                  <input type="tel" name="tele" class="form-control" required pattern="^(\+212|0)[5-7][0-9]{8}$" title="Please enter a valid Moroccan phone number (e.g., +212612345678 or 0612345678)" />
                </div>
              </div>
              <div class="col-lg-9 col-12">
                <div class="form-group">
                  <label>Address <span class="text-danger">*</span></label>
                  <input type="text" name="adr" class="form-control" required />
                </div>
              </div>
              <div class="col-lg-12">
                <div class="form-group">
                  <label> Avatar <span class="text-danger">*</span></label>
                  <div class="image-upload">
                    <input type="file" name="image" required accept="image/*" />
                    <div class="image-uploads">
                      <img src="assets/img/icons/upload.svg" alt="img" />
                      <h4>Drag and drop a file to upload</h4>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-12">
                <button class="btn btn-submit me-2" name="submit">Add</button>
                <a href="supplierlist.php" class="btn btn-cancel">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/jquery-3.6.0.min.js"></script>

  <script src="assets/js/feather.min.js"></script>

  <script src="assets/js/jquery.slimscroll.min.js"></script>

  <script src="assets/js/jquery.dataTables.min.js"></script>
  <script src="assets/js/dataTables.bootstrap4.min.js"></script>

  <script src="assets/js/bootstrap.bundle.min.js"></script>

  <script src="assets/plugins/select2/js/select2.min.js"></script>

  <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
  <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>

  <script src="assets/js/script.js"></script>
  
  <script>
    // Client-side validation
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('form');
      const emailInput = document.querySelector('input[name="email"]');
      const phoneInput = document.querySelector('input[name="tele"]');
      
      // Email validation function
      function validateEmail(email) {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return emailRegex.test(email);
      }
      
      // Moroccan phone validation function
      function validateMoroccanPhone(phone) {
        const phoneRegex = /^(\+212|0)[5-7][0-9]{8}$/;
        return phoneRegex.test(phone);
      }
      
      // Real-time email validation
      emailInput.addEventListener('blur', function() {
        const email = this.value.trim();
        if (email && !validateEmail(email)) {
          this.setCustomValidity('Please enter a valid email address');
          this.reportValidity();
        } else {
          this.setCustomValidity('');
        }
      });
      
      // Real-time phone validation
      phoneInput.addEventListener('blur', function() {
        const phone = this.value.trim();
        if (phone && !validateMoroccanPhone(phone)) {
          this.setCustomValidity('Please enter a valid Moroccan phone number (e.g., +212612345678 or 0612345678)');
          this.reportValidity();
        } else {
          this.setCustomValidity('');
        }
      });
      
      // Form submission validation
      form.addEventListener('submit', function(e) {
        let isValid = true;
        const errors = [];
        
        // Check all required fields
        const requiredFields = form.querySelectorAll('input[required]');
        requiredFields.forEach(function(field) {
          if (!field.value.trim()) {
            isValid = false;
            errors.push(field.previousElementSibling.textContent.replace(' *', '') + ' is required');
          }
        });
        
        // Validate email
        const email = emailInput.value.trim();
        if (email && !validateEmail(email)) {
          isValid = false;
          errors.push('Please enter a valid email address');
        }
        
        // Validate phone
        const phone = phoneInput.value.trim();
        if (phone && !validateMoroccanPhone(phone)) {
          isValid = false;
          errors.push('Please enter a valid Moroccan phone number (e.g., +212612345678 or 0612345678)');
        }
        
        if (!isValid) {
          e.preventDefault();
          alert('Please fix the following errors:\n\n' + errors.join('\n'));
        }
      });
    });
  </script>
</body>

</html>
<?php else: ?>
<?php header("Location: signin.php"); ?>
<?php endif ?>