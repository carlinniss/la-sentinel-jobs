# LA Sentinel Jobs Production Launch Gate

Production launch is blocked until every required item below is completed and verified.

## Portability and recovery

- [ ] Create a repeatable backup script for the MySQL database and `storage_data` Docker volume.
- [ ] Create a matching restore script that provisions a clean Docker host and restores both backups.
- [ ] Perform a full restore drill on a disposable server and record the result.
- [ ] Confirm restored users, jobs, employer profiles, resumes, resume-view records, and listing images.
- [ ] Confirm generated media conversions can be rebuilt successfully.
- [ ] Document the exact Docker volume names used in production.
- [ ] Exclude `redis_data` from required recovery data unless durable queue work is introduced.

## Configuration and secrets

- [ ] Store the production `.env` and secrets outside Git without embedding them in the image.
- [ ] Preserve the production `APP_KEY` across rebuilds and server moves.
- [ ] Configure production database, mail, Stripe, Broadstreet, and application URL values.
- [ ] Document the Cloudflare Tunnel hostname, token ownership, Docker network, and recovery procedure.
- [ ] Confirm the staging hostname and staging credentials are not reused in production.

## Docker readiness

- [ ] Add automatic ownership and write-permission correction for `storage` and `bootstrap/cache` during container startup.
- [ ] Add health checks for the application, MySQL, and Redis services.
- [ ] Confirm the application image builds from a clean checkout without local files or cached layers.
- [ ] Confirm WebP support is enabled in PHP GD.
- [ ] Confirm database seeding remains disabled by default in production.
- [ ] Pin production image versions instead of depending on floating `latest` tags.

## Pre-launch verification

- [ ] Freeze production-bound changes and record the release commit SHA.
- [ ] Back up the final staging database and uploaded media.
- [ ] Run migrations without demo seeders or destructive resets.
- [ ] Verify login, registration, employer profiles, job search, applications, resume upload, resume authorization, and employer resume review.
- [ ] Verify resume-payment behavior and Stripe webhooks before enabling charges.
- [ ] Verify Broadstreet billboard delivery and reporting.
- [ ] Verify Cloudflare, TLS, mail delivery, queues, scheduled tasks, and file uploads.
- [ ] Confirm a documented rollback path to the prior application image and database backup.

## Launch approval

- [ ] Restore drill completed.
- [ ] Security and privacy review completed.
- [ ] Product review completed.
- [ ] Final backups completed and verified.
- [ ] Production launch approved.

