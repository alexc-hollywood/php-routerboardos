# Routerboard API PHP Bridge

This experimental package provides methods for connecting, authenticating, and fetching interface information directly from a Routerboard device over a TCP socket.

## Usage

### Basic Connection

```php
use RouterboardOS\Services\API\Bridge;

$bridge = new Bridge ('your_routerboard_host', 8728, 'your_username', 'your_password');

try 
{
	if ($bridge->connect()->login())
	{
		// do stuff
	}
}
catch (Throwable $e)
{
	// socket exception
}
```

### Using Class Components

```php
use RouterboardOS\Services\API\Bridge;
use RouterboardOS\Services\API\Commands\Network\Interfaces;

$bridge = new Bridge ('your_routerboard_host', 8728, 'your_username', 'your_password');

try 
{
	if ($bridge->connect()->login())
	{
		var_dump (
			new Interfaces ($bridge)->data()
		);
	}
}
catch (Throwable $e)
{
	// socket exception
}