# S3 Sync

This docker image creates container's that are used to sync a directory
to a given S3 bucket on a schedule as defined by cron.

This images extends the Cron image, and therefore it's configuration options.

## Configuration

Configuration is done by environment variable:

- CRED_PATH: The local path to a file that holds credentials, and optionally
    other configuration settings.  Normally this file is mounted into the
    container (e.g. through docker secrets).  Defaults to 
    `/run/secrets/s3-creds`.
- AWS_ACCESS_KEY_ID: The AWS Access key id to use for the S3 bucket.
- AWS_SECRET_ACCESS_KEY: The AWS secret access key to use for the S3 bucket.
- LOCAL_DIR_TO_SYNC_OUT: Full local path to sync out to S3.  Normally directory
    is mounted into the container via a volume.  Defaults to `/backup` if not
    given.
- S3_BUCKET (optional): The path and name of the S3 Bucket to use.  May include 
    a sub-path within the bucket to sync to.  e.g. `s3://BUCKET_NAME` and 
    `S3://BUCKET_NAME/SOME/SUB/PATH` are valid.

## Example

Given the file `s3-creds`:

```shell
AWS_ACCESS_KEY_ID=someaccesskeyid
AWS_SECRET_ACCESS_KEY=somesecretaccesskey
S3_BUCKET=s3://some-bucket
```

Then we could sync `/some/backups` to `s3://some-bucket`:

```shell
docker run -d -v "$(pwd)/s3-creds:/run/secrets/s3-creds" \
    -v backup-vol:/some/backups \
    -e LOCAL_DIR_TO_SYNC_OUT="/some/backups"
    pcmt/s3
```

Notice that we can mix environment variables both directly in the `docker run` command with `-e` as well as place them in `s3-creds`.
