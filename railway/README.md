# Railway service config

Use these files as the service-level config-as-code file in Railway:

- `/railway/app.toml` for the public web app service
- `/railway/worker.toml` for the queue worker service
- `/railway/cron.toml` for the scheduler service

Recommended Railway setup:

- Add a MySQL service and set `DB_URL` or the individual `DB_*` variables from it.
- Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` to your Railway domain, and `LOG_CHANNEL=stderr`.
- Set `APP_KEY` to a real generated key.
- Set `ML_API_URL` to your ML service's Railway private URL if predictions are required.
- Set `GOOGLE_WEATHER_API_KEY` in Railway instead of relying on a code default.
- Attach a volume to `/app/storage` if you want local public-disk uploads to persist between deploys.

Railway can use a custom config file per service. In the service settings, point each service at the matching absolute path above.
