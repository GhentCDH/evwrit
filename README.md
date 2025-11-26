# Evwrit

This repository contains the source code of the [Everyday Writing](https://www.evwrit.ugent.be/) database.

![img.png](img.png)

The Evwrit database consists of a Symphony back-end connected to a PostgreSQL database and Elasticsearch search engine.
The search and edit pages consist of Vue.js applications.

## Getting Started

First, check that `.env` contains the correct default configuration (see `example.env`). Additionally, update the PGAdmin variables in `pgadmin.env` to the desired login credentials.

Run the following command to run the docker services:

- PHP Symfony
- Elasticsearch
- DBBE postgres database
- Node.js
- pgAdmin

```sh
docker compose up --build 
```

After the containers are up and running, you can access the Evwrit database on [localhost:8080](http://localhost:8080).

## Database

In the `initdb` folder, you can find the necessary scripts to create the database schema and a minimum test dataset. The
sql scripts are run when when the database container is first created.

You can add additional scripts to the `initdb` folder if required.

## Indexing

During the first run, the startup script will create (if needed) initial indexes for the Elasticsearch search engine (
100 records max).

To index more records, run the following command:

```sh
docker exec -it evwrit-dev-symfony-1 php bin/console app:elasticsearch:index text [max limit]
docker exec -it evwrit-dev-symfony-1 php bin/console app:elasticsearch:index level [max limit]
```

## Credits

Development by [Ghent Centre for Digital Humanities - Ghent University](https://www.ghentcdh.ugent.be/). Funded by the [GhentCDH research projects](https://www.ghentcdh.ugent.be/projects).

<img src="https://www.ghentcdh.ugent.be/ghentcdh_logo_blue_text_transparent_bg_landscape.svg" alt="Landscape" width="500">
