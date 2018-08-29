# voyage
Voyage is a command-line tool to create, apply (and rollback) database migrations automatically. This tool has been created as a part of implementation of continuous integration workflow for PHP projects and keep changes in database under version control. 

# Get Started
Voyage is a command-line tool which allows to create database migrations, which is especially usable for various content management systems (such as, WordPress, Drupal, etc) where most of information stores in database. Voyage allows custom replace variables, which is very useful if your app or a CMS stores URLs or paths in database and you need to switch between various environments (i.e. host in your local environment is "http://localhost/" and your staging URL is "https://staging.example.com"), Voyage will replace that URL with a placeholder before creating migration and will replace placeholder with the URL in your configuration file before applying migration (this also works for serialized data).

>**Performance Notice**: Voyage performs the best with PHP 7.0 or higher (it's also compatible to PHP 5.5+ but since it does a lot of checks and verifications you should be aware that it may run slow if your database has a lot of records, so make sure to ignore tables which shouldn't be versioned, such as: comments, order data, debug info, logs, etc.) 

## Installation

### Linux & Mac OS
One liner:
```bash
sudo -- sh -c 'curl -o /tmp/voyage http://voyage.hirebrains.co/latest/voyage && mv /tmp/voyage /usr/local/bin/ && chmod +x /usr/local/bin/voyage'
```

### Microsoft Windows
There's no one install command available for MS Windows but you can follow these steps to install it manually:
1. Install Cygwin
2. Download voyage from http://voyage.hirebrains.co/latest/voyage
3. Create a directory in "Program Files\voyage\" and move the downloaded file there
4. Add path to voyage to your %PATH% variable

### Build From Sources
1. Clone voyage repository 
2. Run `composer install`
3. Run `bash ./compile.sh`
4. Voyage binary will be available in `<VOYAGE_DIR>/bin/` directory

## Frequently Asked Questions
**Do I need PHP installed to run Voyage?**
Yes, Voyage is written in PHP and it's required to run Voyage on your system.

**What is 'voyage' file?**
The file you download from http://voyage.hirebrains.co/latest/voyage is a PHAR archive with PHP source code, if you would like to view source code of voyage you can rename voyage to voyage.phar and extact it.

**Which Relation Management Databases are supported?**
Voyage currently supports MySQL only. It can sync everything except for changes in stored procedures and functions.

**How do I upgrade Voyage?**
Voyage has a command to retrieve the latest version from server. Just run `voyage selfupdate` via command-line and it will automatically upgrade itself (if a new version is available). Make sure to run this command via sudo (so that voyage has sufficient permissions to write to itself).


# Voyage Commands

| Command         | Description                                                                                                             |
|-----------------|-------------------------------------------------------------------------------------------------------------------------|
| *voyage init*     | Initialize application and create first dump of the database.                                                           |
| *voyage status*   | Check current status (current migration and list of migrations that hasn't been imported yet).                          |
| *voyage make*     | Calculate difference between current database state and latest migration and create a new migration containing changes. |
| *voyage rollback* | Rollback to previous migration.                                                                                         |
| *voyage apply*    | Apply latest migration to the current database.                                                                         |
| *voyage list*     | Get list of all migrations.                                                                                             |
| *voyage backup*   | Create backup of tables and data (except for the data and records in ignore file). It also saves migrations state.      |
| *voyage restore*  | Restores table and data from backup.                                                                                    |
| *voyage reset*    | Reset database state and re-apply all migrations.                                                                       |

## voyage init

This command initializes Voyage tool and creates a first full dump of database from the current host. This command will create dump with
structure of all database tables event if they are in ignore list (no data will be taken for tables which are in the ignore list). This command is
interactive and will ask you for a list of custom variables. It accepts the following parameters:

```
Parameter Description
```
```
-f or --force Continue execution and overwrite existing .voyage configuration
and clean existing migrations (otherwise Voyage will terminate
execution if working directory already contain a .voyage directory).
```
```
-c or --config="..." Specify a platform, in this case Voyage will detect database
connection settings automatically. For example:
--config=wordpress.
```
```
Available values are:
```
```
auto (default)
none (no verification for configuration files will be performed,
Voyage will ask for database access credentials)
wordpress
magento
magento
```
```
This is important to pass --config parameter if you develop a
website based on CMS, in this case voyage will automatically
generate ignore file which will contain a list of tables that should
be excluded for specific CMS including popular plugins &
extension.
```
```
--host="" Database host (default: localhost, the tool will ask for it if not set)
```
```
-u or --user="" Database username (the tool will ask for it if not set)
```
```
-d or --db="" Database name (the tool will ask for it if not set)
```

```
-p or --pass="" Database password (the tool will ask for it if not set)
```
```
--env="" Optional name of the environment to be created.
```
#### Example

##### # Starts initialization process, the tool will ask for any needed

##### information

##### voyage init

##### # Initialize and overwrite existing config and data if it exists

##### voyage init -f

##### # Initialize Voyage for WordPress

##### voyage init --config=wordpress

##### # OR

##### voyage init -cwordpress

##### # Initialize voyage with given db access info

##### voyage init -uroot -pdbaccess -ddbname

##### # Initialize voyage with given db access info, custom port and will

##### initialize a new environment with name "local"

##### voyage init --host=localhost:3307 --user=root --pass=dbaccess

##### --db=dbname --env=local

## voyage status

Get current migration ID and list of migrations (if there're any) that ahead of current migration. This command doesn't take any parameters
and will fail if Voyage hasn't been initialized.

### voyage make

This command calculates difference between latest migration and current database state and creates a new migration (if there're any
changes in database) based on difference. This command doesn't take any parameters and will fail if Voyage hasn't been initialized.

```
Parameter Description
```
```
--name="..." Name of the migration. Voyage will prompt for it if not provided via
parameter.
```
### voyage rollback

Rollback a migration and apply changes to current database. If no parameters set the command will return to previous migration.The
command accepts the following parameters:

```
Parameter Description
```
```
-m or --migration="..." Migration ID (can be retrieved using `voyage list` command).
```
### voyage apply

Apply all migrations that hasn't been applied to the current database yet. This command doesn't take any parameters and will fail if Voyage
hasn't been initialized.


### voyage list

Show a list of all database migrations.

### voyage backup

Creates a backup of database. This command doesn't take any parameters and will file if Voyage hasn't been initialized.

### voyage backup list

Shows a list of all backups.

### voyage restore

Restores a backup. If no parameter has been provided it will restore from the most recent backup.

```
Parameter Description
```
```
-i or --id="..." Name of the backup to restore from. Run "voyage backup list" to
view list of taken backups.
```
### voyage reset

Reset database state and re-apply all migrations. This command is useful when you switch between versions (for example you need to
switch to an older version of your project where some of already applied migrations in the current version won't be available yet). In this case
voyage will display an error when you run 'voyage list' and 'voyage status' commands. To fix that, you should run this command to reset
database state.


