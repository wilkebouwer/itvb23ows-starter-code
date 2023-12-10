# ITVB23OWS Development Pipelines starter code

This repository contains starter code for the course ITVB23OWS Development pipelines,
which is part of the HBO-ICT program at Hanze University of Applied Sciences in
Groningen.

This is a deliberately poor software project, containing bugs and missing features. It
is not intended as a demonstration of proper software engineering techniques.

The application contains PHP 5.6 code and should run using the built-in PHP server,
which can be started using the following command.

```
php -S localhost:8000
```

In addition to PHP 5.6 or higher, the code requires the mysqli extension and a MySQL
or compatible server. The application assumes a root user without password, and tries
to access the database `hive`. The file `hive.sql` contains the database schema.

This application is licensed under the MIT license, see `LICENSE.md`. Questions
and comments can be directed to
[Ralf van den Broek](https://github.com/ralfvandenbroek).

## Running the application with Docker

The following commands should be run from the Git root.

### Production

To only the production containers, execute the following commands:

`cp .env-example .env`

If you want you can change environment variables in `.env`. Don't worry about the JENKINS.* and DOCKER_GID variables if you're planning to only run the production configuration. They can be kept unchanged.

`docker-compose build app database`

`docker-compose up app database`

The app should now be accessible from `http://localhost:APP_PORT/`

### Development

Setting up the development containers is a bit more involved. Included in the development setup is a tool for setting up development pipelines called Jenkins, which runs in a Docker container, but also needs the capability to run Docker containers from within the container. This is called Docker in Docker, and it requires some special configuration.

Instead of copying the `.env-example` file in the normal way, it's required to run the following command, which does it for you:

`sed "s/\(DOCKER_GID=\)/\1$(grep '^docker' /etc/group | cut -d':' -f3)/" .env-example > .env`

This command changes the `DOCKER_GID` variable to the GID of the docker group on your host system, because the docker group in the Jenkins container will need to match this GID as well for the configuration to work. This is because the dockerd socket from the host is used from within the Jenkins container through a volume. After running this command, you're free to change any other environment variable in `.env` you want.

After this, build and start all containers:

`docker-compose build`

`docker-compose up`

Jenkins should now accessible from `http://localhost:JENKINS_PORT/`, using the credentials `JENKINS_ADMIN_ID` as username and `JENKINS_ADMIN_PASSWORD` as password. It will automatically run a pipeline that checks if the production setup succesfully builds and runs. The app should now be accessible from `http://localhost:APP_PORT/`
