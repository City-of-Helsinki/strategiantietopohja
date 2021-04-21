# HELSTRA-theme

## Requirements

This theme requires Drupal core >= 8.8.0.

Requirements for developing:
- [NodeJS ( ^ 12.18 )](https://nodejs.org/en/)
- [NPM](https://npmjs.com/)

## Commands

Install v12 of NodeJS by running:
  `curl -sL https://deb.nodesource.com/setup_12.x | sudo -E bash -`
  `sudo apt install -y nodejs`

Install local packages with
 `npm install -f --cache /tmp/empty-cache`
 `cp -r node_modules/helsinki-design-system/packages/core/src/svg/ src/icons/hds`

Watch and compile styles with
 `npm run dev`

Debug failures to run with
 `nvm use 12`
 `npm rebuild node-sass`
