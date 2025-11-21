<?php

declare(strict_types=1);

class CI_Loader
{
    public function database(): void
    {
    }

    public function config(string $file): void
    {
    }

    public function helper($helper): void
    {
    }

    public function model($model): void
    {
    }

    public function library($library, array $params = []): void
    {
    }

    public function view($view, array $vars = [], bool $return = false)
    {
        return $return ? '' : null;
    }
}

class CI_Session
{
    /**
     * @param string $item
     * @return mixed
     */
    public function userdata($item)
    {
        return null;
    }

    /**
     * @param mixed $value
     */
    public function set_flashdata(string $key, $value): void
    {
    }
}

class CI_Lang
{
    public function load(string $file, ?string $lang = null): void
    {
    }

    public function line(string $line): string
    {
        return $line;
    }
}

class CI_URI
{
    public function segment(int $n, ?string $no_result = null): ?string
    {
        return $no_result;
    }
}

class CI_Input
{
    public function post(string $index = null, bool $xss_clean = null)
    {
        return null;
    }

    public function get(string $index = null, bool $xss_clean = null)
    {
        return null;
    }

    public function is_cli_request(): bool
    {
        return true;
    }
}

class CI_Config
{
    public function item(string $item)
    {
        return null;
    }

    public function load(string $file): void
    {
    }
}

class CI_DB_result
{
    /**
     * @return object|null
     */
    public function row()
    {
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function row_array(): array
    {
        return [];
    }

    /**
     * @return array<int, object>
     */
    public function result(): array
    {
        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function result_array(): array
    {
        return [];
    }

    /**
     * @return object|null
     */
    public function first_row()
    {
        return null;
    }

    public function num_rows(): int
    {
        return 0;
    }
}

class CI_DB
{
    /** @var string */
    public $hostname = 'localhost';
    /** @var string */
    public $username = 'root';
    /** @var string */
    public $password = '';
    /** @var string */
    public $database = 'database';
    /** @var int */
    public $port = 3306;

    public function table_exists(string $table): bool
    {
        return true;
    }

    public function list_tables(): array
    {
        return [];
    }

    public function select(string $select = '*'): self
    {
        return $this;
    }

    public function from(string $from): self
    {
        return $this;
    }

    public function join(string $table, string $cond, string $type = ''): self
    {
        return $this;
    }

    public function where($key, $value = null, bool $escape = true): self
    {
        return $this;
    }

    public function order_by($orderby, string $direction = ''): self
    {
        return $this;
    }

    public function limit(int $value, ?int $offset = null): self
    {
        return $this;
    }

    public function get(string $table = '', ?int $limit = null, ?int $offset = null): CI_DB_result
    {
        return new CI_DB_result();
    }

    public function get_where(string $table, array $where = [], ?int $limit = null, ?int $offset = null): CI_DB_result
    {
        return new CI_DB_result();
    }

    public function insert(string $table, array $set = []): bool
    {
        return true;
    }

    public function update(string $table, array $set = [], array $where = []): bool
    {
        return true;
    }

    public function delete(string $table, array $where = [], ?int $limit = null): bool
    {
        return true;
    }

    public function query(string $sql, array $binds = []): CI_DB_result
    {
        return new CI_DB_result();
    }
}

class CI_Migration
{
    /**
     * @return array<string, string>
     */
    public function find_migrations(): array
    {
        return [];
    }

    /**
     * @return int|string|false
     */
    public function latest()
    {
        return 0;
    }

    /**
     * @param int|string $target
     * @return int|string|false
     */
    public function version($target)
    {
        return $target;
    }

    public function error_string(): string
    {
        return '';
    }
}

class CI_Form_validation
{
    public function set_rules($field, string $label = '', $rules = ''): self
    {
        return $this;
    }

    public function set_message(string $rule, string $message): void
    {
    }

    public function set_data(array $data): void
    {
    }

    public function run(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function error_array(): array
    {
        return [];
    }
}

class CI_Output
{
    public function set_content_type(string $mime_type, string $charset = ''): self
    {
        return $this;
    }

    public function set_output(string $output): self
    {
        return $this;
    }
}

class CI_Email
{
    public function initialize(array $config = []): void
    {
    }

    public function from(string $from, string $name = ''): self
    {
        return $this;
    }

    public function to($to): self
    {
        return $this;
    }

    public function subject(string $subject): self
    {
        return $this;
    }

    public function message(string $body): self
    {
        return $this;
    }

    public function send(): bool
    {
        return true;
    }
}

class CI_Upload
{
    public function initialize(array $config = []): void
    {
    }

    public function do_upload(string $field = ''): bool
    {
        return true;
    }

    public function display_errors(): string
    {
        return '';
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return [];
    }
}

class CI_Security
{
    public function xss_clean($str)
    {
        return $str;
    }

    public function get_csrf_hash(): string
    {
        return '';
    }
}

class CI_Pagination
{
    public function initialize(array $config = []): void
    {
    }

    public function create_links(): string
    {
        return '';
    }
}

class CI_Controller
{
    /** @var CI_Loader */
    public $load;
    /** @var CI_Session */
    public $session;
    /** @var CI_Lang */
    public $lang;
    /** @var CI_URI */
    public $uri;
    /** @var CI_Input */
    public $input;
    /** @var CI_DB */
    public $db;
    /** @var CI_Config */
    public $config;
    /** @var array<string, mixed> */
    public $data = [];
    /** @var CI_Migration */
    public $migration;
    /** @var CI_Form_validation */
    public $form_validation;
    /** @var CI_Output */
    public $output;
    /** @var CI_Email */
    public $email;
    /** @var CI_Upload */
    public $upload;
    /** @var CI_Security */
    public $security;
    /** @var CI_Pagination */
    public $pagination;

    public function __construct()
    {
        $this->load = new CI_Loader();
        $this->session = new CI_Session();
        $this->lang = new CI_Lang();
        $this->uri = new CI_URI();
        $this->input = new CI_Input();
        $this->db = new CI_DB();
        $this->config = new CI_Config();
        $this->migration = new CI_Migration();
        $this->form_validation = new CI_Form_validation();
        $this->output = new CI_Output();
        $this->email = new CI_Email();
        $this->upload = new CI_Upload();
        $this->security = new CI_Security();
        $this->pagination = new CI_Pagination();
    }
}
