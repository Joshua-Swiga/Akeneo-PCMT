# Cron base image

Meant to be a base-image for which derivatives extend to run a command on
a set CRON schedule.

## Environment variables

- `CRON_SCHEDULE`: A string that represents the CRON schedule, space seperated.
  e.g. at `0 1 * * *` is at `01:00 every day`.