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
| ❌ | L'import du realm. Le provider expose l'id du FS Bucket mais pas ses identifiants FTP, et aucune ressource ne permet d'y déposer un fichier. C'est `./import-realm.sh`, une fois, après le premier apply. |

Donc : l'infra en un `apply`, le realm en un script.

## Reprendre la démo publique au lieu d'en créer une autre

Les trois domaines par défaut (`cloudpics-api`, `photobook-demo`, `photoprint-demo`.cleverapps.io) sont déjà pris par la démo en ligne, et le realm les fige dans ses `redirectUris`. Un apply neuf dans une autre organisation échouera dessus.

Deux chemins :

**Adopter l'existant.** Les trois `app_id` sont dans le `.clever.json` à la racine du dépôt :

```bash
tofu import clevercloud_php.cloudpics_api    app_953687a1-de02-426a-9abc-383bd6b67c20
tofu import clevercloud_php.photobook        app_92b62b7a-af0c-4c10-9852-2e100f87ed55
tofu import clevercloud_static.photoprint    app_0d834ea6-3ae9-4a5d-ae77-5279d1757571
```

Un `import` ne remplit pas le bloc `deployment` : le provider ne peut pas deviner si on veut qu'il gère les déploiements. Le premier `apply` après l'import déclenche donc un déploiement. C'est voulu ici, mais il faut le savoir.

**Déployer ailleurs.** Changer les trois `*_vhost` dans `terraform.tfvars`, et reporter les nouveaux domaines dans les `redirectUris`, `webOrigins` et `post.logout.redirect.uris` de `keycloak/import/photos-realm.json`. Sans ça, le login casse sur « Invalid parameter: redirect_uri ».

## Coût

Les trois apps et l'add-on facturent en continu. Entre deux répétitions :

```bash
clever stop --alias api && clever stop --alias book && clever stop --alias print
```

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
