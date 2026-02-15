#!/bin/sh
set -e

# Génère les clés JWT dans /app/config/jwt si absentes (volume persistant entre déploiements).
JWT_DIR="${JWT_DIR:-/app/config/jwt}"
PRIVATE_PEM="${JWT_DIR}/private.pem"
PUBLIC_PEM="${JWT_DIR}/public.pem"
PASSPHRASE="${JWT_PASSPHRASE:-}"

if [ ! -f "$PRIVATE_PEM" ]; then
  echo "JWT keys not found in ${JWT_DIR}; generating..."
  mkdir -p "$JWT_DIR"
  if [ -n "$PASSPHRASE" ]; then
    openssl genpkey -out "$PRIVATE_PEM" -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass "pass:${PASSPHRASE}"
    openssl pkey -in "$PRIVATE_PEM" -out "$PUBLIC_PEM" -pubout -passin "pass:${PASSPHRASE}"
  else
    openssl genpkey -out "$PRIVATE_PEM" -algorithm rsa -pkeyopt rsa_keygen_bits:4096
    openssl pkey -in "$PRIVATE_PEM" -out "$PUBLIC_PEM" -pubout
  fi
  chmod 644 "$PUBLIC_PEM"
  chmod 600 "$PRIVATE_PEM"
  echo "JWT keys generated."
fi

exec "$@"
