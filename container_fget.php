#!/usr/bin/env php
<?php

require __DIR__.'/app.lib.php';

if ($argc < 4) {
    exit('Usage: <server_name> <file_in_container> <destination_path_on_local>');
}

$container_file_path = $argv[2];
$destination_path = $argv[3];

if ($service && $server_ip && $ssh_user) {

    $ssh_command = "ssh -i $ssh_key $ssh_user@$server_ip 'docker container ls --filter label=service=$service --format \"{{.ID}}\"'";
    $container_id = trim(shell_exec($ssh_command));

    if (empty($container_id)) {
        exit("Error: No running container found with label 'service=$service' on server $server_ip.\n");
    }

    echo "Copying file '$container_file_path' from container '$container_id' to '$destination_path' on local machine...\n";
    $copy_to_tmp_command = "ssh -i $ssh_key $ssh_user@$server_ip 'docker cp $container_id:$container_file_path /tmp/".basename($container_file_path)."'";
    echo "Executing: $copy_to_tmp_command\n";
    passthru($copy_to_tmp_command);

    $scp_command = "scp -i $ssh_key $ssh_user@$server_ip:/tmp/".basename($container_file_path)." $destination_path";
    echo "Executing: $scp_command\n";
    passthru($scp_command);

    $cleanup_command = "ssh -i $ssh_key $ssh_user@$server_ip 'rm /tmp/".basename($container_file_path)."'";
    echo "Executing: $cleanup_command\n";
    passthru($cleanup_command);

} else {
    echo "Error: Service label not found in the YAML file.\n";
    exit(1);
}
