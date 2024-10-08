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

1. Log in to your Magento admin panel.
2. Navigate to Stores > Configuration > Justuno > General Settings.
3. Enter your Justuno API Key.
4. Select your preferred subdomain or justone.ai.
5. Click on "Generate Token" to create a WooCommerce-compatible token for API authentication.
6. Save the configuration.

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
