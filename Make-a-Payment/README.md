# Make a Payment

A form allowing WordPress website owners to "Make a Payment" on their account.


## Features

- Shortcode Driven - Put it anywhere
- Asynchronous 
- Minimal (super lightweight)
- Luhn Algorythm for CC Validation!
- Animated Spinner
- Success Message


## Technology

- AlpineJS
- WordPress REST API
- HTML5 / CSS


## Impllementation 

- Create an `inc` folder in your child theme
- Add `make-a-payment.php` to the folder
- Add `require_once get_stylesheet_directory() . '/inc/make-a-payment.php'` to your child theme's `function.php`
- Copy code from `style.css` to the CSS file of your child theme
- Update transaction keys
- Add shortcode `[payment-form]` to the page
- Test
- Comment out Sandbox URL
- Uncomment Production URL



## Requirements

- Authorize.NET Account
- API Login
- API Transaction Key

```PHP
/**
 * Authorize.NET API Endpoints & Authentication
 * All requests to the Authorize.net API are sent via the HTTP POST method to one of our API endpoint URLs.
 *
 * HTTP Request Method: POST
 *
 * Sandbox API Endpoint: https://apitest.authorize.net/xml/v1/request.api
 * Production API Endpoint: https://api.authorize.net/xml/v1/request.api
 *
 * XML Content-Type: text/xml
 * JSON Content-Type: application/json
 * 
 * API Schema (XSD): https://api.authorize.net/xml/v1/schema/AnetApiSchema.xsd
 *
 * Authentication
 * All calls to the Authorize.net API require merchant authentication. Sign up for a sandbox account to quickly get started.
 *   - https://developer.authorize.net/hello_world/sandbox.html
 *   - https://sandbox.authorize.net/
 *   - 
 *
 * Login: ciwg1337specops
 * Pass: z7Qwe7BDweZ5twQ7Ez
 * API Login: bizdev05 
 * Transaction Key: 4kJd237rZu59qAZd
 * 
 **/
```