# Asset Backup

Can backup, on a set CRON schedule, the file uploads and image assets added to
PCMT to a designated docker volume as a `tgz` file.

## Environment Variables

- `ASSET_DIR`: The path to the docker volume that contains the PCMT files and
  folders to backup.
- `BACKUP_DIR`: The path to the docker volume that the backup will be placed.
- `PCMT_HOSTNAME` (optional): The hostname to prepend the backup filename with.

See [CRON](../cron/README.md) for more.