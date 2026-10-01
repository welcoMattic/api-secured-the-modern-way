# API Secured, the Modern Way

Slides du talk donné à **API Platform Con 2026**, construites avec [Slidev](https://sli.dev/).

## Deux decks, une seule base

Le talk existe en français et en anglais. Les deux partagent le thème et les
assets, seul le contenu diffère :

| Deck     | Point d'entrée                     | Contenu                   |
|----------|------------------------------------|---------------------------|
| Français | [`slides.md`](./slides.md)         | [`pages/fr/`](./pages/fr) |
| Anglais  | [`slides.en.md`](./slides.en.md)   | [`pages/en/`](./pages/en) |

## Développement

- `bun install`
- `bun dev` (français) ou `bun run dev:en` (anglais)
- Puis <http://localhost:3030>

Vue orateur : <http://localhost:3030/presenter/> · Vue d'ensemble : <http://localhost:3030/overview>

`bun run build` construit les deux decks : le français dans `dist/`, l'anglais
dans `dist/en/`. L'ordre compte, le build français vide `dist`.

Le thème maison est documenté dans [`THEME.md`](./THEME.md).

## Publication sur GitHub Pages

Le déploiement est automatique à chaque push sur `main`, via
[`.github/workflows/deploy.yml`](./.github/workflows/deploy.yml).

Mise en route, une seule fois :

1. Dans le dépôt GitHub : **Settings › Pages › Build and deployment › Source** → choisir
   **GitHub Actions** (et non « Deploy from a branch »).
2. Pousser sur `main`. Le deck français sort sur `https://<utilisateur>.github.io/<dépôt>/`,
   l'anglais sur `https://<utilisateur>.github.io/<dépôt>/en/`.

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
bun run slidev build slides.en.md --base /<nom-du-depot>/en/ --out dist/en
```

## Soutenir ce travail

Ce deck, la démo qui l'accompagne et mes contributions open source sont
librement accessibles. Si ça vous est utile, vous pouvez me soutenir sur
[GitHub Sponsors](https://github.com/sponsors/welcoMattic).
