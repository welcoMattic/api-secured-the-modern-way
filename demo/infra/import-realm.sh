#!/bin/sh
# Dépose le realm et le thème de login dans le FS Bucket de l'add-on Keycloak,
# puis relance l'instance pour déclencher l'import.
#
# Cette étape reste hors d'OpenTofu : le provider expose l'id du bucket
# (output cloudpics_id_fsbucket_id) mais pas ses identifiants FTP, et il n'existe
# aucune ressource pour y téléverser un fichier. À lancer une fois, après le
# premier `tofu apply`.
#
# Les identifiants FTP se lisent dans la Console, sur l'add-on Keycloak, onglet
# du FS Bucket associé.
#
#   FTP_HOST=... FTP_USER=... FTP_PASS=... KEYCLOAK_APP_ID=app_xxx ./import-realm.sh
set -eu

: "${FTP_HOST:?FTP_HOST manquant}"
: "${FTP_USER:?FTP_USER manquant}"
: "${FTP_PASS:?FTP_PASS manquant}"
: "${KEYCLOAK_APP_ID:?KEYCLOAK_APP_ID manquant, l app java de l add-on}"

cd "$(dirname "$0")/.."

echo "Dépôt du realm photos"
curl --ftp-create-dirs -T keycloak/import/photos-realm.json \
  "ftp://$FTP_HOST/realms/import/photos-realm.json" --user "$FTP_USER:$FTP_PASS"

echo "Dépôt du thème de login"
curl --ftp-create-dirs -T keycloak/themes/photos/login/theme.properties \
  "ftp://$FTP_HOST/themes/photos/login/theme.properties" --user "$FTP_USER:$FTP_PASS"

echo "Redémarrage sans cache pour déclencher l'import"
clever restart --app "$KEYCLOAK_APP_ID" --without-cache
