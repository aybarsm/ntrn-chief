<?php

namespace Illuminate\Support\Facades;

use Illuminate\Contracts\Auth\PasswordBroker;
















class Password extends Facade
{





const RESET_LINK_SENT = PasswordBroker::RESET_LINK_SENT;






const PASSWORD_RESET = PasswordBroker::PASSWORD_RESET;






const INVALID_USER = PasswordBroker::INVALID_USER;






const INVALID_TOKEN = PasswordBroker::INVALID_TOKEN;






const RESET_THROTTLED = PasswordBroker::RESET_THROTTLED;






protected static function getFacadeAccessor()
{
return 'auth.password';
}
}
