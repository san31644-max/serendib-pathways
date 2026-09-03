# Serendib Pathways

Sri Lanka travel website powered by PHP, MariaDB and Gemini.

## Local setup

1. Import `config/setup.sql` and then `config/leonvia_upgrade.sql` into MariaDB.
2. Configure the database in `config/database.php`.
3. Copy `config/gemini.example.php` to `config/gemini.local.php` and add the Gemini API key.

`config/gemini.local.php` is intentionally excluded from Git.

## Production deployment

The EC2 server checks the public `main` branch every minute using a systemd timer and deploys new commits with `deployment/deploy.sh`. This outbound-only design does not store an EC2 private key in GitHub.

Production database credentials, Gemini configuration and uploaded files are preserved during deployments.
