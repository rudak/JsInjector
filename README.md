# JsInjector

A modern JavaScript library for injecting values into the browser context. This package provides both a client-side JavaScript library and integrates with the PHP Symfony bundle for server-side value injection.

## Features

- 🚀 Modern ES6+ syntax with ES modules
- 📦 Tree-shakeable exports
- 🔒 Type validation for variable names
- 🎯 Namespace support for organized injection
- ⚡ Async script and JSON loading utilities
- 🧪 Comprehensive test coverage
- 📝 JSDoc documentation

## Installation

```bash
npm install js-injector
```

## Usage

### Basic Injection

```javascript
import { inject } from 'js-injector';

// Inject values into the global context
const result = inject({
  API_URL: 'https://api.example.com',
  DEBUG_MODE: true,
  CONFIG: { timeout: 5000 }
});

console.log(result.success); // true
console.log(result.injected); // ['API_URL', 'DEBUG_MODE', 'CONFIG']

// Values are now available globally
console.log(window.API_URL); // 'https://api.example.com'
```

### Using Namespaces

```javascript
import { inject } from 'js-injector';

// Inject values under a namespace
inject({
  apiUrl: 'https://api.example.com',
  timeout: 5000
}, { namespace: 'MyApp' });

// Access via namespace
console.log(window.MyApp.apiUrl); // 'https://api.example.com'
```

### Inject from JSON String

```javascript
import { injectFromJson } from 'js-injector';

const jsonData = '{"foo": 1, "bar": "hello"}';
const result = injectFromJson(jsonData);

console.log(window.foo); // 1
console.log(window.bar); // 'hello'
```

### Generate Injection Code

```javascript
import { generateInjectionCode } from 'js-injector';

// Generate JavaScript code for injection
const code = generateInjectionCode({
  API_URL: 'https://api.example.com',
  VERSION: '1.0.0'
});

// Use the generated code in a script tag or file
console.log(code);
// Output:
// var jsonContent = '{"API_URL":"https://api.example.com","VERSION":"1.0.0"}' || {};
// var { API_URL, VERSION } = JSON.parse(jsonContent);
```

### Loading Resources

```javascript
import { loadScript, loadJson, loadFromScriptTag } from 'js-injector';

// Load an external script
await loadScript('https://example.com/script.js', { timeout: 5000 });

// Load JSON from a URL
const data = await loadJson('https://api.example.com/config.json');

// Load from an embedded script tag
const config = loadFromScriptTag('#config-data');
```

### Utility Functions

```javascript
import {
  isValidVariableName,
  validateVariableNames,
  getVariableType,
  deepClone
} from 'js-injector';

// Validate variable names
isValidVariableName('validName'); // true
isValidVariableName('123invalid'); // false

// Get detailed type information
getVariableType([1, 2, 3]); // 'array [Elements: 3, Max depth: 1]'
getVariableType({ a: 1 }); // 'object [Keys: 1]'

// Deep clone values
const clone = deepClone({ nested: { value: 1 } });
```

## API Reference

### `inject(values, options?)`

Injects values into the target context.

**Parameters:**
- `values` (Object): Object with variable names as keys
- `options` (Object, optional):
  - `target` (Object): Target object for injection (default: `globalThis`)
  - `namespace` (string): Namespace to group injected values
  - `freeze` (boolean): Whether to freeze injected values (default: `false`)
  - `overwrite` (boolean): Whether to overwrite existing values (default: `true`)

**Returns:** `{ success: boolean, injected: string[], errors: Array }`

### `injectFromJson(jsonString, options?)`

Injects values from a JSON string.

**Parameters:**
- `jsonString` (string): JSON string containing values
- `options` (Object, optional): Same as `inject` options

**Returns:** Same as `inject`

### `generateInjectionCode(values, options?)`

Generates JavaScript code for injection.

**Parameters:**
- `values` (Object): Object with variable names as keys
- `options` (Object, optional):
  - `namespace` (string): Namespace for the generated code

**Returns:** `string` - JavaScript code

### `remove(keys, options?)`

Removes injected values.

**Parameters:**
- `keys` (string[]): Array of variable names to remove
- `options` (Object, optional):
  - `target` (Object): Target object (default: `globalThis`)
  - `namespace` (string): Namespace where values were injected

**Returns:** `string[]` - Array of keys that were removed

### `loadScript(url, options?)`

Loads a script from a URL.

**Parameters:**
- `url` (string): URL of the script
- `options` (Object, optional):
  - `timeout` (number): Timeout in milliseconds (default: `10000`)
  - `async` (boolean): Load asynchronously (default: `true`)
  - `defer` (boolean): Defer loading (default: `false`)

**Returns:** `Promise<HTMLScriptElement>`

### `loadJson(url, options?)`

Loads JSON from a URL.

**Parameters:**
- `url` (string): URL of the JSON resource
- `options` (Object, optional):
  - `timeout` (number): Timeout in milliseconds (default: `10000`)

**Returns:** `Promise<Object>`

## Error Handling

The library exports several error classes for precise error handling:

```javascript
import {
  InjectionError,
  LoadError,
  TimeoutError,
  ValidationError
} from 'js-injector';

try {
  await loadScript('https://example.com/script.js', { timeout: 1000 });
} catch (error) {
  if (error instanceof TimeoutError) {
    console.log(`Timeout after ${error.timeout}ms`);
  } else if (error instanceof LoadError) {
    console.log(`Failed to load: ${error.url}`);
  }
}
```

## PHP Symfony Bundle

This package also includes a PHP Symfony bundle for server-side integration. The bundle allows you to inject PHP constants and configuration values into JavaScript.

### PHP Installation

```json
{
  "require": {
    "rudak/js-injector": "^1.0"
  }
}
```

### PHP Usage

1. Create a service that implements `HarvesterInterface`:

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

2. Tag the service in your configuration:

```yaml
services:
    App\Service\MyValuesProvider:
        tags: ['rudak.injector']
```

3. Run the command to generate the JavaScript file:

```bash
php bin/console rudak:generate:js
```

## Scripts

```bash
# Run tests
npm test

# Run tests in watch mode
npm run test:watch

# Run tests with coverage
npm run test:coverage

# Lint code
npm run lint

# Fix linting issues
npm run lint:fix

# Format code with Prettier
npm run format

# Check code formatting
npm run format:check
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the Apache License 2.0 - see the [LICENSE](LICENSE) file for details.
