# Statut — page de statut Laravel multilingue

[![Latest Version on Packagist](https://img.shields.io/packagist/v/memora/statut.svg)](https://packagist.org/packages/memora/statut)
[![Total Downloads](https://img.shields.io/packagist/dt/memora/statut.svg)](https://packagist.org/packages/memora/statut)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Une page de statut publique pour vos applications Laravel, prête à brancher sur une plateforme de monitoring tierce (Robotalp aujourd'hui, d'autres demain), entièrement traduite (FR par défaut, EN inclus), accessible (WCAG 2.2 AA), responsive, avec mode sombre automatique et zéro dépendance frontale.

## Pourquoi

Les pages de statut hébergées des plateformes de monitoring sont rarement traduites, jamais à la marque de votre site, et difficiles à intégrer dans le design existant. Ce paquet vous donne une page `/statut` 100 % sous votre contrôle, qui consomme l'API de votre fournisseur de surveillance et affiche les données dans un layout intégré au reste de votre site.

## Fonctionnalités

- Page publique `/statut` (route configurable) qui s'intègre dans le layout Blade de votre site hôte
- Multi-fournisseur via un contrat `MonitoringProvider` — implémentation Robotalp incluse, ajout d'autres plateformes sans toucher au reste du paquet
- Indicateur global d'état (tous opérationnels, incident en cours, maintenance) avec compteurs détaillés
- Cartes de moniteurs avec statut, dernier check, temps de réponse, et barre de disponibilité par fenêtre temporelle (24 h, 7 j, 30 j, 90 j)
- Liste des incidents en cours
- Internationalisation : français (par défaut) et anglais inclus, ajout d'autres langues trivial
- Mode sombre automatique via `prefers-color-scheme`
- Accessibilité WCAG 2.2 AA (contrastes, focus visibles, rôles ARIA, aria-live, cibles tactiles minimum 44×44 px)
- Mise en cache configurable (60 s par défaut) pour limiter la charge sur l'API distante
- Aucune dépendance JavaScript ni Tailwind requise côté hôte — un seul bloc CSS scopé sous `.statut-*` pour éviter tout conflit avec votre design
- Vues et traductions entièrement publiables et personnalisables

## Pré-requis

- PHP 8.2 ou plus récent
- Laravel 10, 11 ou 12
- Un compte sur une plateforme de monitoring supportée (actuellement : Robotalp)

## Installation

```bash
composer require memora/statut
```

Le service provider est auto-découvert. Publiez ensuite la configuration :

```bash
php artisan vendor:publish --tag=statut-config
```

(Optionnel) publiez les vues et les traductions si vous voulez les personnaliser :

```bash
php artisan vendor:publish --tag=statut-views
php artisan vendor:publish --tag=statut-lang
```

## Configuration

Ajoutez les variables d'environnement nécessaires dans votre `.env` :

```env
STATUT_ENABLED=true
STATUT_PROVIDER=robotalp
STATUT_ROUTE_PATH=/statut
STATUT_LAYOUT=layouts.app
STATUT_SECTION=content
STATUT_CACHE_TTL=60

STATUT_BRAND_NAME="Votre marque"
STATUT_BRAND_URL=https://votre-site.example
STATUT_BRAND_LOGO=https://votre-site.example/logo.svg

# Robotalp
STATUT_ROBOTALP_BASE_URL=https://api.robotalp.com
STATUT_ROBOTALP_API_KEY=votre_clef_api
STATUT_ROBOTALP_WORKSPACE_ID=123456

# Optionnel : moniteurs à masquer (CSV d'identifiants)
STATUT_HIDDEN_MONITORS=99999,88888
```

### Layout du site hôte

La page de statut étend le layout défini par `STATUT_LAYOUT` (`layouts.app` par défaut) et injecte son contenu dans la section `STATUT_SECTION` (`content` par défaut). Assurez-vous que votre layout Blade contient bien `@yield('content')` (ou ajustez `STATUT_SECTION` au nom utilisé chez vous).

### Personnaliser les vues

Si vous avez publié les vues (`statut-views`), modifiez-les dans `resources/views/vendor/statut`. Le CSS est scopé sous `.statut-page` ; vous pouvez le surcharger sans risque de conflit.

### Ajouter ou modifier une langue

Si vous avez publié les traductions (`statut-lang`), elles vivent dans `lang/vendor/statut/{fr,en}/messages.php` et `status.php`. Pour ajouter une nouvelle langue, copiez l'un des dossiers existants et traduisez les chaînes.

## Utilisation

Une fois installé et configuré, ouvrez `/statut` (ou la route que vous avez choisie). La page affichera l'état global, la liste des moniteurs et les incidents actifs, le tout rafraîchi automatiquement toutes les 60 secondes.

Vous pouvez aussi accéder programmatiquement au fournisseur de surveillance via la façade :

```php
use Memora\Statut\Facades\Statut;

$overview = Statut::getOverview();
$monitors = Statut::listMonitors();
$incidents = Statut::getActiveIncidents();
```

## Étendre avec un nouveau fournisseur

Tous les fournisseurs implémentent l'interface `Memora\Statut\Contracts\MonitoringProvider`. Pour brancher une plateforme autre que Robotalp :

1. Créez une classe `MonNouveauProvider implements MonitoringProvider` dans votre application.
2. Liez-la dans un service provider de votre application :

```php
$this->app->singleton(\Memora\Statut\Contracts\MonitoringProvider::class, fn () => new MonNouveauProvider($config));
```

3. Définissez `STATUT_PROVIDER=mon_nouveau_provider` (la valeur informative — c'est la liaison du conteneur qui détermine la classe utilisée).

## Tests

```bash
composer install
composer test
```

Les tests utilisent Pest et Orchestra Testbench. Toutes les requêtes HTTP sont simulées via `Http::fake()`.

## Sécurité

Si vous découvrez une vulnérabilité, écrivez à **info@memora.ca** plutôt que d'ouvrir une issue publique. Nous accusons réception sous 72 heures.

## Contribution

Les contributions sont bienvenues. Ouvrez une issue avant toute PR significative pour qu'on en discute. Respectez PSR-12 et ajoutez des tests Pest pour toute fonctionnalité.

## Licence

Distribué sous licence MIT — voir [LICENSE](LICENSE).

## Auteur

**[MEMORA solutions](https://memora.solutions)** — info@memora.ca
