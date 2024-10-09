#!/usr/bin/env php
<?php

require __DIR__.'/app.lib.php';

$laravel_app_path = $config['laravel_app_path'] ?? exit('Error: Missing laravel_app_path in config/deploy.yml'.PHP_EOL);
$laravel_app_path = rtrim($laravel_app_path, '/').'/';

$default_sqlite_file = 'database/database.sqlite';

$default_remote_sqlite_file = $laravel_app_path.$default_sqlite_file;

$remote_project_path = $laravel_app_path; // Database filename on server
// $local_project_path = './'; // Local database filename
$local_project_path = 'server_file/'; // Local database filename

if ($ssh_user && $server_ip && $ssh_key && $remote_project_path && $local_project_path) {
    echo "Fetching .env file from $server_ip...\n";

    // Read the configurateion of .env file from the server
    $remote_env_file = $remote_project_path.'.env';
    $local_env_temp = '/tmp/.env_remote_'.uniqid();

    $scp_env_cmd = "scp -i $ssh_key $ssh_user@$server_ip:'$remote_env_file' '$local_env_temp'";
    echo "Executing: $scp_env_cmd\n";
    passthru($scp_env_cmd, $return_var);

    if ($return_var !== 0) {
        echo "Failed to copy .env file from server.\n";
        exit(1);
    }

    $env_contents = file_get_contents($local_env_temp);
    if ($env_contents === false) {
        echo "Failed to read the .env file.\n";
        unlink($local_env_temp);
        exit(1);
    }

    $db_database = null;
    $lines = explode("\n", $env_contents);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, 'DB_CONNECTION=sqlite') !== false) {
            foreach ($lines as $db_line) {
                $db_line = trim($db_line);
                if (strpos($db_line, 'DB_DATABASE=') === 0) {
                    $db_database = substr($db_line, strlen('DB_DATABASE='));
                    $db_database = trim($db_database, "\"'"); // Remove quotes if any
                    break 2; // Exit both loops
                } else {
                    $db_database = $default_sqlite_file;
                }
            }
        }
    }

    unlink($local_env_temp);
    if (! $db_database) {
        echo "DB_CONNECTION is not sqlite.\n";
        exit(1);
    }

    $remote_db_file = $remote_project_path.$db_database;
    echo "Server Database: $remote_db_file\n";

    // Backup existing local database file with a timestamp
    $local_db_file = $local_project_path.$db_database;
    echo "Local Database: $local_db_file\n";
    if (file_exists($local_db_file)) {
        $timestamp = date('YmdHis');
        $backup_db_file = $local_db_file.'_'.$timestamp;
        rename($local_db_file, $backup_db_file);
        echo "Old database renamed to $backup_db_file\n";
    }

    // Get the size of the remote database file using SSH
    echo "Fetching remote database size...\n";
    $ssh_cmd = "ssh -i $ssh_key $ssh_user@$server_ip 'stat -c%s \"$remote_db_file\"'";
    $remote_db_size = (int) trim(shell_exec($ssh_cmd));
    if ($remote_db_size <= 0) {
        echo "Failed to determine remote database size or file is empty.\n";
        exit(1);
    }
    function human_filesize($bytes, $decimals = 2)
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).@$sz[$factor];
    }
    $remote_db_size_text = human_filesize($remote_db_size);
    echo "Remote database size: $remote_db_size_text\n";
    echo "Starting database download...\n";

    // Copy the SQLite database file from server to local
    $scp_db_cmd = "scp -i $ssh_key $ssh_user@$server_ip:'$remote_db_file' '$local_db_file'";
    echo "Executing: $scp_db_cmd\n";

    $descriptorspec = [
        1 => ['pipe', 'w'],  // stdout
        2 => ['pipe', 'w'],   // stderr
    ];

    $process = proc_open($scp_db_cmd, $descriptorspec, $pipes);

    $progress = 0;
    if (is_resource($process)) {
        // Read real-time output from both stdout and stderr
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        while (! feof($pipes[1]) || ! feof($pipes[2])) {
            if ($progress < 100) {
                if (file_exists($local_db_file)) {
                    clearstatcache();
                    $local_db_size = filesize($local_db_file);
                    $progress = min(100, round(($local_db_size / $remote_db_size) * 100));
                    echo "Download progress: $progress%\r";
                }
                sleep(1); // Check every second
            }
            while ($line = fgets($pipes[1])) {
                echo 'STDOUT: '.$line;
            }
            while ($line = fgets($pipes[2])) {
                echo 'STDERR: '.$line;
            }
            usleep(100000);
        }
        fclose($pipes[1]);
        fclose($pipes[2]);

        $return_var = proc_close($process);
        if ($return_var === 0) {
            echo "Database successfully copied to $local_db_file\n";
        } else {
            echo "Failed to copy database file.\n";
        }
    } else {
        echo "Failed to execute scp command.\n";
        exit(1);
    }
    // passthru($scp_db_cmd, $return_var);
    //
    // if ($return_var === 0) {
    //     echo "Database successfully copied to $local_db_file\n";
    // } else {
    //     echo "Failed to copy database file.\n";
    //     exit(1);
    // }
} else {
    echo "Error: Missing SSH key, user, server IP, project path, or local database path.\n";
    exit(1);
}
