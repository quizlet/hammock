## Heads-up

The library requires hhvm 3.30 and will therefore use hhvm-autoload 1.
This version ought to be run with HHVM, not PHP.
Failing to do so will result in this error:
```
Fatal error: Uncaught Error: Class undefined: Facebook\AutoloadMap\IncludedRoots in /.../quizlet/hammock/vendor/hhvm/hhvm-autoload/bin/hh-autoload:86
```
