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
- `--font-emoji` : pile de polices emoji, **épinglée dans chaque `font-family`**.
  Sans elle, le navigateur peint l'emoji avec une police de repli puis re-résout
  vers la police emoji couleur, ce qui produit un saut de taille visible à
  l'arrivée sur la slide.

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
- `<Pillar accent icon title tag question lead>` : carte « pilier », en-tête à
  hauteur fixe pour que les cartes restent alignées même si un nom passe sur
  deux lignes.
- `<LogoGrid gapX gapY cols>` + `<Logo src label eu strong size>` : grilles de
  logos. Sans `cols`, disposition `flex-wrap` ; avec `cols`, grille à colonnes
  fixes, seule façon d'obtenir des rangées régulières quand les labels ont des
  largeurs inégales.
- `<ServiceGroup europe label cols>` : groupe de logos encadré et titré.
- `<Alert type>` : callout (info / warning / error), clair.

## Utilitaires de slide (`theme/styles/layout.css`)

Deux lignes de clôture reviennent partout dans le deck. Elles s'écrivaient de
sept façons différentes (h4 + marge ad-hoc, classes scopées par slide, utilitaires
`opacity-*` bruts) ; elles ont désormais un seul vocabulaire.

| Classe         | Rôle                                          |
|----------------|-----------------------------------------------|
| `.slide-punch` | L'assertion sur laquelle la slide atterrit    |
| `.slide-note`  | La glose discrète qui nuance ce qui précède   |

Les deux suivent l'alignement ambiant (à gauche sur `default`) ; ajouter
`.is-centered` sur les slides héros dont le contenu est lui-même centré.
`.slide-punch` colore ses `<b>` avec `--sec`, `.slide-note` les passe en `--c-fg`.

**Toujours les écrire en `<div class="...">`**, jamais avec la syntaxe d'attribut
`{.slide-punch}` : dès qu'une ligne contient du code inline ou un `<br/>`,
l'attribut s'accroche au dernier fragment au lieu du bloc entier.

## Diagrammes (`theme/setup/mermaid.ts`)

- `fontFamily` **doit** rester aligné sur la police réellement rendue (Inter).
  Mermaid calcule la géométrie du SVG en mesurant le texte : si le thème impose
  une autre police au rendu, les labels sortent de leurs boîtes.
- `mirrorActors: false` supprime la rangée d'acteurs dupliquée en bas.
- Ne **pas** utiliser l'option `{scale: N}` de Slidev sur un bloc mermaid : elle
  désynchronise le texte des formes. Pour réduire un diagramme, jouer sur
  `messageMargin` / `boxMargin` via un `%%{init}%%` local.

## Chrome global

- `theme/global-bottom.vue` : numéro de page + logo conf (masqué page 1) + barre
  de progression. Le logo est le point de branding a swapper par talk.

## Rebrander pour un autre talk

1. Remplacer le logo (`public/apipcon.svg`) et l'`img` dans `global-bottom.vue`.
2. Ajuster `--c-accent` / la rampe `--a-*` si la marque l'exige.
3. Adapter le mapping des sections (`.sec-*`) au plan du nouveau talk.
4. Reprendre cover / closing avec le nouveau titre.

## Reste possible (non bloquant)

- Les blocs de code côte à côte (config Symfony) restent en grille Uno brute :
  aucun composant du thème ne couvre ce cas, et il est unique.
- Extraire tokens + layouts + composants dans un paquet `slidev-theme-*` publiable.
