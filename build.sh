#!/usr/bin/env bash

if [ "$1" == "php7.3" ] || [ "$1" == "php7.2" ] || [ "$1" == "php7.1" ] || [ "$1" == "php7.0" ] || [ "$1" == "php5.6" ] || [ "$1" == "php5.5" ] || [ "$1" == "php5.4" ] || [ "$1" == "php5.3" ]; then
  while IFS= read -r -d '' file; do
    sed -i -e 's/private string \$/private \$/g' "$file"
    sed -i -e 's/private array \$/private \$/g' "$file"
#    sed -i -e 's/private static array \$/private static \$/g' "$file"
#    sed -i -e 's/private static string \$/private static \$/g' "$file"
  done < <(find "$(pwd)/src" "$(pwd)/tests" -type f -name '*.php' -print0)
fi

if [ "$1" == "php7.0" ] || [ "$1" == "php5.6" ] || [ "$1" == "php5.5" ] || [ "$1" == "php5.4" ] || [ "$1" == "php5.3" ]; then
  while IFS= read -r -d '' file; do
    sed -i -e 's/public const/const/g' "$file"
    sed -i -e 's/private const/const/g' "$file"
    sed -i -e 's/): void/)/g' "$file"
  done < <(find "$(pwd)/src" "$(pwd)/tests" -type f -name '*.php' -print0)
fi

if [ "$1" == "php5.6" ] || [ "$1" == "php5.5" ] || [ "$1" == "php5.4" ] || [ "$1" == "php5.3" ]; then
  while IFS= read -r -d '' file; do
    # strict
    sed -i -e '/^declare(strict_types=1)/d' "$file"
    # return types
    sed -i -e 's/): int/)/g' "$file"
    sed -i -e 's/): bool/)/g' "$file"
    sed -i -e 's/): string/)/g' "$file"
    sed -i -e 's/): array/)/g' "$file"
    sed -i -e 's/): AddressInterface/)/g' "$file"
    sed -i -e 's/): PublicKeyInterface/)/g' "$file"
    sed -i -e 's/): SecretKeyInterface/)/g' "$file"
    sed -i -e 's/): TransactionInterface/)/g' "$file"
    # type hints
    sed -i -e 's/int \$/\$/g' "$file"
    sed -i -e 's/string \$/\$/g' "$file"
    sed -i -e 's/bool \$/\$/g' "$file"
    sed -i -e 's/string \&\$/\&\$/g' "$file"
  done < <(find "$(pwd)/src" "$(pwd)/tests" -type f -name '*.php' -print0)
fi

if [ "$1" == "php7.3" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=7.3"/' "$(pwd)/composer.json"
fi

if [ "$1" == "php7.2" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=7.2"/' "$(pwd)/composer.json"
fi

if [ "$1" == "php7.1" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=7.1"/' "$(pwd)/composer.json"
fi

if [ "$1" == "php7.0" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=7.0"/' "$(pwd)/composer.json"
fi

if [ "$1" == "php5.6" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=5.6"/' "$(pwd)/composer.json"
fi

if [ "$1" == "php5.5" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=5.5"/' "$(pwd)/composer.json"
fi

if [ "$1" == "php5.4" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=5.4"/' "$(pwd)/composer.json"
fi

if [ "$1" == "php5.4" ] || [ "$1" == "php5.3" ]; then
  mkdir ~/.composer
  echo '{"config":{"disable-tls":true,"secure-http":false}}' >~/.composer/config.json
fi

if [ "$1" == "php5.3" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=5.3"/' "$(pwd)/composer.json"

  while IFS= read -r -d '' file; do
    # array
    sed -i -E 's/ \[\]/ array\(\)/' "$file"
    sed -i -E 's/ \[0\]/ array\(0\)/' "$file"
    sed -i -E 's/ \[0, / array\(0, /' "$file"
    sed -i -E 's/, 0\]/, 0\)/' "$file"
    sed -i -e 's/ \[$/ array\(/' "$file"
    sed -i -E 's/ \]/ \)/' "$file"
    sed -i -e 's/ \[\$hrp/ array\(\$hrp/g' "$file"
    sed -i -E 's/6\)\];/6\)\);/g' "$file"
  done < <(find "$(pwd)/src" "$(pwd)/tests" -type f -name '*.php' -print0)
fi

php composer.phar install
php composer.phar check-platform-reqs
#php composer.phar dump-autoload
php phpunit.phar
