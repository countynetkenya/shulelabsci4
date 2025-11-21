<section class="content invoice">
    <?php
        $payload = isset($statementPayload) ? $statementPayload : ['students' => []];
        $summary = isset($summary) ? $summary : [];
        $generatedAtDisplay = isset($generatedAtDisplay) ? $generatedAtDisplay : ($summary['generated_at_display'] ?? ($payload['generated_at_display'] ?? ''));
        $generatedByDisplay = isset($summary['generated_by']) ? $summary['generated_by'] : '';
        $filters = isset($payload['filters']) ? $payload['filters'] : [];
        $includeParentDetails = array_key_exists('includeParentDetails', $filters) ? (bool) $filters['includeParentDetails'] : true;
        $includeStudentDetails = array_key_exists('includeStudentDetails', $filters) ? (bool) $filters['includeStudentDetails'] : true;
        $siteinfos = isset($siteinfos) ? $siteinfos : null;
        $currencyCode = ($siteinfos && !empty($siteinfos->currency_code)) ? trim((string) $siteinfos->currency_code) : '';
        $currencySymbol = ($siteinfos && !empty($siteinfos->currency_symbol)) ? trim((string) $siteinfos->currency_symbol) : '';
        $currencySuffix = $currencyCode !== '' ? ' (' . $currencyCode . ')' : '';
        $currencyDisplay = trim(($currencySymbol !== '' ? $currencySymbol . ' ' : '') . $currencyCode);
    ?>
    <?php if(customCompute($payload['students'])): ?>
        <?php foreach($payload['students'] as $student): ?>
            <?php
                $studentInfo = isset($student['student']) ? $student['student'] : [];
                $parentInfo = isset($student['parent']) ? $student['parent'] : [];
                $rangeDisplay = isset($student['range_display']) ? $student['range_display'] : '';
                $terms = isset($student['terms']) && customCompute($student['terms']) ? $student['terms'] : [];
                if (!customCompute($terms) && isset($student['rows']) && customCompute($student['rows'])) {
                    $terms = [[
                        'label' => lang('finance_statement_activity'),
                        'rows' => $student['rows'],
                        'subtotal' => [
                            'debit' => $studentInfo['total_debit'] ?? 0,
                            'credit' => $studentInfo['total_credit'] ?? 0,
                            'balance' => $studentInfo['closing_balance'] ?? 0,
                        ],
                        'is_opening' => false,
                        'has_transactions' => true,
                    ]];
                    $terms[0]['rows'][] = [
                        'type' => 'term_summary',
                        'term' => lang('finance_statement_activity'),
                        'term_label' => lang('finance_statement_activity'),
                        'description' => lang('finance_statement_term_subtotal'),
                        'debit' => $studentInfo['total_debit'] ?? 0,
                        'credit' => $studentInfo['total_credit'] ?? 0,
                        'balance' => $studentInfo['closing_balance'] ?? 0,
                        'running_balance' => $studentInfo['closing_balance'] ?? 0,
                    ];
                }
            ?>
            <?php
                echo $this->load->view('finance/partials/statement_pdf_header', [
                    'siteinfos' => $siteinfos,
                    'currencyDisplay' => $currencyDisplay,
                    'generatedAtDisplay' => $generatedAtDisplay,
                    'generatedByDisplay' => $generatedByDisplay,
                ], true);
            ?>
            <div class="row invoice-info">
                <div class="col-sm-6 invoice-col">
                    <address>
                        <strong><?= html_escape($studentInfo['student_name'] ?? ''); ?></strong><br>
                        <?php if(!empty($studentInfo['admission_number'])): ?><?= lang('global_register_no'); ?>: <?= html_escape($studentInfo['admission_number']); ?><br><?php endif; ?>
                        <?php if(!empty($studentInfo['studentID'])): ?><?= lang('finance_statement_student_id_label'); ?>: <?= html_escape($studentInfo['studentID']); ?><br><?php endif; ?>
                        <?php if(!empty($studentInfo['class'])): ?><?= lang('global_classes'); ?>: <?= html_escape($studentInfo['class']); ?><br><?php endif; ?>
                        <?php if(!empty($studentInfo['section'])): ?><?= lang('global_section'); ?>: <?= html_escape($studentInfo['section']); ?><br><?php endif; ?>
                        <?php if(!empty($studentInfo['group'])): ?><?= lang('finance_statement_group_label'); ?>: <?= html_escape($studentInfo['group']); ?><br><?php endif; ?>
                        <?php if(!empty($studentInfo['role'])): ?><?= lang('finance_statement_role_label'); ?>: <?= html_escape($studentInfo['role']); ?><br><?php endif; ?>
                        <?php if($includeStudentDetails && !empty($studentInfo['address'])): ?><?= lang('finance_statement_student_address'); ?>: <?= html_escape($studentInfo['address']); ?><br><?php endif; ?>
                        <?php if($includeStudentDetails && !empty($studentInfo['email'])): ?><?= lang('finance_statement_student_email'); ?>: <?= html_escape($studentInfo['email']); ?><br><?php endif; ?>
                        <?php if($includeStudentDetails && !empty($studentInfo['phone'])): ?><?= lang('finance_statement_student_phone'); ?>: <?= html_escape($studentInfo['phone']); ?><br><?php endif; ?>
                        <?php if($rangeDisplay !== ''): ?><?= lang('finance_statement_range_label'); ?>: <?= html_escape($rangeDisplay); ?><br><?php endif; ?>
                    </address>
                </div>
                <div class="col-sm-6 invoice-col">
                    <address>
                        <strong><?= lang('finance_statement_statement_totals'); ?></strong><br>
                        <?= lang('finance_statement_table_debit'); ?><?= html_escape($currencySuffix); ?>: <?= number_format($studentInfo['total_debit'] ?? 0, 2); ?><br>
                        <?= lang('finance_statement_table_credit'); ?><?= html_escape($currencySuffix); ?>: <?= number_format($studentInfo['total_credit'] ?? 0, 2); ?><br>
                        <strong><?= lang('finance_statement_table_running_balance'); ?><?= html_escape($currencySuffix); ?>: <?= number_format($studentInfo['closing_balance'] ?? 0, 2); ?></strong><br>
                        <?php if($includeParentDetails): ?>
                            <br>
                            <strong><?= lang('finance_statement_parent_details'); ?></strong><br>
                            <?php if(customCompute($parentInfo)): ?>
                                <?php if(!empty($parentInfo['name'])): ?><?= lang('finance_statement_parent_name'); ?>: <?= html_escape($parentInfo['name']); ?><br><?php endif; ?>
                                <?php if(!empty($parentInfo['email'])): ?><?= lang('global_email'); ?>: <?= html_escape($parentInfo['email']); ?><br><?php endif; ?>
                                <?php if(!empty($parentInfo['phone'])): ?><?= lang('global_phone_number'); ?>: <?= html_escape($parentInfo['phone']); ?><br><?php endif; ?>
                                <?php if(!empty($parentInfo['address'])): ?><?= lang('finance_statement_parent_address'); ?>: <?= html_escape($parentInfo['address']); ?><br><?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted"><?= lang('finance_statement_no_parent_details'); ?></span><br>
                            <?php endif; ?>
                        <?php endif; ?>
                    </address>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <?php foreach($terms as $term): ?>
                        <?php
                            if (!$term) {
                                continue;
                            }
                            $termLabel = isset($term['label']) && $term['label'] ? $term['label'] : lang('finance_statement_activity');
                            $rows = isset($term['rows']) ? $term['rows'] : [];
                            $isOpeningGroup = !empty($term['is_opening']);
                            $hasTransactions = array_key_exists('has_transactions', $term) ? (bool) $term['has_transactions'] : true;
                        ?>
                        <h4 style="margin-top:20px;">
                            <?= html_escape($termLabel); ?>
                        </h4>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th><?= lang('global_date'); ?></th>
                                    <th><?= lang('global_description'); ?></th>
                                    <th class="text-right"><?= lang('finance_statement_table_debit'); ?><?= html_escape($currencySuffix); ?></th>
                                    <th class="text-right"><?= lang('finance_statement_table_credit'); ?><?= html_escape($currencySuffix); ?></th>
                                    <th class="text-right"><?= lang('finance_statement_table_running_balance'); ?><?= html_escape($currencySuffix); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(customCompute($rows)): ?>
                                    <?php $hasSummaryRow = false; ?>
                                    <?php foreach($rows as $row): ?>
                                        <?php $type = $row['type'] ?? ''; ?>
                                        <?php if($type === 'term_summary'): ?>
                                            <?php $hasSummaryRow = true; ?>
                                            <?php $summaryLabel = lang('finance_statement_term_subtotal'); ?>
                                            <?php if(!empty($row['term_label'])) { $summaryLabel .= ' - ' . $row['term_label']; } ?>
                                            <tr class="statement-term-subtotal">
                                                <td colspan="2" class="text-right"><strong><?= html_escape($summaryLabel); ?></strong></td>
                                                <td class="text-right"><strong><?= number_format($row['debit'] ?? 0, 2); ?></strong></td>
                                                <td class="text-right"><strong><?= number_format($row['credit'] ?? 0, 2); ?></strong></td>
                                                <td class="text-right"><strong><?= number_format($row['running_balance'] ?? ($row['balance'] ?? 0), 2); ?></strong></td>
                                            </tr>
                                        <?php else: ?>
                                            <tr>
                                                <td><?= html_escape($row['day'] ?? ''); ?></td>
                                                <td><?= html_escape($row['description'] ?? ''); ?></td>
                                                <td class="text-right"><?= !empty($row['debit']) ? number_format($row['debit'], 2) : ''; ?></td>
                                                <td class="text-right"><?= !empty($row['credit']) ? number_format($row['credit'], 2) : ''; ?></td>
                                                <td class="text-right"><?= number_format($row['running_balance'] ?? ($row['balance'] ?? 0), 2); ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if(!$hasSummaryRow && customCompute($rows) && !$isOpeningGroup && $hasTransactions): ?>
                                        <?php $subtotal = isset($term['subtotal']) ? $term['subtotal'] : ['debit' => 0, 'credit' => 0, 'balance' => 0]; ?>
                                        <tr class="statement-term-subtotal">
                                            <td colspan="2" class="text-right"><strong><?= lang('finance_statement_term_subtotal'); ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($subtotal['debit'] ?? 0, 2); ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($subtotal['credit'] ?? 0, 2); ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($subtotal['balance'] ?? 0, 2); ?></strong></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted"><?= lang('finance_statement_no_results'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    <?php endforeach; ?>
                    <?php $balanceSummary = isset($student['balance_summary']) ? $student['balance_summary'] : []; ?>
                    <?php if(!empty($balanceSummary)): ?>
                        <div class="statement-summary-block">
                            <h4 class="statement-summary-heading"><?= lang('finance_statement_summary_heading'); ?></h4>
                            <table class="table table-striped table-bordered statement-summary-table">
                                <thead>
                                    <tr>
                                        <th><?= lang('global_description'); ?></th>
                                        <th class="text-right"><?= lang('finance_statement_table_debit'); ?><?= html_escape($currencySuffix); ?></th>
                                        <th class="text-right"><?= lang('finance_statement_table_credit'); ?><?= html_escape($currencySuffix); ?></th>
                                        <th class="text-right"><?= lang('finance_statement_summary_period_balance_column'); ?><?= html_escape($currencySuffix); ?></th>
                                        <th class="text-right"><?= lang('finance_statement_table_running_balance'); ?><?= html_escape($currencySuffix); ?></th>
                                        <th><?= lang('finance_statement_summary_term_column'); ?></th>
                                        <th><?= lang('finance_statement_summary_year_column'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?= lang('finance_statement_opening_balance'); ?></td>
                                        <td class="text-right"></td>
                                        <td class="text-right"></td>
                                        <td class="text-right"></td>
                                        <td class="text-right"><?= number_format($balanceSummary['opening_balance'] ?? 0, 2); ?></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <?php foreach(($balanceSummary['terms'] ?? []) as $termSummary): ?>
                                        <?php $termLabel = $termSummary['label'] ?? ''; ?>
                                        <tr>
                                            <td><?= lang('finance_statement_summary_term_heading'); ?>: <?= html_escape($termLabel); ?></td>
                                            <td class="text-right"><?= number_format($termSummary['debit'] ?? 0, 2); ?></td>
                                            <td class="text-right"><?= number_format($termSummary['credit'] ?? 0, 2); ?></td>
                                            <td class="text-right"><?= number_format($termSummary['period_balance'] ?? (($termSummary['debit'] ?? 0) - ($termSummary['credit'] ?? 0)), 2); ?></td>
                                            <td class="text-right"><?= number_format($termSummary['balance'] ?? 0, 2); ?></td>
                                            <td><?= html_escape($termSummary['label'] ?? ''); ?></td>
                                            <td><?= html_escape($termSummary['schoolyear_label'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php foreach(($balanceSummary['academic_years'] ?? []) as $yearSummary): ?>
                                        <?php $yearLabel = $yearSummary['label'] ?? ''; ?>
                                        <tr>
                                            <td><?= lang('finance_statement_summary_year_heading'); ?>: <?= html_escape($yearLabel); ?></td>
                                            <td class="text-right"><?= number_format($yearSummary['debit'] ?? 0, 2); ?></td>
                                            <td class="text-right"><?= number_format($yearSummary['credit'] ?? 0, 2); ?></td>
                                            <td class="text-right"><?= number_format($yearSummary['period_balance'] ?? (($yearSummary['debit'] ?? 0) - ($yearSummary['credit'] ?? 0)), 2); ?></td>
                                            <td class="text-right"><?= number_format($yearSummary['balance'] ?? 0, 2); ?></td>
                                            <td></td>
                                            <td><?= html_escape($yearSummary['label'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td><?= lang('finance_statement_summary_totals'); ?></td>
                                        <td class="text-right"><?= number_format($balanceSummary['total_debit'] ?? 0, 2); ?></td>
                                        <td class="text-right"><?= number_format($balanceSummary['total_credit'] ?? 0, 2); ?></td>
                                        <?php $totalsPeriod = ($balanceSummary['total_debit'] ?? 0) - ($balanceSummary['total_credit'] ?? 0); ?>
                                        <td class="text-right"><?= number_format($totalsPeriod, 2); ?></td>
                                        <td class="text-right"></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><?= lang('finance_statement_summary_closing_balance'); ?></td>
                                        <td class="text-right"></td>
                                        <td class="text-right"></td>
                                        <td class="text-right"></td>
                                        <td class="text-right"><?= number_format($balanceSummary['closing_balance'] ?? 0, 2); ?></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 text-right">
                    <p><small><?= lang('finance_statement_generated_by_label'); ?>: <?= html_escape($generatedByDisplay ?: '-'); ?><?php if($generatedAtDisplay !== ''): ?> | <?= lang('finance_statement_generated_at_label'); ?>: <?= html_escape($generatedAtDisplay); ?><?php endif; ?></small></p>
                </div>
            </div>
            <div style="page-break-after:always;"></div>
        <?php endforeach; ?>
    <?php else: ?>
        <p><?= lang('finance_statement_no_results'); ?></p>
    <?php endif; ?>
</section>
