# Déployer la démo sur Clever Cloud avec OpenTofu

Le module crée les quatre acteurs du deck : l'OIDC Provider, le resource server et les deux clients.

```bash
cd demo/infra
cp terraform.tfvars.example terraform.tfvars   # puis remplir les deux APP_SECRET

export CC_ORGANISATION=orga_xxx                # ou user_xxx pour un espace perso
export CC_OAUTH_TOKEN=...                      # les jetons de `clever login`,
export CC_OAUTH_SECRET=...                     # dans ~/.config/clever-cloud/clever-tools.json

tofu init
tofu plan
tofu apply
```

`tofu output` donne les quatre URL. `tofu output -raw cloudpics_id_admin_password` donne le mot de passe temporaire de l'admin Keycloak.

## Ce que l'apply fait, et ce qu'il ne fait pas

| | |
|---|---|
| ✅ | L'add-on Keycloak, les trois apps, leurs flavors, leurs domaines, leurs variables d'environnement |
| ✅ | Le déploiement du code : le bloc `deployment` pousse le HEAD du dépôt public, donc les apps tournent à la fin de l'apply |
| ✅ | Le câblage entre les acteurs : les apps lisent l'URL de l'add-on via son attribut `host`, ce qui ordonne aussi la création |
| ✅ | Les domaines : aucun vhost n'est pinné, Clever attribue à chaque app son `app-<uuid>.cleverapps.io`. Rien à réserver, aucune collision possible, deux personnes peuvent déployer la démo en parallèle. |
| ❌ | L'import du realm. Le provider expose l'id du FS Bucket mais pas ses identifiants FTP, et aucune ressource ne permet d'y déposer un fichier. C'est `./import-realm.sh`, une fois, après le premier apply. |

Donc : l'infra en un `apply`, le realm en un script.

## Le realm suit les domaines générés

Comme les domaines ne sont plus choisis à l'avance, `keycloak/import/photos-realm.json` ne peut plus les contenir en dur. Ses clients `photoprint`, `photobook` et `cloudpics-docs` listent des `redirectUris` en `localhost` et les domaines de la démo publique : il faut y ajouter ceux que l'apply vient de créer, avant de lancer l'import.

```bash
tofu output          # les quatre URL
# ajouter photoprint_url, photobook_url et cloudpics_api_url dans les
# redirectUris, webOrigins et post.logout.redirect.uris du realm
./import-realm.sh
```

Sans ça, le login s'arrête sur « Invalid parameter: redirect_uri ». Le `localhost` reste dans le realm, donc `castor start` continue de fonctionner sans rien changer.

## CORS de l'API

`cors_allow_origin` vaut par défaut `^https://app-[0-9a-f-]+\.cleverapps\.io$`, soit tout domaine d'app généré par Clever, et pas la seule URL de PhotoPrint.

C'est un cycle qu'on casse, pas un oubli : PhotoPrint a besoin de l'URL de l'API, donc si l'API avait besoin de celle de PhotoPrint le graphe bouclerait et aucun apply ne serait possible. Pour resserrer une fois les domaines connus :

```bash
tofu output -raw photoprint_url   # puis coller la valeur exacte dans cors_allow_origin
tofu apply
```

## Coût

Les trois apps et l'add-on facturent en continu. Entre deux répétitions :

```bash
for app in $(tofu state list | grep -E 'clevercloud_(php|static)'); do
  clever stop --app "$(tofu state show -no-color "$app" | awk '/^ *id /{print $3}' | tr -d '\"')"
done
```

Les alias `--alias api|book|print` du `.clever.json` désignent la démo en ligne, pas les apps que l'apply vient de créer : il faut passer par les ids du state.

L'add-on Keycloak, lui, ne se met pas en pause. C'est le poste principal.

## Valeurs à confirmer

Les variables d'environnement du module sont dérivées des `.env` du dépôt et des contraintes documentées dans `../README.md`. Deux valeurs méritent une vérification contre l'existant avec `clever env --alias api|book|print` avant un apply sur l'infra live :

- `spa_build_command` : le runtime `static` de Clever documente `npm`, pas `bun`. Le module reste sur npm pour ne dépendre que de ce que l'image de build garantit, alors que le dépôt utilise bun en local.
- `trusted_proxies` : `REMOTE_ADDR` fait confiance au seul proxy en amont, ce qui est le cas derrière Clever.

## État et lock

`tofu validate` passe contre le provider `clevercloud` 2.1.0 : les types de ressources, les arguments requis et la forme des blocs sont vérifiés contre le schéma réel, pas seulement contre la syntaxe HCL. Aucun `apply` n'a été lancé depuis ce dépôt.

Le `.terraform.lock.hcl` est volontairement ignoré. Un lock généré sur une seule machine ne contient que les hashes de sa plateforme, et ferait échouer le `init` de quiconque n'est pas sur le même OS. Pour figer les versions à plusieurs, le régénérer multi-plateforme :

```bash
tofu providers lock -platform=darwin_arm64 -platform=linux_amd64 -platform=windows_amd64
```


Le state contient le mot de passe admin de Keycloak. Il est dans le `.gitignore` et n'a rien à faire dans un dépôt public. Pour travailler à plusieurs, un bucket [Cellar](https://www.clever.cloud/developers/doc/addons/cellar/) fait un backend S3.
