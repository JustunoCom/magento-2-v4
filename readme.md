# Justuno M2 Plugin for Magento 2

This plugin integrates Justuno with your Magento 2 store, allowing you to use Justuno's marketing and conversion optimization tools.

## Installation

### Via Composer (Recommended)

1. In your Magento 2 root directory, run the following command:
```
composer require justuno/magento-2-v4
```

2. Enable the module:
```
bin/magento module:enable Justuno_M2
```

3. Run the Magento setup upgrade:
```
bin/magento setup:upgrade
```

4. Compile the code (in production mode):
```
bin/magento setup:di:compile
```

5. Deploy static content (in production mode):
```
bin/magento setup:static-content:deploy
```

### Manual Installation

1. Download the plugin files.
2. Create a directory `app/code/Justuno/M2/` in your Magento installation.
3. Copy the plugin files into this directory.
4. Enable the module:
```
bin/magento module:enable Justuno_M2
```

5. Run the Magento setup upgrade:
```
bin/magento setup:upgrade
```

6. Compile the code (in production mode):
```
bin/magento setup:di:compile
```

7. Deploy static content (in production mode):\
```
bin/magento setup:static-content:deploy
```

## Setup and Configuration

### Single-store install

1. Log in to your Magento admin panel.
2. Navigate to Stores > Configuration > Justuno > General Settings.
3. Enter your Justuno API Key.
4. Select your preferred subdomain or justone.ai.
5. Click on "Generate Token" to create a token for API authentication.
6. Save the configuration.
7. Paste the token into the Justuno portal when connecting the Magento app.

### Multi-store install

For installations with more than one website (Magento "website" scope), each
website must have its own Justuno integration so that product feeds and orders
are kept separate. The plugin uses the auth token to figure out which website
an incoming API request belongs to, so the only requirement is that each
website is configured with its own token.

1. Log in to your Magento admin panel.
2. Navigate to Stores > Configuration > Justuno > General Settings.
3. In the **scope dropdown** at the top-left of the page, switch from
   "Default Config" to the website you want to connect.
4. Enter the Justuno API Key for that website and click **Generate Token**.
   The token is saved at the website scope.
5. Save the configuration.
6. In the Justuno portal, connect the Magento app for that site using this
   token. The plugin will scope `/V1/justuno/products` and
   `/V1/justuno/orders` responses to that website automatically — no extra
   site ID configuration is needed.
7. Repeat steps 3–6 for each additional website.

You can also pass `site_id=<websiteId>` as a query-string parameter on
`/V1/justuno/products` and `/V1/justuno/orders` to override the website that
gets returned (useful when sharing a single token across scopes). Token-based
scoping is preferred — it requires no portal-side changes.

## Usage

Once installed and configured, the plugin will automatically:

-   Add Justuno tracking scripts to your store
-   Track product views, add to cart events, and order placements
-   Provide API endpoints for Justuno to fetch product and order data

## Uninstallation

1. Disable the module:

```
bin/magento module:disable Justuno_M2
```

2. Remove the module files:

-   If installed via Composer:
    ```
    composer remove justuno/module-m2
    ```
-   If installed manually, remove the `app/code/Justuno/M2/` directory.

3. Remove the module from the database:

```
bin/magento setup:upgrade
```

4. Clean up the compiled code and generated files:

```
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
```

## License

This plugin is released under the [OSL-3.0 License](https://opensource.org/licenses/OSL-3.0).
