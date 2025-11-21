
<div class="form-box" id="login-box">
    <div class="header"><?=$this->lang->line('select_school')?></div>
    <div class="body white-bg">
        <form method="post" id="schoolSelectForm" class="school-select-form">
            <div class="form-group">
                <label for="schoolSelector" class="control-label sr-only">School</label>
                <select id="schoolSelector" name="schoolID" class="form-control input-lg">
                    <?php foreach($schools as $school) {?>
                        <option value="<?=$school->schoolID?>"><?=$school->name?></option>
                    <?php }?>
                </select>
            </div>
            <button type="submit" class="btn btn-lg btn-success btn-block school-select-submit auth-submit">
                <span class="auth-spinner" aria-hidden="true"></span>
                <span class="auth-submit__label"><?=$this->lang->line('select_school')?></span>
            </button>
        </form>
        <div class="text-center" style="margin-top:10px;">
            <?=$pagination?>
        </div>
    </div>
</div>
