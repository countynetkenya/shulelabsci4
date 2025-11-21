<div class="row">
    <div class="col-sm-12">
        <h3 class="page-header"><i class="fa fa-shield"></i> <?=$this->lang->line('superadmin_dashboard')?></h3>
    </div>
</div>

<div class="row superadmin-quick-links">
    <?php if (empty($quickLinks)) : ?>
        <div class="col-sm-12">
            <div class="alert alert-info">No quick links are available for your account.</div>
        </div>
    <?php else : ?>
        <?php foreach ($quickLinks as $linkKey => $link): ?>
            <?php
                $labelKey = isset($link['menu_label_key']) ? $link['menu_label_key'] : null;
                $label = $labelKey ? $this->lang->line($labelKey) : (isset($link['name']) ? $link['name'] : $linkKey);
                $description = isset($link['description']) ? $link['description'] : '';
                $icon = isset($link['icon']) ? $link['icon'] : 'fa fa-circle';
                $href = base_url(isset($link['link']) ? $link['link'] : $link['route']);
            ?>
            <div class="col-md-4 col-sm-6">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="<?=$icon?>"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><a href="<?=$href?>"><?=$label?></a></span>
                        <?php if ($description): ?>
                            <span class="info-box-number text-muted"><?=$description?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
