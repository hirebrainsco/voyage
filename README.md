# voyage
Voyage is a command-line tool to create, apply (and rollback) database migrations automatically. This tool has been created as a part of implementation of continuous integration workflow for PHP projects and keep changes in database under version control. 

# Get Started
Voyage is a command-line tool which allows to create database migrations, which is especially usable for various content management systems (such as, WordPress, Drupal, etc) where most of information stores in database. Voyage allows custom replace variables, which is very useful if your app or a CMS stores URLs or paths in database and you need to switch between various environments (i.e. host in your local environment is "http://localhost/" and your staging URL is "https://staging.example.com"), Voyage will replace that URL with a placeholder before creating migration and will replace placeholder with the URL in your configuration file before applying migration (this also works for serialized data).

>**Performance Notice**: Voyage performs the best with PHP 7.0 or higher (it's also compatible to PHP 5.5+ but since it does a lot of checks and verifications you should be aware that it may run slow if your database has a lot of records, so make sure to ignore tables which shouldn't be versioned, such as: comments, order data, debug info, logs, etc.) 

## Installation
### Linux & Mac OS

