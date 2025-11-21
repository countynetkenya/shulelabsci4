# Menu Visibility Patch Automation – Ubuntu Command Guide

Follow these steps on an Ubuntu host to execute the menu visibility automation safely.

## 1. Ensure prerequisites are installed

```bash
sudo apt update
sudo apt install -y php-cli php-mysql git unzip
```

Install any additional PHP extensions your deployment requires (for example `php-sqlite3` or `php-pgsql`).

## 2. Position the repository

```bash
cd /var/www
sudo git clone <repository-url> besha
cd besha
```

Adjust the clone location to match your environment. If the code already exists, just `cd` into the project directory.

## 3. Set file permissions for the script

```bash
chmod +x scripts/menu_visibility_patch.sh
```

## 4. Run the automation

```bash
APP_ROOT=/var/www/besha PHP_BIN=/usr/bin/php bash scripts/menu_visibility_patch.sh
```

- Override `APP_ROOT` if the project lives elsewhere.
- Override `PHP_BIN` if your PHP binary is not `/usr/bin/php`.

## 5. Review logs and status

The script tails the latest CodeIgniter log automatically. To re-check later:

```bash
tail -n 50 application/logs/log-$(date '+%Y-%m-%d').php
```

If migrations or linting report issues, inspect the output and resolve before re-running the script.

## 6. Optional: re-run smoke tests only

```bash
APP_ROOT=/var/www/besha PHP_BIN=/usr/bin/php php scripts/smoke_menu_visibility.php
```

This can be useful after manual fixes to verify the menu overrides without executing the full automation again.
