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

