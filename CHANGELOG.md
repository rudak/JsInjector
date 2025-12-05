# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
