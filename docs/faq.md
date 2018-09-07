# Frequently Asked Questions
**Do I need PHP installed to run Voyage?**
Yes, Voyage is written in PHP and it's required to run Voyage on your system.

**What is 'voyage' file?**
The file you download from http://voyage.hirebrains.co/latest/voyage is a PHAR archive with PHP source code, if you would like to view source code of voyage you can rename voyage to voyage.phar and extact it.

**Which Relation Management Databases are supported?**
Voyage currently supports MySQL only. It can sync everything except for changes in stored procedures and functions.

**How do I upgrade Voyage?**
Voyage has a command to retrieve the latest version from server. Just run `voyage selfupdate` via command-line and it will automatically upgrade itself (if a new version is available). Make sure to run this command via sudo (so that voyage has sufficient permissions to write to itself).
