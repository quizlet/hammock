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

# Prevent GitHub rate-limiting.
composer config -g github-oauth.github.com "$GITHUB_TOKEN"

canruntests=$(hhvm --php -r "echo HHVM_VERSION_ID >= 32800 ? 'yes' : 'no';")
if [ "$canruntests" = "yes" ]; then
  rm composer.json
  mv composer.dev.json composer.json
else
  # The tests won't typecheck and work, so we remove them.
  rm -r tests
  # hhvm-autoload needs to have a directory here, but it can be empty.
  mkdir tests
fi

runtime=$(hhvm --php -r "echo HHVM_VERSION_ID >= 40000 ? 'php' : 'hhvm';")
if [ "$runtime" = "hhvm" ]; then
  hhvm /usr/local/bin/composer install
else
  # Implicitly uses php
  composer install
fi

hh_client

if [ "$canruntests" = "yes" ]; then
  vendor/bin/hacktest tests/
fi
