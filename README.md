### Prostor Discount Module

---

## Installation

### 1. Add Repository to `composer.json`

Add the following entry under the `repositories` section of your Magento project’s `composer.json`:

```json
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/vsevolod-tarasov/prostor-test"
  }
]
```

### Install the Package and Enable the Module

Run the following commands in your Magento root directory:

```bash
composer require prostor/cum-discount
php bin/magento module:enable Prostor_CumDiscount
php bin/magento setup:upgrade
bin/magento indexer:reindex
php bin/magento cache:clean
```

### After Installation

Configure the module in the Magento Admin panel:  
**Stores → Configuration → Sales → Prostor Cumulative Discount**

---

## Configuration

### Admin UI Path
Admin → Stores → Configuration → Sales → Prostor Cumulative Discount


### Configuration Fields and Config Paths

| **Field Label**        | **Config Path**                                  | **Description** |
|--------------------------|--------------------------------------------------|-----------------|
| Enable Discount          | `sales/prostor_cumdiscount/active`              | Globally enables or disables the module |
| Enable Test Mode         | `sales/prostor_cumdiscount/test_mode`           | Returns static dummy data instead of calling the live API |
| API Base URL             | `sales/prostor_cumdiscount/api_url`             | Base URL for the external cumulative discount API |
| API Token                | `sales/prostor_cumdiscount/token`               | Secret token for API authentication (stored encrypted) |
| API Timeout (sec)        | `sales/prostor_cumdiscount/timeout`             | Connection timeout for API requests in seconds |
| Cache TTL (minutes)      | `sales/prostor_cumdiscount/cache_ttl`           | Time-to-live for cached API responses (minutes) |
| Discount Thresholds      | `sales/prostor_cumdiscount/thresholds`          | Mapping of spent amount → discount percentage |
| Enable Debug             | `sales/prostor_cumdiscount/debug`               | Enables detailed API request/response logging |


## Redis Integration (Optional)

If Redis is **not** the default cache backend and you want to use Redis for caching API responses,  
add or extend the cache section in `app/etc/env.php`. Insert the Redis configuration under the `frontend` key.

### Example Snippet to Add into `env.php`

```php
'cache' => [
    'frontend' => [
        // ... existing configuration
        'redis' => [
            'backend' => 'Magento\\Framework\\Cache\\Backend\\Redis',
            'backend_options' => [
                'server' => '127.0.0.1',
                'port' => '6379',
                'database' => '5', // use a unique DB index for this instance
                'compress_data' => '0',
                'password' => null,
                'prefix' => 'magento_redis_5_'
            ]
        ],
        // ... rest of the frontend configuration
    ]
]
```
###Notes

Ensure the chosen Redis database index does not conflict with other applications on the same Redis server.

If your environment already configures Redis differently, adapt the snippet accordingly.

## Running Tests

To run tests for the extension, use the following command:

```bash
./vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/Prostor/CumDiscount/Test/Unit
```



To run test for extension use

 ./vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/Prostor/CumDiscount/Test/Unit
