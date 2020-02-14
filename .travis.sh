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

runtime=$(hhvm --php -r "echo HHVM_VERSION[0] === '4' ? 'hhvm' : 'php';")
if [ "$runtime" = "hhvm" ]; then
  hhvm /usr/local/bin/composer install
else
	php /usr/local/bin/composer install
fi

hh_client

vendor/bin/hacktest tests/

vendor/bin/hhast-lint