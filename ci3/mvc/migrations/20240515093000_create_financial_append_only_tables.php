<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_financial_append_only_tables extends CI_Migration {

    public function up()
    {
        $this->load->dbforge();

        $this->createInvoicesTable();
        $this->createInvoiceLinesTable();
        $this->createPaymentsTable();
        $this->createGlJournalTable();
        $this->createGlLinesTable();
    }

    public function down()
    {
        $this->load->dbforge();

        $this->dropAppendOnlyTable('gl_lines');
        $this->dropAppendOnlyTable('gl_journal');
        $this->dropAppendOnlyTable('payments');
        $this->dropAppendOnlyTable('invoice_lines');
        $this->dropAppendOnlyTable('invoices');
    }

    private function createInvoicesTable()
    {
        if ($this->db->table_exists('invoices')) {
            return;
        }

        $this->dbforge->add_field([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'invoice_uuid' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'school_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'schoolyear_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'default' => null,
            ],
            'issue_date' => [
                'type' => 'DATE',
                'null' => true,
                'default' => null,
            ],
            'due_date' => [
                'type' => 'DATE',
                'null' => true,
                'default' => null,
            ],
            'currency_code' => [
                'type' => 'CHAR',
                'constraint' => 3,
                'default' => 'KES',
            ],
            'exchange_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '15,6',
                'default' => 1,
            ],
            'total_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'outstanding_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'default' => 'draft',
            ],
            'external_reference' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
                'default' => null,
            ],
            'meta' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'default' => null,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'default' => null,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'posted_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('invoice_uuid');
        $this->dbforge->add_key('student_id');
        $this->dbforge->add_key('school_id');
        $this->dbforge->create_table('invoices', true, ['ENGINE' => 'InnoDB']);

        $this->db->query('CREATE UNIQUE INDEX invoices_uuid_unique ON invoices (invoice_uuid)');
        $this->db->query('CREATE INDEX invoices_school_student ON invoices (school_id, student_id, issue_date)');

        $this->dbforge->modify_column('invoices', [
            'created_at' => [
                'name'    => 'created_at',
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'posted_at' => [
                'name'    => 'posted_at',
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->createAppendOnlyTriggers('invoices');
    }

    private function createInvoiceLinesTable()
    {
        if ($this->db->table_exists('invoice_lines')) {
            return;
        }

        $this->dbforge->add_field([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'invoice_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
            ],
            'line_number' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 1,
            ],
            'fee_type_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'default' => null,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
            ],
            'quantity' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 1,
            ],
            'unit_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'total_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'gl_account_code' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
                'default' => null,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('invoice_id');
        $this->dbforge->add_key('fee_type_id');
        $this->dbforge->create_table('invoice_lines', true, ['ENGINE' => 'InnoDB']);

        $this->db->query('CREATE INDEX invoice_lines_invoice_number ON invoice_lines (invoice_id, line_number)');

        $this->dbforge->modify_column('invoice_lines', [
            'created_at' => [
                'name'    => 'created_at',
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
        ]);
        $this->createAppendOnlyTriggers('invoice_lines');

        $this->db->query('ALTER TABLE invoice_lines ADD CONSTRAINT invoice_lines_invoice_fk FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE');
    }

    private function createPaymentsTable()
    {
        if ($this->db->table_exists('payments')) {
            return;
        }

        $this->dbforge->add_field([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'payment_uuid' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'invoice_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
                'default' => null,
            ],
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'school_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'payment_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'currency_code' => [
                'type' => 'CHAR',
                'constraint' => 3,
                'default' => 'KES',
            ],
            'method' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'default' => 'cash',
            ],
            'reference' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
                'default' => null,
            ],
            'meta' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'default' => null,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'default' => null,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('payment_uuid');
        $this->dbforge->add_key('invoice_id');
        $this->dbforge->add_key('student_id');
        $this->dbforge->create_table('payments', true, ['ENGINE' => 'InnoDB']);

        $this->db->query('CREATE UNIQUE INDEX payments_uuid_unique ON payments (payment_uuid)');
        $this->db->query('CREATE INDEX payments_lookup ON payments (school_id, student_id, payment_date)');

        $this->dbforge->modify_column('payments', [
            'created_at' => [
                'name'    => 'created_at',
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
        ]);
        $this->createAppendOnlyTriggers('payments');

        $this->db->query('ALTER TABLE payments ADD CONSTRAINT payments_invoice_fk FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL');
    }

    private function createGlJournalTable()
    {
        if ($this->db->table_exists('gl_journal')) {
            return;
        }

        $this->dbforge->add_field([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'journal_uuid' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'school_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'journal_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
                'default' => null,
            ],
            'source_type' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
                'default' => null,
            ],
            'source_reference' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
                'default' => null,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'default' => 'draft',
            ],
            'meta' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'default' => null,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'default' => null,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'posted_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('journal_uuid');
        $this->dbforge->add_key('school_id');
        $this->dbforge->create_table('gl_journal', true, ['ENGINE' => 'InnoDB']);

        $this->db->query('CREATE UNIQUE INDEX gl_journal_uuid_unique ON gl_journal (journal_uuid)');
        $this->db->query('CREATE INDEX gl_journal_school_date ON gl_journal (school_id, journal_date)');

        $this->dbforge->modify_column('gl_journal', [
            'created_at' => [
                'name'    => 'created_at',
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'posted_at' => [
                'name'    => 'posted_at',
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->createAppendOnlyTriggers('gl_journal');
    }

    private function createGlLinesTable()
    {
        if ($this->db->table_exists('gl_lines')) {
            return;
        }

        $this->dbforge->add_field([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'journal_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
            ],
            'line_number' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 1,
            ],
            'account_code' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
                'default' => null,
            ],
            'debit' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'credit' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'default' => null,
            ],
            'invoice_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
                'default' => null,
            ],
            'payment_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
                'default' => null,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('journal_id');
        $this->dbforge->add_key('account_code');
        $this->dbforge->create_table('gl_lines', true, ['ENGINE' => 'InnoDB']);

        $this->db->query('CREATE INDEX gl_lines_journal_line ON gl_lines (journal_id, line_number)');
        $this->db->query('CREATE INDEX gl_lines_student_lookup ON gl_lines (student_id, account_code)');

        $this->dbforge->modify_column('gl_lines', [
            'created_at' => [
                'name'    => 'created_at',
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
        ]);

        $this->createAppendOnlyTriggers('gl_lines');

        $this->db->query('ALTER TABLE gl_lines ADD CONSTRAINT gl_lines_journal_fk FOREIGN KEY (journal_id) REFERENCES gl_journal(id) ON DELETE CASCADE');
    }

    private function dropAppendOnlyTable($table)
    {
        $this->db->query("DROP TRIGGER IF EXISTS {$table}_prevent_update");
        $this->db->query("DROP TRIGGER IF EXISTS {$table}_prevent_delete");
        if ($this->db->table_exists($table)) {
            $this->dbforge->drop_table($table, true);
        }
    }

    private function createAppendOnlyTriggers($table)
    {
        $this->db->query("DROP TRIGGER IF EXISTS {$table}_prevent_update");
        $this->db->query("DROP TRIGGER IF EXISTS {$table}_prevent_delete");

        $this->db->query(
            "CREATE TRIGGER {$table}_prevent_update BEFORE UPDATE ON {$table} FOR EACH ROW " .
            "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '" . $table . " is append-only'"
        );

        $this->db->query(
            "CREATE TRIGGER {$table}_prevent_delete BEFORE DELETE ON {$table} FOR EACH ROW " .
            "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '" . $table . " is append-only'"
        );
    }
}
