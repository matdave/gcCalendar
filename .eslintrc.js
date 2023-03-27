module.exports = {
  env: {
    browser: true,
    es2021: true,
  },
  extends: ['eslint:recommended', 'plugin:prettier/recommended'],
  overrides: [],
  parserOptions: {
    ecmaVersion: 2020,
  },
  rules: {
    'prettier/prettier': ['error', {}, { usePrettierrc: true }],
  },
  globals: {
    Ext: 'readonly',
    MODx: 'readonly',
    gcCalendar: true,
    Extensible: true,
    window: 'readonly',
    module: 'readonly',
    _: 'readonly',
  },
};
