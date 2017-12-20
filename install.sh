#!/bin/sh

INSTALL_PATH="/usr/local/bin/"
DOWNLOAD_URL="http://voyage.hirebrains.co/latest/voyage"

if (( $EUID != 0 )); then
  echo "You need root privileges to install voyage to ${INSTALL_PATH}. Please re-run this script via sudo."
  exit 1;
fi

echo "Downloading voyage from: ${DOWNLOAD_URL}"
curl -O ${DOWNLOAD_URL}
chmod +x ./voyage
mv ./voyage $INSTALL_PATH
echo "Installed voyage to ${INSTALL_PATH}."