#!/bin/bash

echo "Compiling to PHAR..."
php -f ./compile.php

echo "Renaming and setting executable permissions."
mv ./bin/voyage.phar ./bin/voyage
chmod +x ./bin/voyage

echo "Done."

