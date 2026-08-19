terraform {
  required_version = ">= 1.6"

  required_providers {
    clevercloud = {
      source  = "CleverCloud/clevercloud"
      version = "~> 2.1"
    }
  }
}

# Aucun credential ici. Le provider lit CC_ORGANISATION, CC_OAUTH_TOKEN et
# CC_OAUTH_SECRET dans l'environnement : les jetons de `clever login` suffisent,
# et rien de sensible ne peut se retrouver dans un fichier versionné.
provider "clevercloud" {}
