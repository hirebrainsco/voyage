## Usage 

### New Project
* Developer creates a new project, installs CMS (if applicable)
* Developer runs `voyage init` to initialize voyage, sets tables that should be ignored, adds replacement variables (if needed)
* Runs `voyage make` to create the first migration
* During development, developer periodically continue runs `voyage make` command to record new changes in database and commits migration files to version control

**When project is ready to be launched or migrated to live server:**
* Developer clones project from git repository on the server
* If needed, installs voyage on the server
* Configures a new voyage environment
* Runs `voyage apply` to import all the database changes

### Existing Project

This scenario is for an existing project that is going to use Voyage tool:

* Developer runs `voyage init` on live server, configures list of tables that should be ignored, adds replacement variables (if needed).
* Developer runs `voyage make` to create the first migration.
* Please note that Ignored tables won't will be recorded to migrations so this is recommended to dump database fully and import it on development workstation.
* Changes pushed to git.
* Developer clones project from git on development workstation.
* Developer creates a new voyage environment file (i.e. creates a new environment file with database credentials and URLs / paths replacements). It's a good idea just to copy environment file from production server then change credentials, replacement variables, etc.
* Run `voyage apply`. Since you have imported full database dump and this is an initial import of database migrations, voyage will warn that some database tables must to be recreated (that's normal, voyage will need to recreate them from migration files to make sure you don't have changes in database).
* After this, developer starts working on the project and periodically runs `voyage make` command to create new database migrations.

* When changes are ready to be migrated to live server:
    * Developer runs `voyage status` to make sure there are no not committed changes in database. If changes exist:
        * Runs "voyage make" to create a new migration
        * Push migration to git
        * Pull it from git on local instance
        * Run "voyage apply" to apply changes from production server
        * Check changes
    * Push changes to git
    * On live server:
        * If needed run "voyage backup" to create a snapshot of data
        * Run "voyage apply" to apply changes from git
