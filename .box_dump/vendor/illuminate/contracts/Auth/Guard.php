<?php

namespace Illuminate\Contracts\Auth;

interface Guard
{





public function check();






public function guest();






public function user();






public function id();







public function validate(array $credentials = []);






public function hasUser();







public function setUser(Authenticatable $user);
}
