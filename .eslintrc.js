module.exports = {
    env: {
        browser: true,
        es6: true,
        jquery: true,
        node: true
    },
    extends: [
        'eslint:recommended'
    ],
    globals: {
        // WordPress globals
        wp: 'readonly',
        jQuery: 'readonly',
        $: 'readonly',
        ajaxurl: 'readonly',
        // Plugin specific globals
        wpcw_ajax: 'readonly',
        wpcw_vars: 'readonly',
        // Elementor globals
        elementorFrontend: 'readonly',
        elementorModules: 'readonly'
    },
    parserOptions: {
        ecmaVersion: 2018,
        sourceType: 'module'
    },
    rules: {
        // Code quality
        'no-console': 'warn',
        'no-debugger': 'error',
        'no-alert': 'warn',
        'no-unused-vars': 'error',
        'no-undef': 'error',
        
        // Style consistency
        'indent': ['error', 4],
        'quotes': ['error', 'single'],
        'semi': ['error', 'always'],
        'comma-dangle': ['error', 'never'],
        
        // Best practices
        'eqeqeq': 'error',
        'no-eval': 'error',
        'no-implied-eval': 'error',
        'no-new-func': 'error',
        'no-script-url': 'error',
        
        // WordPress specific
        'camelcase': 'off', // WordPress uses snake_case
        'dot-notation': 'off' // Allow bracket notation for WordPress
    },
    overrides: [
        {
            files: ['admin/js/**/*.js'],
            env: {
                browser: true,
                jquery: true
            },
            globals: {
                pagenow: 'readonly',
                adminpage: 'readonly'
            }
        },
        {
            files: ['public/js/**/*.js'],
            env: {
                browser: true,
                jquery: true
            }
        },
        {
            files: ['elementor/**/*.js'],
            globals: {
                elementorFrontend: 'readonly',
                elementorModules: 'readonly'
            }
        }
    ]
};