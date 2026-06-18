# WordPress pre-commit git hooks

WP Pre-Commit Hooks

Will check for the code standards in PHP and JavaScript/React.

Node.js requirement: `20.x`


# Include in your projects
Include the project in your Composer file:

    *nikolareljin/wp-precommit-hooks*


You can run :

`composer require nikolareljin/wp-precommit-hooks -dev -vv`

Or include the plugin in your composer file:

```json
{
  "name": "testing-the-plugin",
  "type": "project",
  "description": "testing how git commit hooks work",
  "require": {
    "php": ">=7.1"
  },
  "require-dev": {
    "php": ">=7.1",
    "nikolareljin/wp-precommit-hooks": ">=1.0.2"
  },
  "minimum-stability": "dev",
  "prefer-stable": true
}
```



# Usage
On `git commit`, it will trigger code checkup for WP and React/Gutenberg standards.


# Run / Install

In the terminal, run: 

`npm install`

`composer install`

## Available npm scripts

`npm run build` - no-op placeholder in this package (no frontend build output is produced here).

---

## Clone traffic

![Clone traffic](https://raw.githubusercontent.com/nikolareljin/stats/main/charts/wp-precommit-hooks.svg)

_Updated daily. Total and unique cloners over the last 14 days._
