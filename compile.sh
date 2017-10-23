#!/bin/bash

echo "Compiling to PHAR..."
rm -f ./bin/voyage
php box.phar build

echo "Renaming and setting executable permissions."
mv ./voyage.phar ./bin/voyage
chmod +x ./bin/voyage

echo "Done."

