# Production Debug Playbooks

This runbook is for debugging production on Forge without paid tools.

## Base Path

```bash
cd /home/forge/api.najaah.me/current
```

## Quick Commands

```bash
# Application log
tail -f storage/logs/laravel-$(date +%F).log

# Request lifecycle log
tail -f storage/logs/requests-$(date +%F).log

# Queue/job lifecycle log
tail -f storage/logs/jobs-$(date +%F).log

# Find all entries for a specific request id
grep "REQUEST_ID_HERE" storage/logs/*.log

# Queue failures
php artisan queue:failed
php artisan queue:retry all
```

Useful filters:

```bash
# Failed jobs only
grep "job_failed" storage/logs/jobs-$(date +%F).log

# Request summary with warnings/errors
grep -E "request_completed|error|warning" storage/logs/requests-$(date +%F).log
```

## Forge Verification Commands

```bash
# Confirm storage symlink and logs path
ls -ld /home/forge/api.najaah.me/current/storage
readlink -f /home/forge/api.najaah.me/current/storage/logs

# Confirm logging and failed job env values
grep -E '^(LOG_|QUEUE_FAILED_DRIVER)' /home/forge/api.najaah.me/.env

# List current log files
ls -lah /home/forge/api.najaah.me/storage/logs
```

## Playbook 1: API Returns 500

1. Reproduce with response headers:
```bash
curl -i https://api.najaah.me/up
```
2. Copy `X-Request-Id` from response.
3. Trace request across logs:
```bash
grep "REQUEST_ID_HERE" storage/logs/*.log
```
4. Inspect request and app errors:
```bash
tail -n 200 storage/logs/requests-$(date +%F).log
tail -n 200 storage/logs/laravel-$(date +%F).log
```
5. If exception points to config/env, run deploy cache refresh:
```bash
php artisan optimize:clear
php artisan optimize
```

## Playbook 2: Mail Not Sent

1. Confirm jobs are being queued:
```bash
php artisan tinker --execute='dump(DB::table("jobs")->select("queue", DB::raw("count(*) as c"))->groupBy("queue")->get());'
```
2. Check mail worker process in Forge is running (`--queue=mail`).
3. Check job log:
```bash
tail -n 200 storage/logs/jobs-$(date +%F).log | grep -E "mail|SendAdmin|job_failed|job_processed"
```
4. Check failed jobs:
```bash
php artisan queue:failed
```
5. Retry and watch logs:
```bash
php artisan queue:retry all
tail -f storage/logs/jobs-$(date +%F).log
```

## Playbook 3: Jobs Stuck or Delayed

1. Check queue depth:
```bash
php artisan tinker --execute='dump(DB::table("jobs")->select("queue", DB::raw("count(*) as c"))->groupBy("queue")->get());'
```
2. Confirm both workers exist and are running:
- worker A: `--queue=default`
- worker B: `--queue=mail`
3. Restart workers:
```bash
php artisan queue:restart
```
4. Watch processing in real time:
```bash
tail -f storage/logs/jobs-$(date +%F).log
```
5. If backlog remains high, increase `processes` for affected queue in Forge.

## Playbook 4: Endpoint Is Slow

1. Find slow requests in request log:
```bash
grep "request_completed" storage/logs/requests-$(date +%F).log | grep "warning"
```
2. Extract endpoint path and request id.
3. Correlate with app/job logs:
```bash
grep "REQUEST_ID_HERE" storage/logs/*.log
```
4. Check if slowness is queue-related or external service-related from logs.
5. Tune:
- increase worker concurrency if queue wait is high
- reduce expensive synchronous work in request cycle
- keep mail and heavy tasks queued

## Playbook 5: Notification Missing (Enrollment / Extra View / Device Change)

1. Reproduce action from API/mobile/admin.
2. Capture request id from API response headers.
3. Check request + domain logs:
```bash
grep "REQUEST_ID_HERE" storage/logs/requests-$(date +%F).log
grep -E "notification|enrollment|extra_view|device_change" storage/logs/laravel-$(date +%F).log
```
4. If notification is queued or downstream:
```bash
tail -n 200 storage/logs/jobs-$(date +%F).log | grep -E "notification|job_failed|job_processed"
php artisan queue:failed
```
5. Verify scope/visibility in API response:
- system admins (`center_id = null`) with permission should see all relevant notifications
- center admins should see only their center notifications

## Post-Deploy Smoke

After deploy:

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan queue:restart
curl -i https://api.najaah.me/up
```

Expected:
- response includes `X-Request-Id`
- request/job logs continue appending in shared storage logs path
