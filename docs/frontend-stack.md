# Stack Frontend : Tailwind CSS v4 + Alpine.js + Motion One

## Quoi

| Lib                 | Rôle                                                        | Version |
| ------------------- | ----------------------------------------------------------- | ------- |
| **Tailwind CSS v4** | Framework utilitaire CSS (config en CSS, plugin Vite natif) | ^4.0    |
| **Alpine.js**       | Micro-framework JS réactif (modales, toggles, interactions) | ^3.15   |
| **Motion One**      | Animations web modernes et performantes                     | ^10.18  |
| **Vite**            | Bundler/dev server avec HMR                                 | ^7.0    |

## Architecture (simple, Docker-ready)

```
assets/
  main.js          ← point d'entrée JS (importe CSS, Alpine, Motion)
  styles/app.css   ← point d'entrée CSS (@import "tailwindcss" + @theme)
templates/
  base.html.twig   ← charge Vite en dev (HMR) ou les builds en prod
  partials/
    nav.html.twig
    footer.html.twig
  home/
    index.html.twig ← landing page
public/build/       ← généré par `npm run build` (gitignored)
vite.config.js      ← config Vite + plugin @tailwindcss/vite
package.json        ← dépendances et scripts
package-lock.json   ← lockfile (commité pour builds reproductibles)
```

Pas de `tailwind.config.js` ni `postcss.config.js` : Tailwind v4 se configure directement en CSS et utilise le plugin Vite.

## Installation

```bash
npm install
```

Ou via le script fourni :

```bash
# PowerShell
pwsh ./scripts/setup-frontend.ps1

# POSIX (Linux/macOS/WSL)
bash scripts/setup-frontend.sh
```

## Commandes

| Commande          | Effet                                             |
| ----------------- | ------------------------------------------------- |
| `npm run dev`     | Lance Vite en dev (HMR sur http://localhost:5173) |
| `npm run build`   | Build production dans `public/build/`             |
| `npm run preview` | Prévisualise le build prod                        |

## Fonctionnement dev vs prod

### Dev (développement local)

`base.html.twig` charge le client Vite HMR et le point d'entrée JS depuis `http://localhost:5173` :

```twig
{% if app.debug %}
    <script type="module" src="http://localhost:5173/@vite/client"></script>
    <script type="module" src="http://localhost:5173/assets/main.js"></script>
{% endif %}
```

Workflow :

1. `npm run dev` (terminal 1)
2. `symfony server:start` (terminal 2)
3. Ouvrir l'URL Symfony → les styles et scripts sont servis par Vite avec rechargement instantané.

### Prod (production / déploiement)

```bash
npm run build
```

Génère `public/build/assets/main-XXXXX.css` et `main-XXXXX.js` + un `manifest.json`.

`base.html.twig` charge les fichiers buildés :

```twig
{% if not app.debug %}
    <link rel="stylesheet" href="{{ asset('build/assets/main.css') }}">
    <script type="module" src="{{ asset('build/assets/main.js') }}"></script>
{% endif %}
```

> Note : les noms de fichiers buildés contiennent un hash. Pour la prod, il faudra lire le `manifest.json` ou utiliser des noms fixes. Ce point sera résolu lors de l'intégration Docker/CI.

## Personnalisation du thème (Tailwind v4)

Tout se fait dans `assets/styles/app.css` :

```css
@import "tailwindcss";

@theme {
    --color-primary: #2563eb;
    --color-accent: #fb923c;
}
```

Les classes `bg-primary`, `text-primary`, `bg-accent`, etc. sont automatiquement disponibles.

## Préparation Docker (plus tard)

L'architecture est prête pour Docker :

- Le build Node (`npm run build`) peut être une étape dans un `Dockerfile` multi-stage.
- Les assets buildés dans `public/build/` sont servis statiquement par Nginx/Apache.
- Aucune dépendance PHP sur le bundler (pas de `symfony/vite-bundle`).
- La variable `APP_DEBUG=0` dans le conteneur prod active le chargement des fichiers build.

## Maintenance

- `npm outdated` pour vérifier les mises à jour.
- `npm audit` pour scanner les vulnérabilités.
- `package-lock.json` commité pour builds reproductibles.
- Tester visuellement après chaque mise à jour de dépendances.
