<?php
$pageLabelText = isset($pageLabel) ? trim((string) $pageLabel) : '';
$pageOfLabelText = isset($pageOfLabel) ? trim((string) $pageOfLabel) : '';
if ($pageLabelText === '') {
    $pageLabelText = 'Page';
}
if ($pageOfLabelText === '') {
    $pageOfLabelText = 'of';
}
?>
<div style="text-align:right;font-size:10px;color:#666;padding-top:5px;">
    <?= html_escape($pageLabelText); ?> {PAGENO} <?= html_escape($pageOfLabelText); ?> {nbpg}
</div>
