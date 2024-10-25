<?php

namespace Illuminate\View\Compilers\Concerns;

trait CompilesIncludes
{






protected function compileEach($expression)
{
return "<?php echo \$__env->renderEach{$expression}; ?>";
}







protected function compileInclude($expression)
{
$expression = $this->stripParentheses($expression);

return "<?php echo \$__env->make({$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
}







protected function compileIncludeIf($expression)
{
$expression = $this->stripParentheses($expression);

return "<?php if (\$__env->exists({$expression})) echo \$__env->make({$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
}







protected function compileIncludeWhen($expression)
{
$expression = $this->stripParentheses($expression);

return "<?php echo \$__env->renderWhen($expression, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path'])); ?>";
}







protected function compileIncludeUnless($expression)
{
$expression = $this->stripParentheses($expression);

return "<?php echo \$__env->renderUnless($expression, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path'])); ?>";
}







protected function compileIncludeFirst($expression)
{
$expression = $this->stripParentheses($expression);

return "<?php echo \$__env->first({$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
}
}
