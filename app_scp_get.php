#!/usr/bin/env php
<?php

require __DIR__.'/app.lib.php';

if ($argc < 4) {
    exit("Usage: <server> <remote_file> <local_path_or_file>\n");
}

$source_file_path = $argv[2]; // remote
$destination_file_path = $argv[3]; // local

if ($ssh_user && $server_ip && $ssh_key && $source_file_path && $destination_file_path) {
    echo "Copying file from $server_ip to local machine...\n";
    $cmd = "scp -i $ssh_key $ssh_user@$server_ip:'$source_file_path' '$destination_file_path'";
    echo "Executing: $cmd\n";
    passthru($cmd, $return_var);
    if ($return_var === 0) {
        echo "File successfully copied to $destination_file_path\n";
    } else {
        echo "Failed to copy file\n";
        exit(1);
    }
} else {
    echo "Error: Missing SSH key, user, server IP, source or destination file path.\n";
    exit(1);
}
