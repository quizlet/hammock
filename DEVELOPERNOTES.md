## Heads-up

The library can run on hhvm 3 and may therefore use hhvm-autoload 1.
This version ought to be run with HHVM, not PHP.
Failing to do so will result in this error:

```
Fatal error: Uncaught Error: Class undefined: Facebook\AutoloadMap\IncludedRoots in /.../quizlet/hammock/vendor/hhvm/hhvm-autoload/bin/hh-autoload:86
```

If you're running on hhvm 4, use PHP to run hh-autoload like normal.

### For developing on hammock

If you want to develop (and test) hammock, you are going to need two things.

- hhvm 3.28 or greater, since hacktest doesn't work on hhvm 3.27 and below
- remove composer.json and rename composer.development.json to composer.json

The last one shouldn't be needed, but composer wants you to be able to install the dev deps when you use the library.
Even when you don't want / need the tests.
