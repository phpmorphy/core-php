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
echo "$(pwd)"

if [ "${PHP_VER}" == "7.3" ] || [ "${PHP_VER}" == "7.2" ] || [ "${PHP_VER}" == "7.1" ] || [ "${PHP_VER}" == "7.0" ] || [ "${PHP_VER}" == "5.6" ] || [ "${PHP_VER}" == "5.5" ] || [ "${PHP_VER}" == "5.4" ] || [ "${PHP_VER}" == "5.3" ]; then
  while IFS= read -r -d '' file; do
    echo "$file"
    sed -i -e 's/private string \$/private \$/g' "$file"
    sed -i -e 's/private array \$/private \$/g' "$file"
  done < <(find "$(pwd)/src" "$(pwd)/tests" -type f -name '*.php' -print0)
fi

if [ "${PHP_VER}" == "7.0" ] || [ "${PHP_VER}" == "5.6" ] || [ "${PHP_VER}" == "5.5" ] || [ "${PHP_VER}" == "5.4" ] || [ "${PHP_VER}" == "5.3" ]; then
  while IFS= read -r -d '' file; do
    echo "$file"
    sed -i -e 's/public const/const/g' "$file"
    sed -i -e 's/private const/const/g' "$file"
    sed -i -e 's/): void/)/g' "$file"
  done < <(find "$(pwd)/src" "$(pwd)/tests" -type f -name '*.php' -print0)
fi

if [ "${PHP_VER}" == "5.6" ] || [ "${PHP_VER}" == "5.5" ] || [ "${PHP_VER}" == "5.4" ] || [ "${PHP_VER}" == "5.3" ]; then
  while IFS= read -r -d '' file; do
    echo "$file"
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

if [ "${PHP_VER}" == "7.3" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=7.3"/' "$(pwd)/composer.json"
fi

if [ "${PHP_VER}" == "7.2" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=7.2"/' "$(pwd)/composer.json"
fi

if [ "${PHP_VER}" == "7.1" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=7.1"/' "$(pwd)/composer.json"
fi

if [ "${PHP_VER}" == "7.0" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=7.0"/' "$(pwd)/composer.json"
fi

if [ "${PHP_VER}" == "5.6" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=5.6"/' "$(pwd)/composer.json"
fi

if [ "${PHP_VER}" == "5.5" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=5.5"/' "$(pwd)/composer.json"
fi

if [ "${PHP_VER}" == "5.4" ]; then
  sed -i -e 's/"php-64bit": ">=7.4"/"php-64bit": ">=5.4"/' "$(pwd)/composer.json"
fi

if [ "${PHP_VER}" == "5.4" ] || [ "${PHP_VER}" == "5.3" ]; then
  mkdir ~/.composer
  echo '{"config":{"disable-tls":true,"secure-http":false}}' >~/.composer/config.json
fi

if [ "${PHP_VER}" == "5.3" ]; then
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
