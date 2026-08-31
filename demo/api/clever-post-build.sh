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

# Les photos d'Alice et celle de Bob, chez CloudPics. La base est recréée à chaque
# déploiement : le disque d'une instance Clever n'est pas persistant, et c'est très
# bien pour une démo qui doit repartir propre.
#
# La colonne owner porte le « sub » du compte, tel que le realm l'épingle : c'est ce
# que PhotoOwnerExtension compare pour cloisonner. Elle est NOT NULL, donc un INSERT
# qui l'oublie fait échouer le déploiement entier (set -e). Ces trois lignes sont les
# mêmes que celles de la tâche « castor db:reset ».
ALICE=11111111-1111-4111-8111-111111111111
BOB=22222222-2222-4222-8222-222222222222

php bin/console dbal:run-sql "INSERT INTO photo (title, url, owner) VALUES ('Coucher de soleil sur le Golden Gate', 'https://cloudpics.example/alice/golden-gate.jpg', '$ALICE')"
php bin/console dbal:run-sql "INSERT INTO photo (title, url, owner) VALUES ('Alice au sommet du Mont Tamalpais', 'https://cloudpics.example/alice/tamalpais.jpg', '$ALICE')"
php bin/console dbal:run-sql "INSERT INTO photo (title, url, owner) VALUES ('Bob en lecture seule', 'https://cloudpics.example/bob/read-only.jpg', '$BOB')"
