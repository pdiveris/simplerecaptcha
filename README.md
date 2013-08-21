## SimpleRecaptcha for Laravel 4
## Installation
Open your composer.json and add the next code
```json
{
	"require": {
	    "laravel/framework": "4.0.*",
	    "unodepiera/simplerecaptcha": "dev-master"
	},
	"minimum-stability": "dev"
}
```
Update your packages with ```composer update``` or install with ```composer install```.
## Usage
Find the providers key in app/config/app.php and register the Simplecart Service Provider.
```json
	'providers' => array(
        //...
        'Unodepiera\Simplerecaptcha\SimplerecaptchaServiceProvider'
    )
```
Find the aliases key in app/config/app.php.
```json
	'aliases' => array(
        //...
        'Simplerecaptcha' => 'Unodepiera\Simplerecaptcha\Facades\Simplerecaptcha',
    )
```

Publish config with this command. 

```$ php artisan asset:publish unodepiera/simplerecaptcha```

## Example Usage SimpleRecaptcha

Settings
```php
return array(


    /*
    | Set the public keys as provided by reCAPTCHA.
    */

    'public_key'    =>      '',


    /*
    | Set the public keys as provided by reCAPTCHA.
    */

    'private_key'   =>      '',


    /*
    | Set the the theme you want for the reCAPTCHA.
    | Options: red, white, clean and blackglass.
    */

    'theme'         =>      'clean',


    /*
    *
    *
    | Options buttons you want for reCAPTCHA.
    *
    *
    */

    /*
    | Set the the text you want for field text reCAPTCHA.
    */

    'textfield'     =>      'Write what you see',

    /*
    | Set the the text you want for field sound text reCAPTCHA.
    */

    'soundfield'    =>      'Write what you hear',


    /*
    | Set the the text you want for title button visual reCAPTCHA.
    */
    'visual_challenge'  =>      'Visual mode',


    /*
    | Set the the text you want for title button audio reCAPTCHA.
    */
    'audio_challenge'   =>      'Audio mode',


    /*
    | Set the the text you for title button reload reCAPTCHA.
    */
    'refresh_btn'   =>      'Ask two new words',


    /*
    | Set the the text you want for title button help reCAPTCHA.
    */
    'help_btn'  =>      'Help',


    /*
    | Set the message incorrect text reCAPTCHA.
    */
    'incorrect_try_again'   =>      'Incorrect. Try again',

);
```
## Usage
```php
Route::get("form", function()
{
    
    $html = "<form action='check' method='POST'>";
    $html.= Simplerecaptcha::recaptcha_get_html();
    $html.= "<input type='submit'>";
    
    echo $html; 
});

Route::post("check", function()
{
    $rules =  array('recaptcha_response_field' => array('required', 'recaptcha'));
    $validator = Validator::make(Input::all(), $rules);
    if($validator->fails())
    {
        echo "fails";
    }else{
        echo "success";
    }
});
```

## Visit me

* [Visit me](http://uno-de-piera.com)
* [SimpleCart on Packagist](https://packagist.org/packages/unodepiera/simplecart)
* [License](http://www.opensource.org/licenses/mit-license.php)
* [Laravel website](http://laravel.com)-