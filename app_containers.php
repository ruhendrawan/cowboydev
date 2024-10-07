#!/usr/bin/env php
<?php

require __DIR__.'/app.lib.php';

if (! $ssh_user || ! $server_ip) {
    exit("Error: Missing SSH connection details (key, user, or server IP).\n");
}

if ($service) {
    $ssh_command = "ssh -i $ssh_key $ssh_user@$server_ip 'docker container ls --all --filter label=service=$service'";
    echo "Listing containers with label 'service=$service' on server $server_ip...\n";
    echo "Executing: $ssh_command\n";
    passthru($ssh_command);
} else {
    echo "Error: Service label not found in the YAML file.\n";
    exit(1);
}
