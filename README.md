# CowboyPHP
PHP tools for managing docker containers on a remote server from the development project folder.

## Installation

First, go to your PHP project folder.

### Create the server config file

Create `config/deploy.yml`

For, example:
```yaml
# Name of your application. Used to uniquely configure containers.
service: appname

# Name of the container image.
image: appname/version

ssh:
  keys: ["~/vault/myserver.pem"]
  user: root

# Deploy to these servers.
servers:
  web:
    - 192.168.0.1

```

### Add the script to the project

Clone this project, download the dependencies, and make the scripts executable.

```bash
git clone https://github.com/ruhendrawan/cowboyphp.git cbd
composer require symfony/yaml --dev
chmod +x cbd/*.php
```


## Available Scripts

SSH to the server.
```bash
cbd/app_ssh.php
```

List the docker container in the server
```bash
cbd/app_containers.php
```

Open the shell of the docker container in the server
```bash
cbd/app_container_shell.php
```

Put a file inside the docker container on the server. 
Example use case: update .env.production
```bash
cbd/app_container_scp_file.php
```


## Optional
This tool could be an extension to https://kamal-deploy.org/

Install with `gem install kamal`
Then, generate `config/deploy.yml` by executing `kamal init`

