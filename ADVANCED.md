# Overview

Here are some advanced use cases for Hammock.

# Passing through to the original behavior

In order to pass through to the original function behavior within the mock callback, throw the `PassThroughException`:

```hack
square(2) === 4; // true
square(3) === 9; // true

using Hammock\mock_global_function('square', $args ==> {
	$arg = intval($args[0]);

	if ($arg % 2 === 0) {
		// Default to the original behavior for even numbers.
		throw new Hammock\Exceptions\PassThroughException();
	}

	// Return 0 for odd numbers.
	return 0;
});

square(4) === 16; // true
square(5) === 0; // true
```

# Creating mocks through a helper

Sometimes, it might be convenient to set up a complicated mock inside a helper function. In order to create a mock inside a helper function and return it, use the `<<__ReturnDisposable>>` attribute:

```hack
<<__ReturnDisposable>>
function helper(): Hammock\Disposables\FunctionMock {
	return Hammock\mock_global_function('square', $args ==> 0);
}
```
