
# Evwrit Docker

Read [Important Notes](#important-notes) thoroughly before starting! 

## 1. Environment Setup

First, create `.env.dev` and `.env.prod` files from the given `example.env` file. Fill in the correct values for the variables in `example.env` and create the env files using the following command:
```sh
cp example.env .env.prod
# Similarly for .env.dev
cp example.env app/.env.dev
```

Additionally, update the PGAdmin variables in `pgadmin.env` to the desired login credentials.

## 2. Data Setup for PostgreSQL

In the `initdb` folder, you can find two example scripts: `001-create-role.sql` and `100-alter-grants.sql`. It is recommended to modify these scripts to suit your needs and also add a SQL script for the actual data with a name starting with `010-` inside this folder. You can also add `.sh` scripts if required. The docker-compose will import the data when the containers are created.

## 3. Building the `evwrit` Image

Add the correct SSH key to the agent if building in production, and then run:
```sh
# For production build
docker buildx build --tag evwritdocker --target prod --ssh default .

# For development build
docker buildx build --tag evwritdocker --target dev --ssh default .
```
Here, `--target` specifies whether it should build in `dev` or `prod`.

## 4. Docker Compose Build

### 4.1 Production Build

To start up the production build, use the following command:
```sh
docker compose up --build
```

#### Notes:
- Ensure you have the ssh-agent active with the correct SSH key!\
- If an external database is used, **comment/remove** the code in `docker-compose.yaml` under `psql-db`.

### 4.2 Development Build in Docker

To build the dev environment in Docker, run:
```sh
docker compose -f compose.dev.yaml up --build 
```
Then, to install backend PHP modules, install frontend dependencies, and start Symfony (web), run the following command:
```sh
docker exec -it evwritdocker-dev sh startup-script.sh
```

Finally, index Elasticsearch on the desired string using the following command:
```sh
docker exec -it evwritdocker-dev php bin/console app:elasticsearch:index <your index> [max limit]
```

The web interface can be accessed on [localhost:8080](http://localhost:8080) and PGAdmin on [localhost:5050](http://localhost:5050).

#### Notes:
- The `thewatcher` container will keep restarting until the following script has been run:
```sh
startup-script.sh
```
- To remove the containers, execute:
```sh
docker compose -f compose.dev.yaml down
```

### 4.3 Development Build using Dev Container

When building using a dev container, the startup script and the indexing for Elasticsearch are done using `postCreateCommand` found in `.devcontainer/devcontainer.json`. Edit this command to the required index and limit. After that, build the dev container and reopen VSCode in the container.

This command is handy because it is only executed once: when creating the containers.

## IMPORTANT NOTES

- Ensure you have your SSH key required for the build in your local `.ssh` folder. For production builds, it is necessary to start and add your SSH key in the same terminal window (unless set up via config file) as where the build command is executed.

- For production, it takes some time after `docker compose up` for Elasticsearch to be fully operational. Checking the website too soon might result in Elasticsearch errors such as "is Elasticsearch down" or "all shards failed" until Elasticsearch is ready to work. The same applies to PostgreSQL, where the wait time can be much longer. You need to wait until the scripts in the `initdb` folder have been executed successfully (check logs of the respective container).

- The build will break if there are errors in the SQL scripts. Examples of these errors include `CREATE SCHEMA public` if the schema already exists. These errors can be easily fixed by using checks via the `EXISTS` operator in your SQL script.

- The SQL scripts in `initdb` should be named in the correct order. A good example is naming the first script `001-<first file>.sql`, the next script `010-<second file>.sql`, and the last script `100-<third file>.sql`.

- Once the `evwrit` container is successfully created, be sure to run the following command before using the website:
  ```sh
  php bin/console app:elasticsearch:index <your index name> [max limit]
  ```

- When running in development, three folders will be created locally: `node_modules`, `vendor`, and `var` (for PostgreSQL and Elasticsearch data). If you want to run in production after development, be sure to delete these three folders!
