/**
 * Gets the type of a value with additional details for arrays
 * @param {*} value - The value to analyze
 * @returns {string} Human-readable type description
 */
export const getVariableType = (value) => {
  const type = typeof value;

  if (value === null) {
    return 'null';
  }

  if (Array.isArray(value)) {
    const depth = getArrayDepth(value);
    return `array [Elements: ${value.length}, Max depth: ${depth}]`;
  }

  if (type === 'object') {
    return `object [Keys: ${Object.keys(value).length}]`;
  }

  return type;
};

/**
 * Calculates the maximum depth of a nested array
 * @param {*} arr - The array to analyze
 * @returns {number} The maximum depth (1 for flat array)
 */
export const getArrayDepth = (arr) => {
  if (!Array.isArray(arr)) {
    return 0;
  }

  let maxDepth = 1;

  for (const item of arr) {
    if (Array.isArray(item)) {
      const depth = 1 + getArrayDepth(item);
      if (depth > maxDepth) {
        maxDepth = depth;
      }
    }
  }

  return maxDepth;
};

/**
 * Deep clones a value using structuredClone when available, falling back to JSON round-trip.
 * @param {*} value - The value to clone
 * @returns {*} A deep clone of the value
 */
export const deepClone = (value) => {
  if (value === undefined) {
    return undefined;
  }

  if (typeof structuredClone === 'function') {
    return structuredClone(value);
  }

  return JSON.parse(JSON.stringify(value));
};

/**
 * Converts a value to a JSON string that is safe to embed in a `<script>` tag.
 * Since ES2019, JSON.stringify already escapes U+2028/U+2029 and slash sequences
 * like `</script>` are neutralized by escaping the closing tag.
 * @param {*} value - The value to convert
 * @returns {string} JSON string safe for JS embedding
 */
export const toJsonString = (value) => {
  return JSON.stringify(value).replace(/</g, '\\u003C');
};

/**
 * Parses a JSON string safely
 * @param {string} jsonString - The JSON string to parse
 * @returns {{success: boolean, data: *, error: Error|null}} Parse result
 */
export const parseJsonSafely = (jsonString) => {
  try {
    const data = JSON.parse(jsonString);
    return { success: true, data, error: null };
  } catch (error) {
    return { success: false, data: null, error };
  }
};
