| Command            | Description                                                                                                                                       |
| -------------------|---------------------------------------------------------------------------------------------------------------------------------------------------|
| `voyage init`      | Initialize voyage and create the first database's snapshot.                                                                                       |
| `voyage status`    | Check current status (displays current migration and list of migrations that haven't been applied yet).                                           |
| `voyage make`      | Detect difference between current database state and latest migration. If new changes exist then create a new migration file with the difference. |
| `voyage rollback`  | Rollback to previous migration.                                                                                                                   |
| `voyage apply`     | Apply all not applied migrations to the current database.                                                                                         |
| `voyage list`      | Print a list of all migrations (applied and not applied).                                                                                         |
| `voyage backup`    | Create database backup (except for data and records defined in ignore file). See [Ignore List](configuration.md#ignore-list)                      |
| `voyage restore`   | Restore table and data from backup.                                                                                                               |
| `voyage reset`     | Reset database state and re-apply all migrations.                                                                                                 |

## voyage init

This command initializes Voyage tool and creates the first full dump of the current database. This command will create dump with structure of all database tables even if they are in ignore list (records of tables from ignore list will be skipped). This command is interactive and will ask you for a list of custom variables. It accepts the following parameters:

#### -f or --force
By default, voyage won't continue execution if current working directory already contains the `.voyage` directory. The force option is used to skip this check, continue execution and overwrite existing .voyage configuration even if it exists. Please note that it will also clean and remove files of existing migrations.

#### -c or --config="..."
Using this parameter you can specify a CMS to allow voyage detect database settings automatically. For example: --config=magento2.

Available values are:

* auto _(default)_
* none _(no verification for CMS configuration files will be performed, Voyage will prompt for database access credentials)_
* wordpress
* magento2
* magento1

This is important to use the `--config` parameter if you develop a website based on one of the supported CMS (currently WordPress, Magento 1 or Magento 2), in this case voyage will automatically generate ignore file which will contain a list of tables that should be excluded for specific CMS including popular plugins & extensions.

#### --host="..."
Database host and port _(default: localhost:3306, if you skip this parameter voyage will prompt for it during initialization)_

#### -u or --user="..."
Database username _(optional, if you skip this parameter voyage will prompt for it during initialization)_

#### -d or --db="..."
Database name _(optional, if you skip this parameter voyage will prompt for it during initialization)_

#### -p or --pass="..."
Database password _(optional, if you skip this parameter voyage will prompt for it during initialization)_

#### --env="..."
Optional name of the current environment (for example: "production", "local", "dev", "staging", etc).

**Example:**

```bash
# Initialize voyage. The tool will prompt for all needed information.
voyage init
 
# Initialize and overwrite existing config and data if it exists.
voyage init -f
 
# Initialize Voyage for WordPress
voyage init --config=wordpress
# OR
voyage init -cwordpress
 
# Initialize voyage with given database access info
voyage init -uroot -pdbaccess -ddbname
 
# Initialize voyage with given db access info, custom port and initialize a new environment with name "local"
voyage init --host=localhost:3307 --user=root --pass=dbaccess --db=dbname --env=local
```

## voyage status

This command prints name of the current database migration and lists not applied migrations. The command will fail if voyage hasn't been initialized.

## voyage make

In this mode voyage detects difference in database and creates a new migration (if database has changes). The tool will prompt for migration's name, you can pass the name as the parameter:

#### --name="..."
Name of the migration. If you skip this parameter then voyage will prompt for it.

## voyage rollback

Rollback applied changes. If no parameters passed then voyage will rollback one latest applied migration, however, you can specify the ID of migration to rollback to.

#### -m or --migration="..."
ID / name of migration to rollback to. Migration ID's can be retrieved using `voyage list` command.

## voyage apply

Apply all not applied migrations to the current database. This command doesn't take any parameters and will fail if Voyage hasn't been initialized.

## voyage list

Display list of all migrations including applied and not applied ones.

## voyage backup

Create database backup. This command doesn't take any parameters and will file if Voyage hasn't been initialized.

## voyage backup list

Print a list of all available backups.

## voyage restore

Restore database from backup. If you run this command without any parameters then voyage will restore database from the most recent backup.

#### -i or --id="..."
Name of the backup to restore from. Run `voyage backup list` to view list of all available backups.

## voyage reset
Reset database state and re-apply all migrations. This command is useful when you switch between versions (for example you need to switch to an older version of your project where some of already applied migrations in the current version won't be available yet). In this case voyage will display an error when you run 'voyage list' and 'voyage status' commands. To fix that, you should run this command to reset database state.
