module.exports = [
    {
        ignores: [
            'node_modules/**',
            'public/build/**',
            'var/**',
            'assets/vendor/**'
        ]
    },
    {
        files: ['assets/**/*.{js,ts}'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module'
        },
        linterOptions: {
            reportUnusedDisableDirectives: true
        },
        rules: {
            'no-unused-vars': ['warn', { argsIgnorePattern: '^_' }]
        }
    }
];
