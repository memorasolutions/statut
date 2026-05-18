# Contribuer à Statut

Merci de l'intérêt porté à ce paquet. Voici comment contribuer efficacement.

## Avant d'ouvrir une PR

Pour toute modification non triviale, **ouvrez d'abord une issue** pour qu'on discute de l'approche. Cela évite que votre travail ne corresponde pas à la direction du projet.

## Workflow

1. Forkez le repo
2. Créez une branche descriptive : `feat/uptime-robot-provider` ou `fix/cache-key-collision`
3. Écrivez le code + les tests Pest correspondants
4. Lancez la suite : `composer test`
5. Respectez **PSR-12** et `declare(strict_types=1)` partout
6. Ouvrez la PR vers `main` avec une description claire

## Style

- PHP 8.2+
- PSR-12
- Final classes par défaut
- Readonly DTOs
- Aucune dépendance JS/CSS frontale ajoutée (le paquet reste minimal)
- Tous les nouveaux textes utilisateur traduits en FR **et** EN

## Ajouter un nouveau fournisseur de surveillance

1. Créer `src/Providers/MonProvider.php` implémentant `Memora\Statut\Contracts\MonitoringProvider`
2. Ajouter le binding conditionnel dans `StatutServiceProvider::register()`
3. Ajouter la section de config dans `config/statut.php`
4. Écrire les tests unitaires avec `Http::fake()`
5. Documenter dans le README (variables ENV, exemple)

## Code de conduite

Soyez respectueux. Pas de discrimination, pas d'attaques personnelles. Les comportements toxiques sont signalés à info@memora.ca.

— [MEMORA solutions](https://memora.solutions)
