<?php

Validator::extend('recaptcha', function($attribute, $recaptcha, $params)
{
    return Simplerecaptcha::check($recaptcha);
});