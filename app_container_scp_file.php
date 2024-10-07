#!/usr/bin/env php
<?php

require __DIR__.'/app.lib.php';

if ($argc < 4) {
    exit("Usage: <local_file> <destination_path_in_container>\n");
}

$local_file = $argv[2];
$destination_path = $argv[3];

if ($service && $server_ip && $ssh_user) {

    $ssh_command = "ssh -i $ssh_key $ssh_user@$server_ip 'docker container ls --filter label=service=$service --format \"{{.ID}}\"'";
    $container_id = trim(shell_exec($ssh_command));

    if (empty($container_id)) {
        exit("Error: No running container found with label 'service=$service' on server $server_ip.\n");
    }

    echo "Copying file '$local_file' to container '$container_id' at '$destination_path' on server $server_ip...\n";
    $scp_command = "scp -i $ssh_key $local_file $ssh_user@$server_ip:/tmp/";
    echo "Executing: $scp_command\n";
    passthru($scp_command);

    $move_file_command = "ssh -i $ssh_key $ssh_user@$server_ip 'docker cp /tmp/".basename($local_file)." $container_id:$destination_path'";
    echo "Executing: $move_file_command\n";
    passthru($move_file_command);

    $cleanup_command = "ssh -i $ssh_key $ssh_user@$server_ip 'rm /tmp/".basename($local_file)."'";
    echo "Executing: $cleanup_command\n";
    passthru($cleanup_command);

} else {
    echo "Error: Service label not found in the YAML file.\n";
    exit(1);
}
