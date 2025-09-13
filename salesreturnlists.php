<?php
session_start();
?>
<?php if (isset($_SESSION['admin'])): ?>
<?php
  require_once("../stock2025/php/Sale.php");
  $active = array(0, 0, 0, 0, 0, 0, 0, "active", 0, 0, 0, 0, 0, 0, 0, 0, 0);
  if (isset($_GET["num_com"])) {
    extract($_GET);
    Sale::deleteSale($num_com);
  }
  
  // Get search and filter parameters
  $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
  $dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
  $dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
  $amountFrom = isset($_GET['amount_from']) ? $_GET['amount_from'] : '';
  $amountTo = isset($_GET['amount_to']) ? $_GET['amount_to'] : '';
  
  // Use search method if any filters are applied, otherwise use recent sales
  if (!empty($searchTerm) || !empty($dateFrom) || !empty($dateTo) || !empty($amountFrom) || !empty($amountTo)) {
    $sales = Sale::searchSales($searchTerm, $dateFrom, $dateTo, $amountFrom, $amountTo);
  } else {
    $sales = Sale::displayRecentSales();
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
  <title>Sales List</title>

  <link rel="shortcut icon" type="image/x-icon" href="assets/img/fav1.jpg" />

  <link rel="stylesheet" href="assets/css/bootstrap.min.css" />

  <link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css" />

  <link rel="stylesheet" href="assets/css/animate.css" />

  <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css" />

  <link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css" />

  <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css" />
  <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css" />

  <link rel="stylesheet" href="assets/css/style.css" />
  
  <style>
    .order-date {
      color: #6c757d;
      font-size: 0.9rem;
    }
    .order-date i {
      margin-right: 5px;
      color: #007bff;
    }
    .recent-badge {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 0.7rem;
      font-weight: 500;
      margin-left: 8px;
    }
    .table tbody tr:hover {
      background-color: #f8f9fa;
    }
    .productimgname img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
    }
     .total-amount {
       font-weight: 600;
       color: #28a745;
     }
     .search-filter-form {
       background: #f8f9fa;
       padding: 20px;
       border-radius: 10px;
       margin-bottom: 20px;
     }
     .search-filter-form .form-label {
       font-weight: 600;
       color: #495057;
       margin-bottom: 5px;
     }
     .search-filter-form .form-control {
       border: 1px solid #dee2e6;
       border-radius: 6px;
       padding: 8px 12px;
     }
     .search-filter-form .form-control:focus {
       border-color: #007bff;
       box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
     }
     .input-group .btn {
       border-radius: 0 6px 6px 0;
     }
     .filter-buttons .btn {
       min-width: 40px;
     }
     .results-summary {
       background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
       border: 1px solid #bbdefb;
       border-radius: 8px;
       padding: 15px;
       margin-bottom: 20px;
     }
     .no-results {
       text-align: center;
       padding: 40px 20px;
       color: #6c757d;
     }
     .no-results i {
       font-size: 3rem;
       margin-bottom: 15px;
       opacity: 0.5;
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
            <h4>Sales List</h4>
            <h6>Manage Your Sales</h6>
          </div>
          <div class="page-btn">
            <a href="createsalesreturns.php" class="btn btn-added"><img src="assets/img/icons/plus.svg" alt="img"
                class="me-2" />Add New Sale</a>
          </div>
        </div>

         <div class="card">
           <div class="card-body">
             <!-- Search and Filter Form -->
             <form method="GET" action="salesreturnlists.php" class="search-filter-form">
               <div class="row mb-4">
                 <div class="col-lg-3 col-md-6 mb-3">
                   <div class="form-group">
                     <label class="form-label">Search</label>
                     <div class="input-group">
                       <input type="text" name="search" class="form-control" 
                              placeholder="Search by order #, customer name..." 
                              value="<?= htmlspecialchars($searchTerm) ?>">
                       <button type="submit" class="btn btn-primary">
                         <i class="fas fa-search"></i>
                       </button>
                     </div>
                   </div>
                 </div>
                 
                 <div class="col-lg-2 col-md-6 mb-3">
                   <div class="form-group">
                     <label class="form-label">Date From</label>
                     <input type="date" name="date_from" class="form-control" 
                            value="<?= htmlspecialchars($dateFrom) ?>">
                   </div>
                 </div>
                 
                 <div class="col-lg-2 col-md-6 mb-3">
                   <div class="form-group">
                     <label class="form-label">Date To</label>
                     <input type="date" name="date_to" class="form-control" 
                            value="<?= htmlspecialchars($dateTo) ?>">
                   </div>
                 </div>
                 
                 <div class="col-lg-2 col-md-6 mb-3">
                   <div class="form-group">
                     <label class="form-label">Amount From (DH)</label>
                     <input type="number" name="amount_from" class="form-control" 
                            placeholder="0.00" step="0.01" min="0"
                            value="<?= htmlspecialchars($amountFrom) ?>">
                   </div>
                 </div>
                 
                 <div class="col-lg-2 col-md-6 mb-3">
                   <div class="form-group">
                     <label class="form-label">Amount To (DH)</label>
                     <input type="number" name="amount_to" class="form-control" 
                            placeholder="0.00" step="0.01" min="0"
                            value="<?= htmlspecialchars($amountTo) ?>">
                   </div>
                 </div>
                 
                 <div class="col-lg-1 col-md-6 mb-3">
                   <div class="form-group">
                     <label class="form-label">&nbsp;</label>
                     <div class="d-flex gap-2">
                       <button type="submit" class="btn btn-primary">
                         <i class="fas fa-filter"></i>
                       </button>
                       <a href="salesreturnlists.php" class="btn btn-secondary">
                         <i class="fas fa-times"></i>
                       </a>
                     </div>
                   </div>
                 </div>
               </div>
             </form>
             
             <!-- Results Summary -->
             <?php if (!empty($searchTerm) || !empty($dateFrom) || !empty($dateTo) || !empty($amountFrom) || !empty($amountTo)): ?>
               <div class="alert alert-info mb-3">
                 <i class="fas fa-info-circle"></i>
                 <strong>Filtered Results:</strong> 
                 Found <?= count($sales) ?> sale(s) matching your criteria.
                 <a href="salesreturnlists.php" class="btn btn-sm btn-outline-primary ms-2">
                   <i class="fas fa-times"></i> Clear Filters
                 </a>
               </div>
             <?php endif; ?>
             
             <?php if (empty($sales)): ?>
               <div class="no-results">
                 <i class="fas fa-search"></i>
                 <h5>No sales found</h5>
                 <p>No sales match your search criteria. Try adjusting your filters or search terms.</p>
                 <a href="salesreturnlists.php" class="btn btn-primary">
                   <i class="fas fa-refresh"></i> View All Sales
                 </a>
               </div>
             <?php else: ?>
               <div class="table-responsive">
                 <table class="table">
                   <thead>
                     <tr>
                       <th>Sale reference</th>
                       <th>Customer</th>
                       <th>Date</th>
                       <th>Grand Total (DH)</th>
                       <th>Action</th>
                     </tr>
                   </thead>
                   <tbody>
                     <?php foreach ($sales as $sale): ?>
                  <tr>
                    <td><?= $sale['num_com'] ?></td>
                    <td class="productimgname">
                      <a href="javascript:void(0);" class="product-img">
                        <img src="<?= $sale['image'] ?>" alt="product" />
                      </a>
                      <a href="javascript:void(0);"><?= $sale['nom'] . " " . $sale['prenom'] ?></a>
                    </td>
                    <td>
                        <span class="order-date">
                            <i class="fas fa-calendar"></i> 
                            <?= date('M j, Y g:i A', strtotime($sale['date_com'])) ?>
                        </span>
                    </td>
                    <td>
                        <span class="total-amount"><?= number_format($sale['total'], 2) ?> DH</span>
                        <?php 
                        // Show "New" badge for orders within last 24 hours
                        $orderDate = strtotime($sale['date_com']);
                        $currentDate = time();
                        $hoursDiff = ($currentDate - $orderDate) / 3600;
                        if ($hoursDiff <= 24): 
                        ?>
                            <span class="recent-badge">New</span>
                        <?php endif; ?>
                    </td>
                    <td>
                      <a class="me-3" href="sale-details.php?num_com=<?= $sale['num_com'] ?>">
                        <img src="assets/img/icons/eye.svg" alt="img" />
                      </a>
                      <a target="_blank" style="display: inline-block; margin-right:10px;" data-bs-placement="top"
                        title="pdf" href="printPdf.php?num_com=<?= $sale['num_com'] ?>"><img
                          src="assets/img/icons/pdf.svg" alt="img" /></a>
                      <a href="salesreturnlists.php?num_com=<?= $sale['num_com'] ?>" class="me-3">
                        <img src="assets/img/icons/delete.svg" alt="img" />
                      </a>
                    </td>
                     </tr>
                     <?php endforeach ?>
                   </tbody>
                 </table>
               </div>
             <?php endif; ?>
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

  <script src="assets/js/moment.min.js"></script>
  <script src="assets/js/bootstrap-datetimepicker.min.js"></script>

  <script src="assets/plugins/select2/js/select2.min.js"></script>

  <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
  <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>

   <script src="assets/js/script.js"></script>
   
   <script>
     // Enhanced search and filter functionality
     document.addEventListener('DOMContentLoaded', function() {
       const searchForm = document.querySelector('.search-filter-form');
       const searchInput = document.querySelector('input[name="search"]');
       const dateFromInput = document.querySelector('input[name="date_from"]');
       const dateToInput = document.querySelector('input[name="date_to"]');
       const amountFromInput = document.querySelector('input[name="amount_from"]');
       const amountToInput = document.querySelector('input[name="amount_to"]');
       
       // Date validation
       function validateDateRange() {
         const dateFrom = dateFromInput.value;
         const dateTo = dateToInput.value;
         
         if (dateFrom && dateTo && dateFrom > dateTo) {
           alert('Date From cannot be later than Date To');
           dateToInput.focus();
           return false;
         }
         return true;
       }
       
       // Amount validation
       function validateAmountRange() {
         const amountFrom = parseFloat(amountFromInput.value) || 0;
         const amountTo = parseFloat(amountToInput.value) || 0;
         
         if (amountFromInput.value && amountToInput.value && amountFrom > amountTo) {
           alert('Amount From cannot be greater than Amount To');
           amountToInput.focus();
           return false;
         }
         return true;
       }
       
       // Form submission validation
       searchForm.addEventListener('submit', function(e) {
         if (!validateDateRange() || !validateAmountRange()) {
           e.preventDefault();
         }
       });
       
       // Real-time validation
       dateFromInput.addEventListener('change', validateDateRange);
       dateToInput.addEventListener('change', validateDateRange);
       amountFromInput.addEventListener('change', validateAmountRange);
       amountToInput.addEventListener('change', validateAmountRange);
       
       // Auto-submit on Enter key in search input
       searchInput.addEventListener('keypress', function(e) {
         if (e.key === 'Enter') {
           searchForm.submit();
         }
       });
       
       // Clear filters functionality
       const clearButtons = document.querySelectorAll('a[href="salesreturnlists.php"]');
       clearButtons.forEach(button => {
         button.addEventListener('click', function(e) {
           // Clear all form inputs
           searchInput.value = '';
           dateFromInput.value = '';
           dateToInput.value = '';
           amountFromInput.value = '';
           amountToInput.value = '';
         });
       });
       
       // Set default date range (last 30 days) if no filters are applied
       if (!dateFromInput.value && !dateToInput.value && !searchInput.value && !amountFromInput.value && !amountToInput.value) {
         const today = new Date();
         const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
         
         // Uncomment the lines below if you want to set default date range
         // dateFromInput.value = thirtyDaysAgo.toISOString().split('T')[0];
         // dateToInput.value = today.toISOString().split('T')[0];
       }
     });
   </script>
 </body>

</html>
<?php else: ?>
<?php header("Location: signin.php"); ?>
<?php endif ?>