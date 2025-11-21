<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <div class="report-header">
        <h2><?=$this->lang->line('product_history_report_for')?>: <?=$product->productname?></h2>
        <?php if($from_date && $to_date) { ?>
            <p><?=$this->lang->line('product_from_date')?>: <?=$from_date?> <?=$this->lang->line('product_to_date')?>: <?=$to_date?></p>
        <?php } ?>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th><?=$this->lang->line('product_date')?></th>
                <th><?=$this->lang->line('product_type')?></th>
                <th><?=$this->lang->line('product_reference')?></th>
                <th><?=$this->lang->line('product_quantity')?></th>
                <th><?=$this->lang->line('product_unit_price')?></th>
                <th><?=$this->lang->line('product_total')?></th>
            </tr>
        </thead>
        <tbody>
            <?php
                if(customCompute($history)) {
                    foreach($history as $item) {
            ?>
                <tr>
                    <td><?=date('d M Y', strtotime($item->date))?></td>
                    <td><?=$item->type?></td>
                    <td><?=$item->reference?></td>
                    <td><?=$item->quantity?></td>
                    <td><?=number_format($item->price, 2)?></td>
                    <td><?=number_format($item->total, 2)?></td>
                </tr>
            <?php
                    }
                }
            ?>
        </tbody>
    </table>
</body>
</html>
