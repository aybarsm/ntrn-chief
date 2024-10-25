<?php

namespace Illuminate\Contracts\Auth;

interface StatefulGuard extends Guard
{







public function attempt(array $credentials = [], $remember = false);







public function once(array $credentials = []);








public function login(Authenticatable $user, $remember = false);








public function loginUsingId($id, $remember = false);







public function onceUsingId($id);






public function viaRemember();






public function logout();
}
