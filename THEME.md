# Design system (thème Slidev réutilisable)

Ce deck sert de base à un thème Slidev générique, réutilisable pour de futures
présentations. Le principe : la **structure** (layouts, composants, tokens) est
neutre ; le **branding** (logo, couleurs de marque) est isolable.

Règle de base : **fond clair, jamais sombre** (sauf la cover, exception assumée).

## Tokens (`theme/styles/tokens.css`)

Fondation neutre, re-thémable en changeant ces variables.

- Surfaces / texte : `--c-bg`, `--c-surface`, `--c-fg`, `--c-muted`, `--c-border`.
- Rampe d'accent harmonieuse teal -> magenta : `--a-1` (teal) ... `--a-7` (magenta),
  chacune avec sa variante `--a-N-rgb` pour les tintes `rgba()`.
- Accents sémantiques : `--c-accent` (magenta de marque), `--c-accent-2` (teal).
- Échelles : `--fs-*`, `--sp-*`, `--radius*`, `--shadow-card`.

## Couleur par section (fil rouge)

Classe posée en frontmatter (`class: sec-*`) sur chaque slide ; pilote `--sec`,
utilisé par le tick de titre, l'index de section, l'en-tête de table.

| Section          | Classe      | Couleur          |
|------------------|-------------|------------------|
| Autorisation     | `sec-authz` | teal (`--a-1`)   |
| Authentification | `sec-authn` | indigo (`--a-4`) |
| Rate Limiting    | `sec-rate`  | magenta (`--a-7`)|
| API Gateway      | `sec-gw`    | violet (`--a-5`) |

Sans classe (intro, conclusion) : `--sec` retombe sur `--c-accent` (magenta).

## Typographie

- Titres : **Sora** (grotesque), chargé via la config `fonts` de `slides.md`.
- Corps : **Inter**. Mono : Fira Code.
- Titre `default` : tick d'accent court sous le début (pas de souligné pleine largeur).
- Titres centrés (`statement`/`fact`/`center`) : tick centré, tailles équilibrées
  (section 5rem, statement/fact 4.6rem).

## Layouts

- `cover` : hero sombre (exception), logo + surtitre + titre + chips + auteur.
- `section` : index (01/02/03) + titre + couleur de section.
- `statement` / `fact` / `center` : gros titre centré + tick.
- `default` : titre + tick + sous-titre + contenu (puces agrandies), zone de
  sécurité basse pour ne pas heurter le footer.
- `closing` : clair, écho de la cover (eyebrow + gros titre + contacts).
- `about-me` : présentation orateur (photo + infos).

## Composants (`theme/components/`)

- `<Card accent icon|badge title>` + `<CardGrid cols>` : cartes à accent (rampe).
- `<LogoGrid gapX gapY>` + `<Logo src label eu strong>` : grilles de logos unifiées.
- `<Alert type>` : callout (info / warning / error), clair.

## Chrome global

- `theme/global-bottom.vue` : numéro de page + logo conf (masqué page 1) + barre
  de progression. Le logo est le point de branding a swapper par talk.

## Rebrander pour un autre talk

1. Remplacer le logo (`public/apipcon.svg`) et l'`img` dans `global-bottom.vue`.
2. Ajuster `--c-accent` / la rampe `--a-*` si la marque l'exige.
3. Adapter le mapping des sections (`.sec-*`) au plan du nouveau talk.
4. Reprendre cover / closing avec le nouveau titre.

## Reste possible (non bloquant)

- Migrer les grandes grilles OIDC/gateway vers `<LogoGrid>` (aujourd'hui en markup
  inline calé sur 2 lignes ; fonctionnel, migration = pur refactor).
- Extraire tokens + layouts + composants dans un paquet `slidev-theme-*` publiable.
