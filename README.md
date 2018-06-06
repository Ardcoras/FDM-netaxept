# Netaxept API Library

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Build Status](https://api.travis-ci.com/fdmweb/FDM-netaxept.png?branch=master)](https://travis-ci.org/fdmweb/FDM-netaxept)
[![Latest Stable Version](https://poser.pugx.org/fdm/netaxept/version.png)](https://packagist.org/packages/fdm/netaxept)
[![Code Coverage](https://img.shields.io/codecov/c/github/fdmweb/FDM-netaxept.svg)](https://codecov.io/gh/fdmweb/FDM-netaxept)

This project provides a simple interface to NETS' Netaxept payment gateway.

## Using the library

```php
<?php

// Instantiate the API with the required merchantId, token, and a response factory
$responseFactory = new \FDM\Netaxept\Response\Factory();
$api = new \FDM\Netaxept\Api('merchantId', 'token', $responseFactory);

// Get a response object from the API, in this example, get a transaction.
$response = $api->getTransaction('transactionId');

// Retrieve properties on the transaction.
$status = $response->getTransactionStatus();
print_r($response->getOrderInformation());
```

## Customising the response factory.

The provided response classes only have methods for exposing the most common data. If
you have a requirement to retrieve other data, then you simply create a response class
that extends one of the existing, (or a completely new class that implements the
appropriate interface.) and implement your methods. Then provide these fully qualified
classnames to the constructor of the Factory() class.
```php
// Acme/Netaxept/Response/MyCustomQuery.php

use FDM\Netaxept\Response\Query;
class MyCustomQuery extends Query
{
    public function getQueryFinished()
    {
        return (string)$this->xml->QueryFinished;
    }
}

...

// In your file that instantiates the API object
$responseFactory = new Factory(Acme\Netaxept\Response\MyCustomQuery::class);
$api = new Api('merch', 'token', $responseFactory);
$response = $api->getTransaction('transactionid');
$finished = $response->getQueryFinished(); 
```

## Contributing

Some tools are provided to ease development. Clone the project and run
`make docker-start` to start a PHP Docker container. Run `make docker-shell` in order
to get a shell inside the container. Run `composer install` to install dependencies.
Run `make test` from inside the container to run tests, and `make codecheck` to make
sure your code follows standards.

## License

Copyright (c) Forenede Danske Motorejere (FDM). All rights reserved.

Licensed under the [MIT](LICENSE) License.  