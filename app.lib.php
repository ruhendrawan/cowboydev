<?php

require_once __DIR__.'/vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

$yaml_file = 'config/deploy.yml';

if (! file_exists($yaml_file)) {
    exit("Error: YAML file '$yaml_file' not found.\n");
}

$yaml_content = file_get_contents($yaml_file);
if ($yaml_content === false) {
    exit("Error: Unable to read the YAML file.\n");
}

$config = Yaml::parse($yaml_content);

$service = $config['service'] ?? null;

$ssh_key = $config['ssh']['keys'][0] ?? null;
$ssh_user = $config['ssh']['user'] ?? null;
$available_servers = $config['servers'] ?? [];

$default_server = $argv[1] ?? 'web';

if (! isset($available_servers[$default_server])) {
    echo "Error: Server '$default_server' not found.\n";
    echo 'Available servers are: '.implode(', ', array_keys($available_servers))."\n";
    exit(1);
}

$server_ip = $available_servers[$default_server][0] ?? null;
