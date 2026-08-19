output "cloudpics_id_url" {
  description = "🛂 CloudPics ID, l'OIDC Provider."
  value       = local.keycloak_url
}

output "cloudpics_api_url" {
  description = "☁️ CloudPics API, le resource server."
  value       = "https://${var.api_vhost}"
}

output "photoprint_url" {
  description = "🌁 PhotoPrint, le client public."
  value       = "https://${var.photoprint_vhost}"
}

output "photobook_url" {
  description = "📕 PhotoBook, le client confidentiel."
  value       = "https://${var.photobook_vhost}"
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
