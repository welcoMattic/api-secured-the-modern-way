# Infra de la démo « API Secured, the Modern Way » sur Clever Cloud.
#
# Un apply crée les quatre acteurs du deck : l'OIDC Provider, le resource server
# et les deux clients. Les apps référencent l'add-on par son attribut `host`,
# ce qui suffit à ordonner la création : Keycloak d'abord, les apps ensuite.
#
# Ce que ce module ne fait PAS : importer le realm. Voir import-realm.sh.

# 🛂 CloudPics ID : l'OIDC Provider. Il émet les tokens, personne d'autre.
resource "clevercloud_keycloak" "cloudpics_id" {
  name    = "cloudpics-id"
  region  = var.region
  version = var.keycloak_version
}

locals {
  # `host` est documenté comme « URL to access Keycloak » : selon la version du
  # provider il arrive avec ou sans schéma. On normalise pour ne jamais fabriquer
  # un « https://https://... » dans les OIDC_ISSUER des apps.
  keycloak_host = replace(replace(clevercloud_keycloak.cloudpics_id.host, "https://", ""), "http://", "")
  keycloak_url  = "https://${local.keycloak_host}"
  realm_url     = "${local.keycloak_url}/realms/photos"

  # Derrière le proxy inverse de Clever, Symfony se croit en http et PhotoBook
  # fabriquerait un redirect_uri en http que CloudPics ID refuserait.
  trusted_proxies = "REMOTE_ADDR"

  # Aucun vhost n'est pinné : Clever attribue à chaque app son propre
  # app-<uuid>.cleverapps.io dès la création. Vérifié sur les trois apps de la
  # démo en ligne avec `clever domain`, le domaine par défaut reprend l'id de
  # l'app avec un tiret à la place du underscore.
  api_url        = "https://${replace(clevercloud_php.cloudpics_api.id, "app_", "app-")}.cleverapps.io"
  photobook_url  = "https://${replace(clevercloud_php.photobook.id, "app_", "app-")}.cleverapps.io"
  photoprint_url = "https://${replace(clevercloud_static.photoprint.id, "app_", "app-")}.cleverapps.io"
}

# ☁️ CloudPics API : le resource server. Il vérifie les tokens, il n'en émet aucun.
resource "clevercloud_php" "cloudpics_api" {
  name               = "cloudpics-api"
  description        = "CloudPics API, resource server de la démo"
  region             = var.region
  php_version        = var.php_version
  smallest_flavor    = "nano"
  biggest_flavor     = "nano"
  min_instance_count = 1
  max_instance_count = 1

  # Par défaut le build tourne sur l'instance elle-même, et un composer install
  # d'API Platform ne tient pas dans les 512 Mo d'une nano.
  build_flavor = "M"
  app_folder   = "demo/api"
  webroot      = "/public"

  redirect_https = true

  # Le hook s'exécute depuis la racine du dépôt, pas depuis app_folder : le
  # script se replace lui-même dans demo/api. Un `php bin/console` nu échouerait
  # sur « Could not open input file ».
  hooks {
    post_build = "./demo/api/clever-post-build.sh"
  }

  environment = {
    APP_ENV                 = "prod"
    APP_DEBUG               = "0"
    APP_SECRET              = var.api_app_secret
    CC_COMPOSER_VERSION     = "2"
    DATABASE_URL            = "sqlite:///%kernel.project_dir%/var/data.db"
    CORS_ALLOW_ORIGIN       = var.cors_allow_origin
    TRUSTED_PROXIES         = local.trusted_proxies
    OIDC_ISSUER             = local.realm_url
    OIDC_DISCOVERY_BASE_URI = "${local.realm_url}/"
    OIDC_AUDIENCE           = "cloudpics-api"
  }

  deployment {
    repository = var.repository
  }
}

# 📕 PhotoBook : client confidentiel. Il tourne sur son serveur, il peut garder
# un secret, donc pas de PKCE obligatoire.
resource "clevercloud_php" "photobook" {
  name               = "photobook"
  description        = "PhotoBook, client OIDC confidentiel de la démo"
  region             = var.region
  php_version        = var.php_version
  smallest_flavor    = "nano"
  biggest_flavor     = "nano"
  min_instance_count = 1
  max_instance_count = 1

  build_flavor = "M"
  app_folder   = "demo/client-symfony"
  webroot      = "/public"

  redirect_https = true

  environment = {
    APP_ENV             = "prod"
    APP_DEBUG           = "0"
    APP_SECRET          = var.photobook_app_secret
    CC_COMPOSER_VERSION = "2"
    TRUSTED_PROXIES     = local.trusted_proxies
    API_BASE_URL        = local.api_url
    OIDC_CLIENT_ID      = "photobook"
    OIDC_CLIENT_SECRET  = var.photobook_client_secret
    OIDC_WELL_KNOWN_URL = "${local.realm_url}/.well-known/openid-configuration"
  }

  deployment {
    repository = var.repository
  }
}

# 🌁 PhotoPrint : client public. Il tourne chez Alice, il ne peut rien garder,
# donc PKCE. Vite reçoit ses VITE_* comme variables d'environnement Clever, et
# leur donne priorité sur les fichiers .env du dépôt.
#
# Pas d'app_folder ici : sur le runtime static, combiné à un webroot profond, il
# fait échouer la phase de run. On construit depuis la racine.
resource "clevercloud_static" "photoprint" {
  name               = "photoprint"
  description        = "PhotoPrint, client OIDC public de la démo"
  region             = var.region
  smallest_flavor    = "pico"
  biggest_flavor     = "pico"
  min_instance_count = 1
  max_instance_count = 1

  build_flavor = "M"

  redirect_https = true

  environment = {
    CC_BUILD_COMMAND    = var.spa_build_command
    CC_WEBROOT          = "/demo/client-spa/dist"
    VITE_API_BASE_URL   = local.api_url
    VITE_OIDC_AUTHORITY = local.realm_url
    VITE_OIDC_CLIENT_ID = "photoprint"
  }

  deployment {
    repository = var.repository
  }
}
