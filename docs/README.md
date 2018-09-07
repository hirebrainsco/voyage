# Introduction

Voyage is a command-line tool which allows to track changes in database automatically, view them, save changes to files (for version control), apply to multiple environments, rollback, backup and restore database, etc.

Among many other features, Voyage provides custom replacement variables, in case app or a CMS stores URLs or paths in database and you need to switch between multiple environments (for example your project in your local environment has URL "http://localhost/" and your staging URL is "https://staging.example.com"), Voyage will replace that URL with a placeholder before creating migration and will replace placeholder with the URL from your configuration file before applying migration. Replacement also works with serialized data stored in database (for example data stored by WordPress widgets, plugins, etc).

* [Installation Guide](install.md)
* [Configuration](configuration.md)

If you have any question or need assitance in configuring CI workflow with Voyage please feel free to contact us at [hello@hirebrains.co](mailto:hello@hirebrains.co).
