#!/usr/bin/env php
<?php

require __DIR__.'/app.lib.php';

if (! $ssh_user || ! $server_ip) {
    exit("Error: Missing SSH connection details (key, user, or server IP).\n");
}

if ($service) {

    $ssh_command = "ssh -i $ssh_key $ssh_user@$server_ip 'docker container ls --filter label=service=$service --format \"{{.ID}}\"'";
    echo "Listing containers with label 'service=$service' on server $server_ip...\n";
    echo "Executing: $ssh_command\n";
    $container_id = trim(shell_exec($ssh_command));

    if (empty($container_id)) {
        exit("Error: No running container found with label 'service=$service' on server $server_ip.\n");
    }

    echo "Opening shell in container '$container_id' on server $server_ip...\n";
    $shell_command = "ssh -t -i $ssh_key $ssh_user@$server_ip 'docker exec -it $container_id sh'";
    echo "Executing: $shell_command\n";
    passthru($shell_command);
} else {
    echo "Error: Service label not found in the YAML file.\n";
    exit(1);
}
