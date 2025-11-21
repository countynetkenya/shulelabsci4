<?php
    // Calculate summary stats
    $received_qty = 0;
    $sold_qty = 0;
    $net_sales = 0;
    $cost_of_goods = 0;

    if(customCompute($history)) {
        foreach($history as $item) {
            if($item->type == 'Sale') {
                $sold_qty += $item->quantity;
                $net_sales += $item->total;
            } else {
                $received_qty += $item->quantity;
            }
        }
    }

    $cogs_unit_price = isset($averageunitprice->averageunitprice) ? $averageunitprice->averageunitprice : 0;
    $cost_of_goods = $sold_qty * $cogs_unit_price;
    $profit = $net_sales - $cost_of_goods;
    $profit_margin = ($net_sales > 0) ? ($profit / $net_sales) * 100 : 0;
?>

<div class="table-responsive" style="margin-bottom: 20px;">
    <table class="table table-bordered">
        <thead>
            <th colspan="2">Quantities In</th>
            <th colspan="2">Quantities Out</th>
            <th colspan="2">Totals</th>
        </thead>
        <tbody>
            <tr>
                <td>Received</td>
                <td><?=$received_qty?></td>
                <td>Sold</td>
                <td><?=$sold_qty?></td>
                <td>Net Sales</td>
                <td><?=number_format($net_sales, 2)?></td>
            </tr>
            <tr>
                <td>Sales Returns</td>
                <td>0</td>
                <td>Vendor Returns</td>
                <td>0</td>
                <td>Cost of Goods</td>
                <td><?=number_format($cost_of_goods, 2)?></td>
            </tr>
            <tr>
                <td colspan="4"></td>
                <td>Profit</td>
                <td><?=number_format($profit, 2)?></td>
            </tr>
            <tr>
                <td colspan="4"></td>
                <td>Profit Margin</td>
                <td><?=number_format($profit_margin, 2)?>%</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="row">
    <div class="col-sm-12" style="margin-bottom: 10px;">
        <a href="#" id="pdf-export-btn" class="btn btn-success btn-sm">
            <i class="fa fa-file-pdf-o"></i> <?=$this->lang->line('product_export_pdf')?>
        </a>
        <a href="#" id="csv-export-btn" class="btn btn-success btn-sm">
            <i class="fa fa-file-excel-o"></i> <?=$this->lang->line('product_export_csv')?>
        </a>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-striped" id="history-table">
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
                $total_sales = 0;
                $total_purchases = 0;
                if(customCompute($history)) {
                    foreach($history as $item) {
                        if($item->type == 'Sale') {
                            $total_sales += $item->total;
                        } else {
                            $total_purchases += $item->total;
                        }
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
</div>

<!-- Hidden data for chart -->
<div id="history_summary" style="display: none;">
    <span id="total_sales"><?=$total_sales?></span>
    <span id="total_purchases"><?=$total_purchases?></span>
</div>

<script type="text/javascript">
    var historyData = <?=json_encode($history)?>;
</script>
