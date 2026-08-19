# Les quatre URL ne sont connues qu'après l'apply : Clever attribue les domaines
# à la création, le module n'en pin aucun. Ce sont ces valeurs qu'il faut reporter
# dans le realm avant de l'importer.

output "cloudpics_id_url" {
  description = "🛂 CloudPics ID, l'OIDC Provider."
  value       = local.keycloak_url
}

output "cloudpics_api_url" {
  description = "☁️ CloudPics API, le resource server."
  value       = local.api_url
}

output "photoprint_url" {
  description = "🌁 PhotoPrint, le client public."
  value       = local.photoprint_url
}

output "photobook_url" {
  description = "📕 PhotoBook, le client confidentiel."
  value       = local.photobook_url
}

# L'add-on managé génère son propre compte admin et impose de changer le mot de
# passe à la première connexion : ce n'est pas admin/admin comme en local.
output "cloudpics_id_admin_username" {
  description = "Compte admin généré par l'add-on Keycloak."
  value       = clevercloud_keycloak.cloudpics_id.admin_username
}

output "cloudpics_id_admin_password" {
  description = "Mot de passe temporaire de l'admin. À changer à la première connexion."
  value       = clevercloud_keycloak.cloudpics_id.admin_password
  sensitive   = true
}

# Le realm et le thème de login se déposent dans ce bucket. Voir import-realm.sh.
output "cloudpics_id_fsbucket_id" {
  description = "FS Bucket de l'add-on, où déposer le realm et le thème."
  value       = clevercloud_keycloak.cloudpics_id.fsbucket_id
}
