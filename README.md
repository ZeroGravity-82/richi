
# Web application for personal finance accounting "Richi"

Quickstart
----------

To configure PhpStorm + Docker + Xdebug, please see this article: <https://blog.denisbondar.com/post/phpstorm_docker_xdebug/>

This project requires the Docker Compose plugin to be installed: <https://docs.docker.com/compose/install/linux/>

Run the following console commands before starting local development:
```bash
export HOST_USER_UID=$(id -u) && export HOST_USER_GID=$(id -g);
make init

# wait for the MySQL server to initialize (it usually takes a few minutes), then do the following:
make db-init
```
**Tip**: To avoid manually creating the HOST_USER_UID and HOST_USER_GID variables each time, just add their creation to the ~/.bashrc file.

CLI tools
---------

For convenient work with the project through Docker, use Makefile. It contains examples of how to use all the CLI tools available to you.
