<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->renderSection('title', true) ?: 'ShuleLabs - School Management System' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="<?= base_url('assets/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?= base_url('assets/fonts/font-awesome.css') ?>" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= base_url('assets/inilabs/inilabs.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/inilabs/responsive.css') ?>" rel="stylesheet">
    
    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            background: #2c3e50;
            color: #fff;
            transition: all 0.3s;
            min-height: 100vh;
        }
        #sidebar.active {
            margin-left: -250px;
        }
        #content {
            width: 100%;
            padding: 0;
            min-height: 100vh;
            transition: all 0.3s;
        }
        .navbar {
            padding: 15px 10px;
            background: #fff;
            border: none;
            border-radius: 0;
            margin-bottom: 0;
            box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
        }
        .main-content {
            padding: 20px;
        }
    </style>
    
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?= view('components/sidebar') ?>
        
        <!-- Page Content -->
        <div id="content">
            <!-- Header -->
            <?= view('components/header') ?>
            
            <!-- Main Content -->
            <div class="main-content">
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="<?= base_url('assets/inilabs/jquery.js') ?>"></script>
    <script src="<?= base_url('assets/bootstrap/bootstrap.min.js') ?>"></script>
    
    <script>
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>
