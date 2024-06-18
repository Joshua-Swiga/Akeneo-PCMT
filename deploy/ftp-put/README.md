# SFTP Upload Tool

This tool downloads XML files for processing by the PCMT. See 
https://gitlab.com/pcmt/pcmt/-/issues/681

## Configure

Environment variables to configure how this container runs.  Ones without
a default are *required*.  Aside from `FTP_PUT_CREDS`, any may be set in the
file that `FTP_PUT_CREDS` points to.

1. `FTP_PUT_CREDS`: Where the file that contains the credentials will be
    mounted into the container. Default: `/run/secrets/ftp-put-creds`
1. `SFTP_USER`: The SFTP username, should be in `FTP_GET_CREDS`.
1. `SFTP_HOST`: The SFTP hostname, should be in `FTP_GET_CREDS`.
1. `SFTP_PRIVKEY_FILENAME`: Set where the file that contains the SFTP user's
    private OpenSSH key will be mounted into the container.
1. `SFTP_LOCAL_DIR`: Where the files to upload to the SFTP server are
    to.  Default: `/uploads/work`.
1. `SFTP_LOCAL_ARCHIVE_DIR`: Where the files will be moved to, locally, after
    they are uploaded.  Defaults to `/uploads/done`.
1. `SFTP_REMOTE_DIR`: The remote working directory to upload files to.
1. `SFTP_REMOTE_ARCHIVE_DIR`: The remote final destination directory to move files
    to, once they're _fully_ uploaded to `SFTP_REMOTE_DIR`.

## Example

See [ftp-get](../ftp-get/README.md) for examples.