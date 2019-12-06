## Heads-up

The library requires hhvm 3.30 and will therefore use hhvm-autoload 1.
This version ought to be run with HHVM, not PHP.
Failing to do so will result in this error:
```
Fatal error: Uncaught Error: Class undefined: Facebook\AutoloadMap\IncludedRoots in /.../quizlet/hammock/vendor/hhvm/hhvm-autoload/bin/hh-autoload:86
```

## Required patches to composer packages

### Typechecker

Composer requires two versions of the hsl and hsl-experimental that both define SecureRandom.
Delete the file in /vendor/hhvm/hsl-experimetal/src/random/\*.php

In vendor/hhvm/hhast/src/\_\_Private/LintRun.php
HHAST checks the truthyness of a traversable, vec($errors) first on line 105, like so.
```
$errors = await $linter->getLintErrorsAsync();
$errors = vec($errors);
```

In vendor/facebook/fbexpect/src/ExpectObj.php
fbexpect toBePHPEqualWithNANEqual uses is\_float, instead of `is float`, so is\_nan() is a type error, use `is float` on line 83, like so.
```
is_float($expected) &&
$actual is float &&
\is_nan($expected) &&
\is_nan($actual)
```

### Runtime

In vendor/hhvm/hhast/src/SchemaVersionError.php
HHAST divides your HHVM\_VERSION\_ID by 10000 and 100 using `as int`.
Use `\intdiv()` or `Math\int_div()` like so.
```
"AST version mismatch: expected '%s' (%d.%d.%d), but got '%s",
SCHEMA_VERSION,
\intdiv(HHVM_VERSION_ID, 10000) as int,
\intdiv(HHVM_VERSION_ID, 100) as int % 100,
HHVM_VERSION_ID % 100,
$version,
```

In vendor/hhvm/hhast/src/entrypoints.php
HHAST Checks your AST version and throws this error:
```
A linter threw an exception:
Linter: Facebook\HHAST\Linters\AsyncFunctionAndMethodLinter
File: /.../quizlet/hammock/src/Persistent/PersistentMockRegistry.php
Exception: Facebook\HHAST\SchemaVersionError
Message: In file "! no file !": AST version mismatch: expected '2018-07-19-0001' (3.28.1), but got '2018-10-30-0001
Trace:
  #0 /.../quizlet/hammock/vendor/hhvm/hhast/src/entrypoints.php(124): Facebook\HHAST\from_json()
  #1 /.../quizlet/hammock/vendor/hhvm/hhast/src/Linters/ASTLinter.php(30): Facebook\HHAST\from_code_async()
  ...
  #16 /.../quizlet/hammock/vendor/hhvm/hhast/bin/hhast-lint(36): Facebook\CLILib\CLIBase::main()
  #17 {main}
```

Comment out line 21 like so.
```
// throw new SchemaVersionError($file ?? '! no file !', $version);
```

## Configuration

HHAST will have some issues with parsing, so DON'T blindly run hhast.
The autofixes it presents are not safe.