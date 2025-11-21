
<div class="form-box" id="login-box">
    <div class="header"><?=$this->lang->line('signin')?></div>
    <form method="post" id="signinForm" novalidate>

        <!-- style="margin-top:40px;" -->

        <div class="body white-bg">
        <?php
            if($form_validation == "No"){
            } else {
                if(customCompute($form_validation)) {
                    echo "<div class=\"alert alert-danger alert-dismissable\">
                        <i class=\"fa fa-ban\"></i>
                        <button aria-hidden=\"true\" data-dismiss=\"alert\" class=\"close\" type=\"button\">×</button>
                        $form_validation
                    </div>";
                }
            }
            if($this->session->flashdata('reset_success')) {
                $message = $this->session->flashdata('reset_success');
                echo "<div class=\"alert alert-success alert-dismissable\">
                    <i class=\"fa fa-ban\"></i>
                    <button aria-hidden=\"true\" data-dismiss=\"alert\" class=\"close\" type=\"button\">×</button>
                    $message
                </div>";
            }
        ?>
            <div class="form-group">
                <label class="sr-only" for="signin-username">Username</label>
                <input id="signin-username" class="form-control" placeholder="Username" name="username" type="text" autofocus value="<?=set_value('username')?>">
                <?=form_error('username', '<div class="help-block text-danger small">', '</div>')?>
            </div>
            <div class="form-group">
                <label class="sr-only" for="signin-password">Password</label>
                <input id="signin-password" class="form-control" placeholder="Password" name="password" type="password">
                <?=form_error('password', '<div class="help-block text-danger small">', '</div>')?>
            </div>


            <div class="checkbox">
                <label>
                    <input type="checkbox" value="Remember Me" name="remember">
                    <span> &nbsp; Remember Me</span>
                </label>
                <span class="pull-right">
                    <label>
                        <a href="<?=base_url('reset/index')?>"> Forgot Password?</a>
                    </label>
                </span>
            </div>

            <?php if(isset($siteinfos->captcha_status) && $siteinfos->captcha_status == 0) { ?>
                <div class="form-group">
                    <?php echo $recaptcha['widget']; echo $recaptcha['script']; ?>
                </div>
            <?php } ?>

            <button type="submit" class="btn btn-lg btn-success btn-block auth-submit">
                <span class="auth-spinner" aria-hidden="true"></span>
                <span class="auth-submit__label">SIGN IN</span>
            </button>

            <span>
                <label>
                  <a href="<?=base_url('register/index')?>"><b> New account?</b></a>
                </label>
            </span>

            <?php if(config_item('demo')) { ?>
                <a href="https://codecanyon.net/item/inilabs-school-android-app-ionic-mobile-application/25780938" target="_blank">
                    <img class="img-responsive" src="https://demo.inilabs.net/description/school/app/signin-banner-2.png" alt="inilabs school app banner">
                </a>
            <?php } ?>
        </div>
    </form>
</div>
