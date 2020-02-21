#!/bin/sh
set -ex
apt update -y
DEBIAN_FRONTEND=noninteractive apt install -y php-cli zip unzip
hhvm --version
php --version

(
  cd $(mktemp -d)
  curl https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
)

runtime=$(hhvm --php -r "echo HHVM_VERSION_ID >= 40000 ? 'php' : 'hhvm';")
if [ "$runtime" = "hhvm" ]; then
  removetestfiles=$(hhvm --php -r "echo HHVM_VERSION_ID < 32800 ? 'yes' : 'no';")
  if [ "$removetestfiles" = "yes" ]; then
    # The tests won't typecheck and work, so I remove them.
    rm -r tests
    # hhvm-autoload needs to have a directory here, but it can be empty.
    mkdir tests
    # We can't install hacktest
    # rm composer.json (next commit)
    # mv composer.old.json composer.json (next commit)
  fi
  hhvm /usr/local/bin/composer install
else
    # Implicitly uses php
    composer install
fi

hh_client

runstests=$(hhvm --php -r "echo HHVM_VERSION_ID >= 32800 ? 'canruntests' : 'cannotruntests';")
if [ "$runstests" = "canruntests" ]; then
  vendor/bin/hacktest tests/
fi
