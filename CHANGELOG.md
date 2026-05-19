# Journal des modifications

Toutes les modifications notables sont listées ici. Le format suit [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/) et le paquet adhère au [versionnement sémantique](https://semver.org/lang/fr/).

## [0.1.4] — 2026-05-19

### Corrigé

- **`IncidentDto` — timestamps Robotalp** — l'API Robotalp renvoie `started_at` et `ended_at` sous forme d'entier Unix en millisecondes (ex : `1779140556104`). La v0.1.3 passait cette valeur brute à `Carbon::parse()`, ce qui levait `Failed to parse time string`. Le DTO normalise désormais toute valeur numérique vers une chaîne ISO 8601 (`gmdate('c', …)`), avec division automatique ms → s. Les chaînes ISO déjà valides passent inchangées.

---

## [0.1.3] — 2026-05-19

### Corrigé

- **Endpoints Robotalp réels** — les chemins de l'API utilisés par `RobotalpProvider` retournaient tous des 404 contre l'API publique de Robotalp. Mapping corrigé :
  - `buildOverview` : `workspace/{id}/robots/status/` → `robot/status/{id}/`
  - `listMonitors` : `workspace/{id}/robots/` → `robot/?workspace_id={id}`
  - `getMonitor` : `robot/{id}/` → `robot/{id}` (sans slash final pour éviter le 301 qui perd l'en-tête `Authorization`)
  - `getActiveIncidents` : `workspace/{id}/incident/active/` → `incident/?workspace_id={id}&status_id=0`
  - `getMonitorDashboard` : `robot/{id}/` → `robot/{id}/dashboard`
- **Schéma d'authentification** — l'en-tête `Authorization: Token <clef>` est remplacé par `Authorization: ApiKey <clef>`, le schéma réellement attendu par l'API Robotalp.

### Ajouté

- Test Pest dédié vérifiant que l'en-tête `Authorization: ApiKey ...` est bien envoyé.
- Test Pest dédié vérifiant le bon endpoint et les paramètres `workspace_id` + `status_id=0` pour les incidents actifs.

### Migration

```bash
composer update memora/statut
```

Aucune variable d'environnement à modifier. La clef API Robotalp continue de se définir via `STATUT_ROBOTALP_API_KEY`.

---

## [0.1.2] — 2026-05-19

### Corrigé

- **Régression v0.1.1 — vues namespacées** — le regex de validation de `STATUT_LAYOUT` introduit en v0.1.1 rejetait à tort les vues namespacées Laravel (`paquet::vue.name`), syntaxe Blade standard utilisée par les modules nwidart, Filament, Jetstream et tout paquet publiant un layout. Le caractère `:` est désormais autorisé. La validation reste safe (pas de `/`, `\`, espace ni `..`).

---

## [0.1.1] — 2026-05-18

### Corrigé (audit code-reviewer)

- **Rate-limit** `throttle:60,1` par défaut sur la route `/statut` pour protéger l'API distante des rafales.
- **Defaults brand à `null`** pour éviter une fuite involontaire de la marque MEMORA solutions si l'intégrateur oublie ses variables d'environnement.
- **`IncidentDto::monitorId`** devient `?int` au lieu d'un fallback à `0` (valeur d'ID invalide).
- **`incident-card`** : `role="article"` au lieu de `role="alert"` sur chaque carte (corrige un anti-pattern WCAG).
- **`Carbon::parse('')`** protégé contre les chaînes vides qui retournaient silencieusement `now()`.
- **`RobotalpProvider`** : garde explicite si `api_key` ou `workspace_id` manquent — message d'erreur en français sans fuite du corps Robotalp.
- **`StatutController`** : validation regex du `layout` et de la `section` (defense in depth).
- Code mort retiré : `MonitorStatus::label()`, fichiers `lang/{fr,en}/status.php`.
- Clef de config `locale_default` retirée (`app.locale` Laravel est l'autorité unique).

### Modifié

- `composer.json` : retrait de `guzzlehttp/guzzle` redondant (le code utilise la façade `Http`), ajout d'`illuminate/http` explicite, ajout du bloc `support` (issues, source).
- Vues : bloc brand entièrement omis si `name` et `logo` absents, footer rend la marque uniquement si `name` est défini, `rel="noopener noreferrer"` sur tous les liens externes.
- README : documentation de la dépendance au `<html lang>` du layout hôte.

---

## [0.1.0] — 2026-05-18

### Première version publique

- Page publique `/statut` (route configurable) qui s'intègre dans le layout Blade du site hôte.
- Contrat `MonitoringProvider` + implémentation `RobotalpProvider` (façade `Http` Laravel + `Cache::remember` 60 s).
- Indicateur global (tous opérationnels, incident en cours, maintenance) avec compteurs total / up / down / paused / incidents.
- Cartes de moniteurs avec statut, dernier check, temps de réponse, barres uptime 24 h / 7 j / 30 j / 90 j.
- Liste des incidents actifs.
- Internationalisation FR (par défaut) + EN, traductions publiables.
- Mode sombre automatique via `prefers-color-scheme`.
- Accessibilité WCAG 2.2 AA (contrastes ≥ 4.5:1, focus visibles, rôles ARIA, cibles tactiles ≥ 44 × 44).
- Auto-refresh 60 s côté client, cache 60 s côté serveur.
- Aucune dépendance JavaScript ni Tailwind requise.
- Vues, configuration et traductions entièrement publiables.
- Tests Pest unitaires et fonctionnels via Http::fake() + Orchestra Testbench.
- CI GitHub Actions sur matrice PHP 8.2 / 8.3 / 8.4 × Laravel 10 / 11 / 12.

---

— [MEMORA solutions](https://memora.solutions) · info@memora.ca
