<?php echo doctype("html5"); ?>
<html class="white-bg-login" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <title>Select School</title>
    <link rel="SHORTCUT ICON" href="<?=base_url("uploads/images/$siteinfos->photo")?>" />
    <!-- bootstrap 3.0.2 -->
    <link href="<?php echo base_url('assets/bootstrap/bootstrap.min.css'); ?>" rel="stylesheet"  type="text/css">
    <!-- font Awesome -->
    <link href="<?php echo base_url('assets/fonts/font-awesome.css'); ?>" rel="stylesheet"  type="text/css">
    <!-- Style -->
    <link href="<?php echo base_url($backendThemePath.'/style.css'); ?>" rel="stylesheet"  type="text/css">
    <!-- iNilabs css -->
    <link href="<?php echo base_url($backendThemePath.'/inilabs.css'); ?>" rel="stylesheet"  type="text/css">
    <link href="<?php echo base_url('assets/inilabs/responsive.css'); ?>" rel="stylesheet"  type="text/css">
</head>

<body class="white-bg-login">

    <div class="col-md-4 col-md-offset-4 marg" style="margin-top:30px;">
        <?php
            if(customCompute($siteinfos->photo)) {
                echo "<center><img width='50' height='50' src=".base_url('uploads/images/'.$siteinfos->photo)." /></center>";
            }
        ?>
        <center><h4><?php echo namesorting($siteinfos->sname, 25); ?></h4></center>
    </div>

    <?php $this->load->view($subview); ?>

    <script type="text/javascript" src="<?php echo base_url('assets/inilabs/jquery.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('assets/bootstrap/bootstrap.min.js'); ?>"></script>
    <script type="text/javascript">
        (function(){
            var form = document.getElementById('schoolSelectForm');
            if(!form) return;
            var select = document.getElementById('schoolSelector');
            var controls = form.querySelectorAll('input, button, select');
            var submitButton = form.querySelector('.school-select-submit');
            var storageKey = 'shulelabs.lastSchool';
            function setControlState(state){
                Array.prototype.forEach.call(controls, function(ctrl){
                    if (ctrl.tagName.toLowerCase() === 'button' || ctrl.type === 'submit') {
                        ctrl.disabled = state;
                    } else if (ctrl.tagName.toLowerCase() !== 'select') {
                        ctrl.readOnly = state;
                    }
                });
                if(submitButton){ submitButton.classList.toggle('is-loading', state); }
                form.classList.toggle('is-submitting', state);
            }
            function rememberCurrent(){
                if(select){
                    try{ localStorage.setItem(storageKey, select.value); }catch(e){}
                }
            }
            if(select){
                try{
                    var saved = localStorage.getItem(storageKey);
                    if(saved){
                        var option = select.querySelector('option[value="'+saved+'"]');
                        if(option){
                            select.value = saved;
                            var helper = document.createElement('p');
                            helper.className = 'text-muted small last-school-note';
                            helper.textContent = '<?php echo $this->lang->line('select_school'); ?>: ' + option.textContent + ' (last used)';
                            form.insertBefore(helper, form.querySelector('.school-select-submit'));
                        }
                    }
                }catch(e){}
                select.addEventListener('change', function(){
                    rememberCurrent();
                    setControlState(true);
                    if(typeof form.requestSubmit === 'function'){
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                });
            }
            setControlState(false);
            form.addEventListener('submit', function(){
                rememberCurrent();
                setControlState(true);
            });
            window.addEventListener('pageshow', function(){
                setControlState(false);
            });
        })();
    </script>
</body>
</html>
