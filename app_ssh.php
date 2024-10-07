#!/usr/bin/env php
<?php

require __DIR__.'/app.lib.php';

if ($ssh_user && $server_ip) {
    echo "Connecting to $server_ip as $ssh_user...\n";
    $cmd = "ssh -i $ssh_key $ssh_user@$server_ip";
    echo "Executing: $cmd\n";
    passthru($cmd);
} else {
    echo "Error: Missing SSH key, user, or server IP for server '$default_server'.\n";
    exit(1);
}
