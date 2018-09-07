When you first run the `voyage init` command, Voyage tool creates a directory '.voyage' in the current path. This directory contains the following:

| Directory / File        | Description                                                           |
|-------------------------|-----------------------------------------------------------------------|
| `.voyage/backups/`      | Files with database backups.                                          |
| `.voyage/migrations/`   | A directory which contains migration files.                           |
| `.voyage/environments/` | A directory which contains configuration files for each environment.  |
| `.voyage/environment`   | Configuration file which contains name of the current environment.    |
| `.voyage/ignore`        | List of tables / records which should be ignored.                     |

## Version Control
We recommend to keep all files except for backups and current environment configuration file under version control. Add the following lines to your `.gitignore`:
```
.voyage/environment
.voyage/backups/
```

## Migrations
All migration files are located in .voyage/migrations directory. Migrations are created automatically when you run `voyage make` command. You shouldn't manually edit migration files (unless you know what you're doing).

## Environments
This directory contains configuration files for each environment. I.e. you can create configuration files for your local development server, staging and production server. Each environment configuration file consists of two sections:

### Database Settings
These are set of parameters to connect to database in current environment, it includes the following reserved parameters:
`host` (in format host:port or ip:port, for example: localhost:3306 or 127.0.0.1:3306)
`username`, `password` (it can be empty but the parameter should be included), `database`

Example of configuration:
```
host=localhost:3306
username=root
password=dbaccess
database=test
```

### Replacement variables

This section contains a list of replacement variables which allows to adjust data to your current environment. Here's example on how it works:
* For example, you start development on your local PC where you have URL: http://local.dev/. You initialize Voyage and create a "local" environment configuration.
* Live version of the website is hosted at https://www.example.com/ (Voyage environment configuration file is "production")
* If you will be copying data from one server to another, you may need to replace URLs in database from http://local.dev/ to https://www.example.com/ (or otherwise). With Voyage you can add a replacement variables in format: `PARAM_NAME=REPLACEMENT_VALUE`. To do that, edit `.voyage/environments/local` configuration file and add the following:
```
SITEURL=http://local.dev
```
* And in `.voyage/environments/production` configuration file:
```
SITEURL=https://www.example.com
```

_**Notice:** you can add as many custom replacement variables as needed._

* When you will be creating migration(s) on your local environment, voyage will replace all instances of "http://local.dev" to "SITEURL" replacement variable and then when you will be deploying changes to production server all instances of "SITEURL" will be replaced to "https://www.example.com". This works with serialized arrays stored in database too (i.e. it won't break things in WordPress CMS).
* Name of replacement variable shouldn't contain spaces, quotes and shouldn't be a reserved name (the one from variables listed in "Database Settings" chapter, i.e.: host, username, password or database)
* Name of replacement variable is case-sensitive

You can also add multiple replacement variables of the same name. Example:

* Sometimes CMS can store different forms of URL in database for example: http://example.com, https://www.example.com,http://www.example.com. If you declare only one replacement variable for "https://www.example.com" then it won't replace all occurences, therefore it's suggested to use multiple replacement variables of the same name, for example:

```
SITEURL=https://www.example.com  # The first declaration is your desired value.
SITEURL=https://example.com
SITEURL=http://www.example.com
SITEURL=http://example.com
```

* In this case Voyage will replace all occurences of the URL to placeholder "SITEURL" when you will be making a migration.
* And will replace "SITEURL" to the first declaration when you will be applying a migration.

### Environment Name

A configuration file which contains name of the current / active environment is located at `.voyage/environment`. This is basically a name of a file in .voyage/environments/ directory. _This file shouldn't be included to your VCS repository._

### Ignore List

A configuration file which contains a list of tables & values which should be ignored is located at `.voyage/ignore`. In this configuration file you can include tables which contain debug, log or binary data, those tables will be ignored by voyage app when it will be making a migration. You should add one ignore rule per line. A table name without any parameters will be ignored completely (data and structure). If you would like to keep tracking changes of table's structure but ignore data in the table then you should add a "~" before table's name.

#### Ignore Tables

For example:

`~users` - this will record only changes in structure of the `users` table.
`users` - this will completely ignore table `users` (records and any changes on table's structure).

**Wildcards**
Ignore list also supports wildcards, just use an asterisk character - * to add a wildcard, i.e.:

`~*_log` - will ignore records in all tables which ends with "_log" string.
`*_cache_*` - will ignore all tables completely which contain "_cache_" string inside of table name.
`~debug_*` - will ignore data of all tables which starts with "debug_" string.

#### Ignore Rows
In addition to tables you can also ignore specific records in database tables. Format of ignore rule is:
```
TABLENAME.FIELDNAME=VALUE
```

You can also use wildcards which will work similar to SQL LIKE, such as:
```
TABLENAME.FIELDNAME=*VALUE*
```

**For example:**

`wp_options.option_name=xxx` - will ignore a record in `wp_options` table where field `option_name` contains exact value (case-sensitive) of 'xxx'

`wp_options.option_name=*transient*` - will ignore records in `wp_options` table where field `option_name` contains any values which contain word 'transient' (case-sensitive)

`wp_options.option_name=*transient` - will ignore records in `wp_options` table where value of field `option_name` ends with word 'transient' (case-sensitive)

#### Ignore Fields

You can also ignore changes in specific fields (for example if there's a table with pages and a field with page views count, in this case you only want to store changes of content but exclude changes in number of views because it will always vary). Please notice that this will ignore only UPDATEs on tables with primary keys. To do that you can add the following ignore rule:

```
TABLENAME.FIELDNAME
```

**For example:**

```
pages.pageviews
```

This will ignore any changes in field "pageviews" of "pages" table.