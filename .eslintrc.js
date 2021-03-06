module.exports = {
    env: {
        browser: true,
    },
    extends: [
        "eslint:recommended",
        "plugin:sonarjs/recommended",
        "plugin:clean-regex/recommended",
        "plugin:vue/vue3-recommended",
        "prettier",
    ],
    parserOptions: {
        ecmaVersion: 2021,
        sourceType: "module",
    },
    plugins: ["vue", "sonarjs", "clean-regex"],
    rules: {
        "no-undef": "off",
    },
};
