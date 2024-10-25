<?php

namespace Illuminate\Http\Concerns;

use Illuminate\Support\Collection;

trait CanBePrecognitive
{






public function filterPrecognitiveRules($rules)
{
if (! $this->headers->has('Precognition-Validate-Only')) {
return $rules;
}

return Collection::make($rules)
->only(explode(',', $this->header('Precognition-Validate-Only')))
->all();
}






public function isAttemptingPrecognition()
{
return $this->header('Precognition') === 'true';
}






public function isPrecognitive()
{
return $this->attributes->get('precognitive', false);
}
}
