/**
 * JsInjector - inject PHP constants into the browser context.
 */

export interface InjectionOptions {
  target?: object | null;
  namespace?: string | null;
  freeze?: boolean;
  overwrite?: boolean;
}

export interface InjectionResult {
  success: boolean;
  injected: string[];
  errors: Array<{ key: string; error: string }>;
}

export interface LoaderOptions {
  timeout?: number;
  async?: boolean;
  defer?: boolean;
}

export type InjectionCodeFormat = 'module' | 'globals';

export interface GenerateInjectionCodeOptions {
  namespace?: string;
  format?: InjectionCodeFormat;
}

export function inject(values: object, options?: InjectionOptions): InjectionResult;
export function injectFromJson(jsonString: string, options?: InjectionOptions): InjectionResult;
export function injectFromDom(selector: string, options?: InjectionOptions): InjectionResult | null;
export function generateInjectionCode(values: object, options?: GenerateInjectionCodeOptions): string;
export function remove(keys: string[], options?: InjectionOptions): string[];

export function loadScript(url: string, options?: LoaderOptions): Promise<HTMLScriptElement>;
export function loadJson(url: string, options?: LoaderOptions): Promise<object>;
export function loadFromScriptTag(selector: string): object | null;

export class InjectionError extends Error {
  code: string;
  constructor(message: string, code?: string);
}
export class LoadError extends Error {
  url: string | null;
  constructor(message: string, url?: string | null);
}
export class TimeoutError extends Error {
  timeout: number | null;
  constructor(message: string, timeout?: number | null);
}
export class ValidationError extends Error {
  field: string | null;
  constructor(message: string, field?: string | null);
}

export interface VariableNameValidationResult {
  valid: boolean;
  invalidKey: string | null;
}

export function isValidVariableName(name: string): boolean;
export function validateVariableNames(values: object): VariableNameValidationResult;
export function isValidUrl(url: string): boolean;
export function isJsonSerializable(value: unknown): boolean;
export function validateOptions(options: InjectionOptions): void;
export function getVariableType(value: unknown): string;
export function getArrayDepth(arr: unknown): number;
export function deepClone<T>(value: T): T;
export function toJsonString(value: unknown): string;
export function parseJsonSafely(
  jsonString: string
): { success: boolean; data: unknown; error: Error | null };
