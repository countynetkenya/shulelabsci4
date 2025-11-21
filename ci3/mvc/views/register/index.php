
<div class="form-box" id="login-box">
    <div class="header"><?=$this->lang->line('register')?></div>
    <form method="post">
      <div class="body white-bg">
        <?php
            if(form_error('name'))
                echo "<div class='form-group has-error' >";
            else
                echo "<div class='form-group' >";
        ?>
            <input type="text" class="form-control" id="name_id" name="name" value="<?=set_value('name')?>" placeholder="Name">
            <?php echo form_error('name'); ?>
        </div>

        <?php
            if(form_error('dob'))
                echo "<div class='form-group has-error' >";
            else
                echo "<div class='form-group' >";
        ?>
            <input type="text" class="form-control" id="dob" name="dob" value="<?=set_value('dob')?>" placeholder="Date of Birth">
            <?php echo form_error('dob'); ?>
        </div>

        <?php
            if(form_error('email'))
                echo "<div class='form-group has-error' >";
            else
                echo "<div class='form-group' >";
        ?>
            <input type="text" class="form-control" id="email" name="email" value="<?=set_value('email')?>" placeholder="Email">
            <?php echo form_error('email'); ?>
        </div>

        <?php
            if(form_error('username'))
                echo "<div class='form-group has-error' >";
            else
                echo "<div class='form-group' >";
        ?>
            <input type="text" class="form-control" id="username" name="username" value="<?=set_value('username')?>" placeholder="Username">
            <?php echo form_error('username'); ?>
        </div>

        <?php
            if(form_error('password'))
                echo "<div class='form-group has-error' >";
            else
                echo "<div class='form-group' >";
        ?>
            <input type="password" class="form-control" id="password" name="password" value="<?=set_value('password')?>" placeholder="Password">
            <?php echo form_error('password'); ?>
        </div>

        <input type="submit" class="btn btn-lg btn-success btn-block" value="<?=$this->lang->line("submit")?>" >

        <span>
            <label>
              <a href="<?=base_url('signin/index')?>"><b> Already have an account?</b></a>
            </label>
        </span>
      </div>
    </form>
</div>


<script type="text/javascript">
$('#username').keyup(function() {
    $(this).val($(this).val().replace(/\s/g, ''));
});

$('#dob').datepicker({ startView: 2 });
$('#jod').datepicker({ dateFormat : 'dd-mm-yyyy' });

$(document).on('click', '#close-preview', function(){
    $('.image-preview').popover('hide');
    // Hover befor close the preview
    $('.image-preview').hover(
        function () {
           $('.image-preview').popover('show');
           $('.content').css('padding-bottom', '100px');
        },
         function () {
           $('.image-preview').popover('hide');
           $('.content').css('padding-bottom', '20px');
        }
    );
});
</script>
