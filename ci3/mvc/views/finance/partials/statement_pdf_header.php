<?php
$site = isset($siteinfos) ? $siteinfos : null;
$schoolName = $site && isset($site->sname) ? trim((string) $site->sname) : '';
$address = $site && isset($site->address) ? trim((string) $site->address) : '';
$phone = $site && isset($site->phone) ? trim((string) $site->phone) : '';
$email = $site && isset($site->email) ? trim((string) $site->email) : '';
$currencyDisplay = isset($currencyDisplay) ? trim((string) $currencyDisplay) : '';
$generatedAtDisplay = isset($generatedAtDisplay) ? trim((string) $generatedAtDisplay) : '';
$generatedByDisplay = isset($generatedByDisplay) ? trim((string) $generatedByDisplay) : '';
$logoPath = ($site && !empty($site->photo)) ? base_url('uploads/images/' . $site->photo) : '';

$letterheadDetails = [];
if ($address !== '') {
    $letterheadDetails[] = preg_replace('/\s+/', ' ', $address);
}

if ($phone !== '') {
    $letterheadDetails[] = trim(lang('global_phone_number')) . ': ' . $phone;
}

if ($email !== '') {
    $letterheadDetails[] = trim(lang('global_email')) . ': ' . $email;
}

if ($currencyDisplay !== '') {
    $letterheadDetails[] = trim(lang('finance_statement_currency_label')) . ': ' . $currencyDisplay;
}

$letterheadDetailsLine = trim(implode(' • ', array_filter($letterheadDetails, static function ($value) {
    return $value !== '';
})));
?>
<div style="border-bottom:1px solid #ddd;padding:10px 0 5px;margin-bottom:10px;">
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:15%;vertical-align:top;">
                <?php if ($logoPath): ?>
                    <img src="<?= $logoPath; ?>" alt="<?= html_escape($schoolName); ?>" style="height:50px;">
                <?php endif; ?>
            </td>
            <td style="width:55%;vertical-align:top;font-size:11px;">
                <?php if ($schoolName !== ''): ?>
                    <div style="font-size:14px;font-weight:bold;">
                        <?= html_escape($schoolName); ?>
                    </div>
                <?php endif; ?>
                <?php if ($letterheadDetailsLine !== ''): ?>
                    <div style="margin-top:2px;color:#777;line-height:1.4;word-break:break-word;overflow-wrap:anywhere;">
                        <?= html_escape($letterheadDetailsLine); ?>
                    </div>
                <?php endif; ?>
            </td>
            <td style="width:30%;vertical-align:top;text-align:right;font-size:11px;">
                <?php if ($generatedAtDisplay !== ''): ?>
                    <?= html_escape(lang('finance_statement_generated_at_label')); ?>: <?= html_escape($generatedAtDisplay); ?><br>
                <?php endif; ?>
                <?php if ($generatedByDisplay !== ''): ?>
                    <?= html_escape(lang('finance_statement_generated_by_label')); ?>: <?= html_escape($generatedByDisplay); ?><br>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>
