# Installation Guide

>**Performance Notice**: Voyage performs the best with PHP 7.0 or higher (it's also compatible with PHP 5.5+ but since it does a lot of checks and verifications you should be aware that it may run slow if your database has a lot of records, so make sure to ignore tables which shouldn't be versioned, such as: comments, order data, debug info, logs, etc.) 

### Linux & Mac OS
One liner:
```bash
sudo -- sh -c 'curl -o /tmp/voyage https://voyage.hirebrains.co/latest/voyage && mv /tmp/voyage /usr/local/bin/ && chmod +x /usr/local/bin/voyage'
```

### Microsoft Windows
Please follow these steps to install Voyage under MS Windows:
1. Install [Cygwin](https://www.cygwin.com/)
2. Download voyage PHAR binary from https://voyage.hirebrains.co/latest/voyage
3. Create a directory in "Program Files\voyage\" and move (or copy) the downloaded file there
4. Add path to voyage to your %PATH% variable

### Build From Source Code
1. Clone voyage repository 
2. Run `composer install`
3. Run `bash ./compile.sh`
4. Voyage binary will be available in `<VOYAGE_DIR>/bin/` directory
