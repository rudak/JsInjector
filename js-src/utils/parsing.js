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
 * Deep clones a value using JSON serialization
 * @param {*} value - The value to clone
 * @returns {*} A deep clone of the value
 */
export const deepClone = (value) => {
  if (value === undefined) {
    return undefined;
  }
  return JSON.parse(JSON.stringify(value));
};

/**
 * Converts a value to a safe JSON string
 * @param {*} value - The value to convert
 * @returns {string} JSON string representation
 */
export const toJsonString = (value) => {
  return JSON.stringify(value);
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
