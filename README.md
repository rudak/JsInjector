# JsInjector

Bundle Symfony pour injecter des constantes PHP dans un fichier JavaScript.

## Installation

### PHP (Composer)

```bash
composer require rudak/js-injector
```

### JavaScript (npm)

```bash
npm install js-injector
```

## Utilisation rapide

### 1. Créer un provider de valeurs

Créez un service qui implémente `HarvesterInterface` :

```php
use Rudak\JsInjector\Harvester\HarvesterInterface;

class MyValuesProvider implements HarvesterInterface
{
    public function getValues(): array
    {
        return [
            'API_URL' => 'https://api.example.com',
            'DEBUG' => true,
        ];
    }
}
```

### 2. Taguer le service

```yaml
services:
    App\Service\MyValuesProvider:
        tags: ['rudak.injector']
```

### 3. Générer le fichier JS

```bash
php bin/console rudak:generate:js
```

Le fichier `injection.js` sera créé dans `/public/bundles/rudakInjection/`.

### 4. Inclure le script dans vos templates

```html
<script src="/bundles/rudakInjection/injection.js"></script>
<script>
    console.log(API_URL); // 'https://api.example.com'
    console.log(DEBUG);   // true
</script>
```

## API JavaScript

```javascript
import { inject, injectFromJson, generateInjectionCode } from 'js-injector';

// Injection directe
inject({ API_URL: 'https://api.example.com', DEBUG: true });

// Avec namespace
inject({ timeout: 5000 }, { namespace: 'MyApp' });
console.log(window.MyApp.timeout); // 5000

// Depuis JSON
injectFromJson('{"foo": 1, "bar": "hello"}');
```

## Scripts npm

```bash
npm test           # Lancer les tests
npm run lint       # Vérifier le code
npm run format     # Formater le code
```

## Licence

Apache License 2.0
