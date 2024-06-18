# SFTP Download Tool

This tool downloads XML files for processing by the PCMT. See 
https://gitlab.com/pcmt/pcmt/issues/212.

## Configure

Environment variables to configure how this container runs.  Ones without
a default are *required*.  Aside from `FTP_GET_CREDS`, any may be set in the
file that `FTP_GET_CREDS` points to.

1. `FTP_GET_CREDS`: Where the file that contains the credentials will be
    mounted into the container. Default: `/run/secrets/ftp-get-creds`
1. `SFTP_USER`: The SFTP username, should be in `FTP_GET_CREDS`.
1. `SFTP_HOST`: The SFTP hostname, should be in `FTP_GET_CREDS`.
1. `SFTP_PRIVKEY_FILENAME`: Set where the file that contains the SFTP user's
    private OpenSSH key will be mounted into the container.
1. `SFTP_LOCAL_DIR`: Where the files from the SFTP server will be downloaded
    to.  Default: `/downloads`.
1. `SFTP_REMOTE_DIR`: The remote directory to get `*.xml` files from.
1. `SFTP_REMOTE_ARCHIVE_DIR`: The remote archive directory to move files
    to, once they're downloaded from `SFTP_REMOTE_DIR`.

## Example

1. Run a one-off, placing files into named volume

    ```shell
    docker volume create ftp-downloads
    docker run -it --rm \
        -v "$(pwd)/conf/ftp-get-creds.env:/run/secrets/ftp-get-creds" \
        -v "$(pwd)/conf/sftp-key-openssh:/run/secrets/sftp-key-openssh" \
        -e SFTP_PRIVKEY_FILENAME="/run/secrets/sftp-key-openssh" \
        -v "ftp-downloads:/downloads" \
        --entrypoint /cmd-to-run.sh \
        pcmt/ftp-get
    docker run -it --rm -v "ftp-downloads:/downloads" alpine:3.11 ls /downloads
    ```

1. Run interactive sftp session

    ```shell
    docker run -it --rm \
        -v "$(pwd)/conf/ftp-get-creds.env:/run/secrets/ftp-get-creds" \
        -v "$(pwd)/conf/sftp-key-openssh:/run/secrets/sftp-key-openssh" \
        -e SFTP_PRIVKEY_FILENAME="/run/secrets/sftp-key-openssh" \
        --entrypoint /ftp-cmd.sh \
        pcmt/ftp-get
    ```

1. Run on schedule

    ```shell
    docker volume create ftp-downloads
    docker run -d \
        -e CRON_SCHEDULE='* * * * *' \
        -v "$(pwd)/conf/ftp-get-creds.env:/run/secrets/ftp-get-creds" \
        -v "$(pwd)/conf/sftp-key-openssh:/run/secrets/sftp-key-openssh" \
        -e SFTP_PRIVKEY_FILENAME="/run/secrets/sftp-key-openssh" \
        -v "ftp-downloads:/downloads" \
        pcmt/ftp-get
    docker run -it --rm -v "ftp-downloads:/downloads" alpine:3.11 \
        watch ls /downloads
    ```


