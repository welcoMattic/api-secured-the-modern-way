#!/bin/sh
# Hook de post-build Clever Cloud pour CloudPics API.
#
# Il vit dans un script versionné, et pas dans la variable CC_POST_BUILD_HOOK,
# pour deux raisons : le hook s'exécute depuis la racine du dépôt et doit donc
# se replacer lui-même dans demo/api, et le SQL de seed devient illisible dès
# qu'on l'échappe dans une variable d'environnement.
set -e
cd "$(dirname "$0")"

php bin/console doctrine:schema:create --no-interaction

# Les photos d'Alice, chez CloudPics. La base est recréée à chaque déploiement :
# le disque d'une instance Clever n'est pas persistant, et c'est très bien pour
# une démo qui doit repartir propre.
php bin/console dbal:run-sql "INSERT INTO photo (title, url) VALUES ('Coucher de soleil sur le Golden Gate', 'https://cloudpics.example/alice/golden-gate.jpg')"
php bin/console dbal:run-sql "INSERT INTO photo (title, url) VALUES ('Alice au sommet du Mont Tamalpais', 'https://cloudpics.example/alice/tamalpais.jpg')"
