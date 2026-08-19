variable "region" {
  description = "Région Clever Cloud des trois apps et de l'add-on."
  type        = string
  default     = "par"
}

variable "repository" {
  description = "Dépôt git déployé sur les trois apps. Public, donc aucun credential à fournir."
  type        = string
  default     = "https://github.com/welcoMattic/api-secured-the-modern-way.git"
}

variable "keycloak_version" {
  description = "Version Keycloak de l'add-on managé."
  type        = string
  default     = "26.7.1"
}

# Le realm (keycloak/import/photos-realm.json) fige ces trois domaines dans les
# redirectUris et les webOrigins de ses clients. Les changer ici oblige à les
# changer dans le realm, sinon le login casse avec « Invalid parameter:
# redirect_uri ». Les valeurs par défaut sont celles de la démo publique.
variable "api_vhost" {
  description = "Domaine public de CloudPics API."
  type        = string
  default     = "cloudpics-api.cleverapps.io"
}

variable "photobook_vhost" {
  description = "Domaine public de PhotoBook (client confidentiel)."
  type        = string
  default     = "photobook-demo.cleverapps.io"
}

variable "photoprint_vhost" {
  description = "Domaine public de PhotoPrint (client public)."
  type        = string
  default     = "photoprint-demo.cleverapps.io"
}

variable "api_app_secret" {
  description = "APP_SECRET de CloudPics API. À générer : openssl rand -hex 16"
  type        = string
  sensitive   = true
}

variable "photobook_app_secret" {
  description = "APP_SECRET de PhotoBook. À générer : openssl rand -hex 16"
  type        = string
  sensitive   = true
}

# Ce secret n'en est pas vraiment un : il est en clair dans photos-realm.json,
# qui est versionné. Il reste une variable pour que quiconque redéploie la démo
# avec son propre realm puisse en mettre un autre sans toucher au module.
variable "photobook_client_secret" {
  description = "Secret du client OIDC confidentiel photobook, tel qu'importé dans le realm."
  type        = string
  sensitive   = true
  default     = "photobook-secret"
}

variable "php_version" {
  description = "Version PHP des deux apps Symfony. Les composer.json exigent >= 8.4."
  type        = string
  default     = "8.4"
}

# Le runtime static de Clever documente npm, pas bun. Le dépôt utilise bun en
# local, mais le build distant reste sur npm pour ne dépendre que de ce que
# l'image de build garantit.
variable "spa_build_command" {
  description = "Commande de build de PhotoPrint, exécutée depuis la racine du dépôt."
  type        = string
  default     = "cd demo/client-spa && npm install && npm run build"
}
