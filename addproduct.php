<?php
session_start();
?>
<?php if (isset($_SESSION['admin'])): ?>
<?php
  require_once("../stock2025/php/Product.php");
  require_once("../stock2025/php/Categorie.php");
  require_once("../stock2025/php/Marque.php");
  $cats = Categorie::afficher("categorie");
  $brands = Marque::afficher("marque");
  $active = array(0, 0, 0, 0, 0, 0, "active", 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
  if (isset($_POST['add'])) {
    extract($_POST);
    
    // Server-side validation
    $errors = [];
    
    // Check if all required fields are filled
    if (empty($num_pr)) $errors[] = "Product reference is required";
    if (empty($lib_pr)) $errors[] = "Product name is required";
    if (empty($id_cat)) $errors[] = "Category is required";
    if (empty($id_marque)) $errors[] = "Brand is required";
    if (empty($qte_stock)) $errors[] = "Quantity is required";
    if (empty($prix_achat)) $errors[] = "Purchase price is required";
    if (empty($prix_uni)) $errors[] = "Unit price is required";
    if (empty($_FILES["image"]["name"])) $errors[] = "Product image is required";
    
    // Numeric validation
    if (!empty($qte_stock) && (!is_numeric($qte_stock) || $qte_stock < 0)) {
      $errors[] = "Quantity must be a positive number";
    }
    
    if (!empty($prix_achat) && (!is_numeric($prix_achat) || $prix_achat <= 0)) {
      $errors[] = "Purchase price must be a positive number";
    }
    
    if (!empty($prix_uni) && (!is_numeric($prix_uni) || $prix_uni <= 0)) {
      $errors[] = "Unit price must be a positive number";
    }
    
    // Price validation - unit price should be higher than purchase price
    if (!empty($prix_achat) && !empty($prix_uni) && is_numeric($prix_achat) && is_numeric($prix_uni)) {
      if ($prix_uni <= $prix_achat) {
        $errors[] = "Unit price must be higher than purchase price";
      }
    }
    
    // If there are validation errors, display them
    if (!empty($errors)) {
      echo "<div class='alert alert-danger'><ul>";
      foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
      }
      echo "</ul></div>";
    } else {
      // Proceed with file upload and product creation
      $filename = $_FILES["image"]["name"];
      $tempname = $_FILES["image"]["tmp_name"];
      $image = "./image/product/" . $filename;

      if (move_uploaded_file($tempname, $image)) {
        $nv_pr = new Product($num_pr, $id_cat, $id_marque, $lib_pr, $desc_pr, $prix_uni, $prix_achat, $qte_stock, $image);
        $nv_pr->addPr();
        echo "<div class='alert alert-success'>Product added successfully!</div>";
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
  <title>Add Product</title>

  <link rel="shortcut icon" type="image/x-icon" href="assets/img/fav1.jpg" />

  <link rel="stylesheet" href="assets/css/bootstrap.min.css" />

  <link rel="stylesheet" href="assets/css/animate.css" />

  <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css" />

  <link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css" />

  <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css" />
  <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css" />

  <link rel="stylesheet" href="assets/css/style.css" />

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
            <h4>Add Product</h4>
            <h6>Create new product</h6>
          </div>
        </div>
        <div class="card">
          <div class="card-body">
            <form class="row" method="post" action="addproduct.php" enctype="multipart/form-data">
              <div class="col-lg-3 col-sm-6 col-12">
                <div class="form-group">
                  <label>Reference <span class="text-danger">*</span></label>
                  <input type="text" name="num_pr" class="form-control" required />
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 col-12">
                <div class="form-group">
                  <label>Product Name <span class="text-danger">*</span></label>
                  <input type="text" name="lib_pr" class="form-control" required />
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 col-12">
                <div class="form-group">
                  <label>Category <span class="text-danger">*</span></label>
                  <select class="select" name="id_cat" required>
                    <option value="">Choose Category</option>
                    <?php foreach ($cats as $item): ?>
                    <option value="<?= $item['id_cat']; ?>">
                      <?= $item['lib_cat']; ?>
                    </option>
                    <?php endforeach ?>
                  </select>
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 col-12">
                <div class="form-group">
                  <label>Brand <span class="text-danger">*</span></label>
                  <select class="select" name="id_marque" required>
                    <option value="">Choose Brand</option>
                    <?php foreach ($brands as $item): ?>
                    <option value="<?= $item['id_marque']; ?>">
                      <?= $item['nom_marque']; ?>
                    </option>
                    <?php endforeach ?>
                  </select>
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 col-12">
                <div class="form-group">
                  <label>Quantity <span class="text-danger">*</span></label>
                  <input type="number" name="qte_stock" class="form-control" min="0" step="1" required />
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 col-12">
                <div class="form-group">
                  <label>Purchase Price (DH) <span class="text-danger">*</span></label>
                  <input type="number" name="prix_achat" class="form-control" min="0" step="0.01" required />
                </div>
              </div>
              <div class="col-lg-3 col-sm-6 col-12">
                <div class="form-group">
                  <label>Unit Price (DH) <span class="text-danger">*</span></label>
                  <input type="number" name="prix_uni" class="form-control" min="0" step="0.01" required />
                </div>
              </div>

              <div class="col-lg-3 col-sm-6 col-12">
                <div class="form-group">
                  <label>Description</label>
                  <input type="text" name="desc_pr" class="form-control" />
                </div>
              </div>
              <div class="col-lg-12">
                <div class="form-group">
                  <label> Product Image <span class="text-danger">*</span></label>
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
                <button class="btn btn-submit me-2" name="add">Add product</button>
                <a href="productlist.php" class="btn btn-cancel">Cancel</a>
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
      const purchasePriceInput = document.querySelector('input[name="prix_achat"]');
      const unitPriceInput = document.querySelector('input[name="prix_uni"]');
      const quantityInput = document.querySelector('input[name="qte_stock"]');
      
      // Price validation function
      function validatePrices() {
        const purchasePrice = parseFloat(purchasePriceInput.value) || 0;
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        
        if (purchasePrice > 0 && unitPrice > 0 && unitPrice <= purchasePrice) {
          unitPriceInput.setCustomValidity('Unit price must be higher than purchase price');
          return false;
        } else {
          unitPriceInput.setCustomValidity('');
          return true;
        }
      }
      
      // Real-time price validation
      purchasePriceInput.addEventListener('input', validatePrices);
      unitPriceInput.addEventListener('input', validatePrices);
      
      // Quantity validation
      quantityInput.addEventListener('input', function() {
        const quantity = parseInt(this.value) || 0;
        if (quantity < 0) {
          this.setCustomValidity('Quantity must be a positive number');
        } else {
          this.setCustomValidity('');
        }
      });
      
      // Form submission validation
      form.addEventListener('submit', function(e) {
        let isValid = true;
        const errors = [];
        
        // Check all required fields
        const requiredFields = form.querySelectorAll('input[required], select[required]');
        requiredFields.forEach(function(field) {
          if (!field.value.trim()) {
            isValid = false;
            const label = field.previousElementSibling.textContent.replace(' *', '');
            errors.push(label + ' is required');
          }
        });
        
        // Validate prices
        if (!validatePrices()) {
          isValid = false;
          errors.push('Unit price must be higher than purchase price');
        }
        
        // Validate numeric fields
        const purchasePrice = parseFloat(purchasePriceInput.value) || 0;
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        const quantity = parseInt(quantityInput.value) || 0;
        
        if (purchasePrice <= 0) {
          isValid = false;
          errors.push('Purchase price must be greater than 0');
        }
        
        if (unitPrice <= 0) {
          isValid = false;
          errors.push('Unit price must be greater than 0');
        }
        
        if (quantity < 0) {
          isValid = false;
          errors.push('Quantity must be a positive number');
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