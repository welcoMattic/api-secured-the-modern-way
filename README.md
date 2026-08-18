# API Secured, the Modern Way

Slides du talk donné à **API Platform Con 2026**, construites avec [Slidev](https://sli.dev/).

## Développement

- `bun install`
- `bun dev`
- Puis <http://localhost:3030>

Vue orateur : <http://localhost:3030/presenter/> · Vue d'ensemble : <http://localhost:3030/overview>

Le contenu vit dans [`pages/`](./pages), assemblé par [`slides.md`](./slides.md).
Le thème maison est documenté dans [`THEME.md`](./THEME.md).

## Publication sur GitHub Pages

Le déploiement est automatique à chaque push sur `main`, via
[`.github/workflows/deploy.yml`](./.github/workflows/deploy.yml).

Mise en route, une seule fois :

1. Dans le dépôt GitHub : **Settings › Pages › Build and deployment › Source** → choisir
   **GitHub Actions** (et non « Deploy from a branch »).
2. Pousser sur `main`. Le site sort sur `https://<utilisateur>.github.io/<dépôt>/`.

Quelques points à connaître :

- Le **base path** est dérivé du nom du dépôt dans le workflow, il n'y a rien à coder en dur.
  Si tu publies sur un domaine personnalisé ou sur un dépôt `<utilisateur>.github.io`,
  remplace `--base /${{ github.event.repository.name }}/` par `--base /`.
- Le workflow installe Chromium, parce que `download: true` dans `slides.md` déclenche
  un export PDF pendant le build. Sans navigateur, le build échoue.

Pour reproduire le build de production en local :

```shell
bunx playwright install chromium   # une seule fois
bun run slidev build --base /<nom-du-depot>/
```
