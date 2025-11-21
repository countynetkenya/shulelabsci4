<!DOCTYPE html>
<html class="white-bg-login" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <title>Sign in - <?= esc($siteinfos->sname ?? 'ShuleLabs') ?></title>
    <link rel="SHORTCUT ICON" href="<?= base_url("uploads/images/" . ($siteinfos->photo ?? 'favicon.ico')) ?>" />
    
    <!-- Bootstrap CSS -->
    <link href="<?= base_url('assets/bootstrap/bootstrap.min.css') ?>" rel="stylesheet" type="text/css">
    <!-- Font Awesome -->
    <link href="<?= base_url('assets/fonts/font-awesome.css') ?>" rel="stylesheet" type="text/css">
    <!-- Custom CSS -->
    <link href="<?= base_url('assets/inilabs/inilabs.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/inilabs/responsive.css') ?>" rel="stylesheet" type="text/css">
    
    <style>
        .white-bg-login {
            background: #f3f3f4;
        }
        .form-box {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-box .header {
            background: #3c8dbc;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
            font-size: 20px;
            font-weight: 600;
        }
        .form-box .body {
            padding: 30px;
        }
        .auth-submit {
            position: relative;
        }
        .auth-submit.is-loading {
            pointer-events: none;
            opacity: 0.7;
        }
        .auth-spinner {
            display: none;
        }
        .is-loading .auth-spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="white-bg-login">
    <div class="col-md-4 col-md-offset-4 marg" style="margin-top:30px;">
        <?php if (!empty($siteinfos->photo)) : ?>
            <center><img width="50" height="50" src="<?= base_url('uploads/images/' . $siteinfos->photo) ?>" alt="Logo" /></center>
        <?php endif; ?>
        <center><h4><?= namesorting($siteinfos->sname ?? 'ShuleLabs', 25) ?></h4></center>
    </div>

    <div class="form-box" id="login-box">
        <div class="header">Sign In</div>
        <form method="post" id="signinForm" action="<?= base_url('auth/signin') ?>" novalidate>
            <?= csrf_field() ?>
            
            <div class="body white-bg">
                <?php if ($form_validation !== 'No') : ?>
                    <div class="alert alert-danger alert-dismissable">
                        <i class="fa fa-ban"></i>
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <?= esc($form_validation) ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('signin_debug')) : ?>
                    <div class="alert alert-info alert-dismissable">
                        <i class="fa fa-info-circle"></i>
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <?= esc(session()->getFlashdata('signin_debug')) ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')) : ?>
                    <div class="alert alert-success alert-dismissable">
                        <i class="fa fa-check"></i>
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <?= esc(session()->getFlashdata('success')) ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="sr-only" for="signin-username">Username</label>
                    <input id="signin-username" class="form-control" placeholder="Username" name="username" type="text" autofocus value="<?= set_value('username') ?>">
                    <?= form_error('username', '<div class="help-block text-danger small">', '</div>') ?>
                </div>
                
                <div class="form-group">
                    <label class="sr-only" for="signin-password">Password</label>
                    <input id="signin-password" class="form-control" placeholder="Password" name="password" type="password">
                    <?= form_error('password', '<div class="help-block text-danger small">', '</div>') ?>
                </div>

                <div class="checkbox">
                    <label>
                        <input type="checkbox" value="1" name="remember">
                        <span> &nbsp; Remember Me</span>
                    </label>
                    <span class="pull-right">
                        <label>
                            <a href="<?= base_url('reset/index') ?>"> Forgot Password?</a>
                        </label>
                    </span>
                </div>

                <button type="submit" class="btn btn-lg btn-success btn-block auth-submit">
                    <span class="auth-spinner" aria-hidden="true"></span>
                    <span class="auth-submit__label">SIGN IN</span>
                </button>

                <span>
                    <label>
                      <a href="<?= base_url('register/index') ?>"><b> New account?</b></a>
                    </label>
                </span>
            </div>
        </form>
    </div>

    <script type="text/javascript" src="<?= base_url('assets/inilabs/jquery.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/bootstrap/bootstrap.min.js') ?>"></script>
    <script type="text/javascript">
        (function(){
            var form = document.getElementById('signinForm');
            if(!form) return;
            var controls = form.querySelectorAll('input, button, select, textarea');
            var submitButton = form.querySelector('.auth-submit');
            function setControlState(state){
                Array.prototype.forEach.call(controls, function(ctrl){
                    if (ctrl.type === 'submit' || ctrl.tagName.toLowerCase() === 'button') {
                        ctrl.disabled = state;
                    } else {
                        ctrl.readOnly = state;
                    }
                });
                if(submitButton){
                    submitButton.classList.toggle('is-loading', state);
                }
                form.classList.toggle('is-submitting', state);
            }
            setControlState(false);
            form.addEventListener('submit', function(){
                setControlState(true);
            });
            window.addEventListener('pageshow', function(){
                setControlState(false);
            });
        })();
    </script>
</body>
</html>
