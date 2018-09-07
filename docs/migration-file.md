## Migration File

Migration files are located in `.voyage/migrations/` directory. Filename has the following syntax:

```
DATETIME-ENVIRONMENT.mgr
```

Where:

DATETIME has format of YYYYMMDD-HHIISS (24-hours format in UTC+0 timezone)
ENVIRONMENT is a name of environment where migration has been created

**For example:**

```
20170929-132801-development.mgr
20170929-174334-live.mgr
```

Each migration file (*.mgr) has the following format:

```
# @APPLY
```

SQL Queries to apply migration.

```
# @ROLLBACK
```

SQL Queries to rollback migration.