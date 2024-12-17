#!/usr/bin/env php
<?php

require __DIR__.'/app.lib.php';

if ($argc < 4) {
    exit("Usage: <server> local_path_or_file <remote_path_or_file>\n");
}

$local_file_path = $argv[2]; // local
$remote_file_path = $argv[3]; // remote

if ($ssh_user && $server_ip && $ssh_key && $remote_file_path && $local_file_path) {
    echo "Copying file from $server_ip to local machine...\n";
    $cmd = "scp -pr -i $ssh_key '$local_file_path' $ssh_user@$server_ip:'$remote_file_path'";
    echo "Executing: $cmd\n";
    passthru($cmd, $return_var);
    if ($return_var === 0) {
        echo "File successfully copied to $local_file_path\n";
    } else {
        echo "Failed to copy file\n";
        exit(1);
    }
} else {
    echo "Error: Missing SSH key, user, server IP, source or destination file path.\n";
    exit(1);
}
