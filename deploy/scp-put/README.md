# SCP-put

This docker image creates container's that are used to transfer the contents of
a local path, to a remote path, via SCP.  Removing the transfered files after it's done.

This images extends the Cron image, and therefore it's configuration options.

## Configuration

Configuration is done by environment variable:

- `CRED_PATH`: The local path to a file that holds credentials, and optionally
    other configuration settings.  Normally this file is mounted into the
    container (e.g. through docker secrets).  Defaults to 
    `/run/secrets/scp-creds`.
- `SCP_IDENTITY_PATH`: The container path to the scp identity file mounted in.
    Defaults to `/run/secrets/scp-identity`.
- `SCP_LOCAL_PATH`: The path to the local container directory.
- `SCP_REMOTE_URI`: The URI (username, host, remote path) of the remote to connect to
  and where to transfer the file(s) to. e.g. `user@somehost:/some/path`
- `SCP_REMOTE_PORT`: The port for the remote host to use, defaults to `22`.

## Example

Given the file `scp-creds`:

```shell
SCP_IDENTITY_PATH=/run/secrets/scp-identity
SCP_LOCAL_PATH=/some/backups
SCP_REMOTE_URI=someuser@somehost:~/backup
SCP_REMOTE_PORT=2222
```

Then we could sync `/some/backups` to `someuser@somehost:./backups`:

```shell
docker run -d -v "$(pwd)/scp-creds:/run/secrets/scp-creds" \
    -v "$HOME/.ssh/id_rsa:/run/secrets/scp-identity:ro" \
    -v backup-vol:/some/backups \
    pcmt/scp-put
```
