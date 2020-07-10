#!/usr/bin/env bash

if [ $# -gt 0 ]; then
  PHP_VER="${1}"
else
  if [[ -z "${TRAVIS_PHP_VERSION}" ]]; then
    PHP_VER="7.4"
  else
    PHP_VER="${TRAVIS_PHP_VERSION:0:3}"
  fi
fi

echo "${PHP_VER}"

if [ "${PHP_VER}" == "7.0" ] || [ "${PHP_VER}" == "5.6" ] || [ "${PHP_VER}" == "5.5" ] || [ "${PHP_VER}" == "5.4" ]; then
  while IFS= read -r -d '' file; do
    sed -i -e 's/public const/const/g' "$file"
    sed -i -e 's/): void/)/g' "$file"
  done < <(find "$(pwd)/src" "$(pwd)/tests" -type f -name '*.php' -print0)
fi

if [ "${PHP_VER}" == "5.6" ] || [ "${PHP_VER}" == "5.5" ] || [ "${PHP_VER}" == "5.4" ]; then
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
    sed -i -e 's/): BlockHeaderInterface/)/g' "$file"
    sed -i -e 's/): BlockInterface/)/g' "$file"

    # type hints
    sed -i -e 's/int \$/\$/g' "$file"
    sed -i -e 's/string \$/\$/g' "$file"
    sed -i -e 's/bool \$/\$/g' "$file"
    sed -i -e 's/string \&\$/\&\$/g' "$file"
  done < <(find "$(pwd)/src" "$(pwd)/tests" -type f -name '*.php' -print0)
fi

if [ "${PHP_VER}" == "7.0" ]; then
  sed -i -e 's/"php-64bit": ">=7.1"/"php-64bit": ">=7.0"/' "$(pwd)/composer.json"
fi

if [ "${PHP_VER}" == "5.6" ]; then
  sed -i -e 's/"php-64bit": ">=7.1"/"php-64bit": ">=5.6"/' "$(pwd)/composer.json"
fi

if [ "${PHP_VER}" == "5.5" ]; then
  sed -i -e 's/"php-64bit": ">=7.1"/"php-64bit": ">=5.5"/' "$(pwd)/composer.json"
fi

if [ "${PHP_VER}" == "5.4" ]; then
  sed -i -e 's/"php-64bit": ">=7.1"/"php-64bit": ">=5.4"/' "$(pwd)/composer.json"
  mkdir ~/.composer
  echo '{"config":{"disable-tls":true,"secure-http":false}}' >~/.composer/config.json
fi
