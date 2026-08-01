# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-08-01

### Added

- **Bundle Symfony modernisé** :
  - Cible Symfony 6.4/7.x et PHP 8.2+ (`composer.json` avec les vraies dépendances Symfony)
  - `VariableNameValidator` : validation complète des identifiants JS + mots réservés (miroir de la validation client)
  - `JsFileGenerator` : génération du fichier JS en PHP, sans dépendance Twig
  - `Configuration` bundle : `output_path`, `format` et `namespace` configurables
- **Formats de sortie** : `module` (défaut, `export default` / `export const <namespace>`, import ES) et `globals` (`var INJECTED_VALUES` + destructuring) — cohérents côté PHP (`JsFileGenerator`) et côté client (`generateInjectionCode`)
- **Injection dynamique par page** : tag `rudak.injector.dynamic` (avec `channel` optionnel) + attribut `#[JsInjectorDynamic]` posé sur un contrôleur (classe ou méthode) + switch global `dynamic_enabled` (défaut `false`). Le `DynamicValuesListener` (`kernel.response`) recalcule les valeurs des seuls channels demandés et les injecte dans un `<script type="application/json" id="js-injector-dynamic">` à **chaque chargement de page** (données fraîches). Les pages sans attribut ne sont jamais touchées
- `injectFromDom(selector, options)` : côté client, lit le tag JSON injecté et injecte les valeurs (`loadFromScriptTag` + `inject`)
- Configuration `dynamic_script_tag_id` (défaut `js-injector-dynamic`) et `dynamic_enabled`
- `rudak:debug:injection` : commande de debug listant les channels dynamiques et les providers statiques avec leurs données
  - Tests PHPUnit, PHPStan, PHP-CS-Fixer
- **CI** : job PHP (8.2/8.3/8.4) ajouté au workflow GitHub Actions

### Changed

- La commande `rudak:generate:js` est réécrite : attribut `AsCommand`, types stricts, `execute(): int`
- Le fichier généré utilise un littéral JSON (plus de `JSON.parse` sur une chaîne) :
  insensible aux apostrophes et aux sauts de ligne, `</script>` échappé
- Bibliothèque JS : build rollup (`dist/` ESM + CJS), types `index.d.ts`, Jest 30, ESLint 9 flat config
- `generateInjectionCode` produit le même format que la commande Symfony
- `deepClone` utilise `structuredClone` quand disponible

### Removed

- Imports cassés (`App\Entity\User\Like`, `ValuesNormalizer`, `Bim`, `ConfigCache`)
- `CacheManager` inutilisé
- Template Twig `injection.js.twig` (remplacé par `JsFileGenerator`)
- `commands.yml` commenté
- Dépendance Twig

## [1.0.0] - 2024-01-XX

### Added

- **JavaScript Library**: Modern ES6+ JavaScript library for client-side value injection
  - `inject()` - Inject values into target context
  - `injectFromJson()` - Inject values from JSON string
  - `generateInjectionCode()` - Generate JavaScript injection code
  - `remove()` - Remove injected values
  - `loadScript()` - Load external scripts with timeout support
  - `loadJson()` - Load JSON resources with fetch
  - `loadFromScriptTag()` - Load JSON from embedded script tags

- **Utility Functions**:
  - `isValidVariableName()` - Validate JavaScript identifiers
  - `validateVariableNames()` - Validate object keys as identifiers
  - `isValidUrl()` - URL validation
  - `isJsonSerializable()` - Check JSON serializability
  - `getVariableType()` - Get detailed type information
  - `getArrayDepth()` - Calculate array nesting depth
  - `deepClone()` - Deep clone values
  - `toJsonString()` / `parseJsonSafely()` - Safe JSON operations

- **Error Classes**:
  - `InjectionError` - General injection failures
  - `LoadError` - Resource loading failures
  - `TimeoutError` - Timeout errors
  - `ValidationError` - Validation failures

- **Development Infrastructure**:
  - Jest test framework with comprehensive test coverage (70%+)
  - ESLint configuration for code quality
  - Prettier for consistent code formatting
  - GitHub Actions CI workflow for automated testing
  - npm scripts for development workflow

- **Documentation**:
  - Comprehensive README with usage examples
  - JSDoc comments for all public functions
  - API reference documentation

### Changed

- Project structure now includes both PHP (Symfony bundle) and JavaScript components
- Added `js-src/` directory for JavaScript source modules
- Added `__tests__/` directory for unit tests

### Notes

- The PHP Symfony bundle functionality remains unchanged and backward compatible
- The JavaScript library is a new addition and does not affect existing PHP functionality
- Minimum Node.js version: 18.0.0 (for ES modules support)
