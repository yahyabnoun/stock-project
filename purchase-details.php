<?php
session_start();
?>
<?php if (isset($_SESSION['admin'])): ?>
<?php
    require_once("../stock2025/php/PrPurchase.php");
    require_once("../stock2025/php/PrSale.php");
    // $active = array(0, 0, 0, 0, 0, "active", 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    if (isset($_GET['num_app'])) {
        extract($_GET);
        $prPurchases = PrPurchase::displayPrPurchase($num_app);
    }
    // echo ("<pre>");
    // print_r($prPurchases);
    // echo ("<pre>");
    // print_r($_GET);
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
    <title>Product List</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/fav1.jpg" />

    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />

    <link rel="stylesheet" href="assets/css/animate.css" />

    <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css" />

    <link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css" />

    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css" />
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css" />

    <link rel="stylesheet" href="assets/css/style.css" />
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
                        <h4>Product List</h4>
                        <h6>Manage Your Products</h6>
                    </div>
                    <div class="page-btn">
                        <a href="addpurchase.php?num_app=<?= $_GET['num_app'] ?>" class="btn btn-added">
                            <img src="assets/img/icons/plus.svg" alt="img" class="me-1" />
                            Add New Product
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-top">
                            <div class="search-set">
                                <div class="search-input">
                                    <a class="btn btn-searchset">
                                        <img src="assets/img/icons/search-white.svg" alt="img" />
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table datanew">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Reference</th>
                                        <th>Sale Price</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($prPurchases as $item): ?>
                                    <tr>
                                        <td class="productimgname">
                                            <a class="product-img">
                                                <img src="<?= $item['pr_image'] ?>" alt="product" />
                                            </a>
                                            <a href="javascript:void(0);"><?= $item['lib_pr'] ?></a>
                                        </td>
                                        <td><?= $item['num_pr'] ?></td>
                                        <td><?= $item['prix_achat'] ?>DH</td>
                                        <td><?= $item['qte_achete'] ?></td>
                                        <td><?= $item['qte_achete'] * $item['prix_achat'] ?>DH</td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
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
</body>

</html>
<?php else: ?>
<?php header("Location: signin.php"); ?>
<?php endif ?>