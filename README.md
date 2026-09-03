# Serendib Pathways

Sri Lanka travel website powered by PHP, MariaDB and Gemini.

## Local setup

1. Import `config/setup.sql` and then `config/leonvia_upgrade.sql` into MariaDB.
2. Configure the database in `config/database.php`.
3. Copy `config/gemini.example.php` to `config/gemini.local.php` and add the Gemini API key.

`config/gemini.local.php` is intentionally excluded from Git.

## Production deployment

Pushes to `main` deploy automatically through `.github/workflows/deploy.yml` after these GitHub Actions repository secrets are configured:

- `EC2_HOST`
- `EC2_USER`
- `EC2_SSH_KEY`

Production database and Gemini configuration files are preserved during deployments.
