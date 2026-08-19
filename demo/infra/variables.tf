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

# Le module ne pin aucun vhost. Clever attribue à chaque app un domaine
# app-<uuid>.cleverapps.io dès sa création, et les URL se dérivent de l'id des
# ressources : aucun apply ne peut entrer en collision avec un domaine déjà pris,
# et deux personnes peuvent déployer la démo en parallèle.

# PhotoPrint est un SPA : il appelle l'API depuis le navigateur, donc il lui faut
# une origine autorisée. On ne peut pas y mettre son URL exacte : PhotoPrint lit
# l'URL de l'API, l'API lirait celle de PhotoPrint, et le graphe boucle. Le motif
# casse le cycle, au prix d'une autorisation à l'échelle de cleverapps.io. Pour
# resserrer après le premier apply : `tofu output photoprint_url`, puis coller
# cette valeur exacte ici.
variable "cors_allow_origin" {
  description = "Origines CORS acceptées par CloudPics API, sous forme d'expression régulière."
  type        = string
  default     = "^https://app-[0-9a-f-]+\\.cleverapps\\.io$"
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
