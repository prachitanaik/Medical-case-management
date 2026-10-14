# Deployment plan (AWS)

How I would host this app on AWS. This is a plan; the app is not deployed.

**Compute - EC2.** One Ubuntu LTS instance (e.g. `t3.small`) in a public subnet running Nginx + PHP-FPM 8.3. Deploy by `git pull`, then `composer install --no-dev --optimize-autoloader`, `php artisan migrate --force`, `php artisan config:cache route:cache view:cache`. Nginx serves `public/` only, never the project root. Later, an Auto Scaling group behind an Application Load Balancer would replace the single instance.

**Database - RDS.** A MySQL 8 RDS instance in a private subnet, not publicly accessible, with automated backups and encryption at rest. Its security group accepts port 3306 only from the EC2 security group.

**Files - S3.** The app stores no uploads today. If case attachments are added, use a private S3 bucket (block public access, server-side encryption) through Laravel's `s3` filesystem disk, with an IAM role on the EC2 instance instead of access keys, and pre-signed URLs for downloads.

**Network and security groups.** The EC2 group allows 443 and 80 from the internet and 22 only from my own IP (or use SSM Session Manager and close 22). Nothing else is open. The RDS group is described above.

**Secrets.** `.env` is never committed. On the server it holds `APP_KEY`, DB credentials and `APP_DEBUG=false`, readable only by the app user. A better option is AWS Secrets Manager or SSM Parameter Store, loaded at deploy time. The seeded demo users are not created in production.

**HTTPS.** Request a free certificate from AWS Certificate Manager and terminate TLS on the load balancer (or use Let's Encrypt with Certbot on a single instance). Redirect HTTP to HTTPS, set `APP_URL` to the https address and enable HSTS.

**Operations.** CloudWatch for logs and alarms, daily RDS snapshots, and security patches applied regularly. Move the scheduler/queue to a separate worker only when needed.
