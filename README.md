

# Evwrit Docker

Read [Important Notes](#important-notes) thoroughly before starting! 

## Environment Setup

First, create `.env.dev` and `.env.prod` files from the given `example.env` file. Fill in the right values for the variables in `example.env` and create the `.env` files using the following command:
```sh
cp example.env .env.prod
# Similarly for .env.dev
cp example.env app/.env.dev
```

## Startup Production Build

To start up the production build, use the following command:
```sh
docker compose up --build evwritdocker
```

## Building the `evwrit` Image

Add the right SSH key to the agent if building in production, and then run:
```sh
docker buildx build --tag evwritdocker --target prod --ssh default .
```
Here, `--target` specifies whether it should build in `dev` or `prod`.

## Building Dev in Docker

To build the dev environment in Docker, run:
```sh
docker compose -f compose.dev.yaml up --build evwritdocker
```
Then, attach to the `evwritdocker` container and run the following script:
```sh
./startup-script.sh
```

### Note:
The `thewatcher` container will keep restarting until the following script has been run:
```sh
./startup-script.sh
```

## IMPORTANT NOTES

- For production, it takes some time after `docker compose up` for Elasticsearch to be fully operational. Checking the website too soon might result in Elasticsearch errors such as "is Elasticsearch down" or "all shards failed" until Elasticsearch is ready to work. The same applies to PostgreSQL, where the wait time can be much longer. You need to wait until the scripts in the `initdb` folder have been executed successfully (check logs of respective container).

- The build will break if there are errors in the SQL scripts. Examples of these errors include `CREATE SCHEMA public` if the schema already exists. These errors can be easily fixed by using checks via the `EXISTS` operator in your SQL script.

- The SQL scripts in `initdb` should be named in the correct order. A good example is naming the first script `001-<first file>.sql`, the next script `010-<second file>.sql`, and the last script `100-<third file>.sql`.

- Once the `evwrit` container is successfully created, be sure to run the following command before using the website:
  ```sh
  php bin/console app:elasticsearch:index <your index name> [<max limit>]
  ```

- When running in development, 3 folders will be created locally: `node_modules`, `vendor`, and `var` (for PostgreSQL and Elasticsearch data). If you want to run in production after development, be sure to delete these 3 folders!

## ISSUES

There is an issue with getting the dev build to run in a dev container. The problem encountered is that the dev container gives the following error:
```sh
invalid empty ssh agent socket: make sure SSH_AUTH_SOCK is set
```
Even though the SSH socket had been set correctly (it was visible in the output logs some lines above the error).

For the dev container, the scripts `startup_script.sh` and `dev_script.sh` were used. The lines in `devcontainer.json` are mostly commented out. The commented lines show most of the things that have been tried to solve the issue but did not fix it.
