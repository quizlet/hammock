# Overview

Hammock is a stand-alone mocking library for the Hack language. At its core, it uses `fb_intercept`, which can intercept any function and change its behavior. Hammock aims to provide APIs for mocking public methods and global functions with ease. While it is also possible to mock protected and private methods, it is generally frowned upon to do so. Here are some of Hammock's key features:

- Block-scoped mocking, which automatically restores the original behavior of the mocked function at the end of the block.
- Tracking the intercepted arguments and number of calls into mocked functions.
- Spying on functions without altering their behavior.

# Installation

```bash
composer require --dev quizlet/hammock
```

# Usage

Mock an object's method:

```hack
$dog = new Dog();

$dog->fetch('ball') === 'ball'; // true

using Hammock\mock_object_method($dog, 'fetch', $args ==> 'frisbee');

$dog->fetch('ball') === 'frisbee'; // true
```

Same thing, but using a block scope:

```hack
$dog = new Dog();

using (Hammock\mock_object_method($dog, 'fetch', $args ==> 'frisbee')) {
	$dog->fetch('ball') === 'frisbee'; // true
} // Original behavior is restored at the end of the `using` block.

$dog->fetch('ball') === 'ball'; // true
```

Use the intercepted arguments and get the number of calls:

```hack
$dog = new Dog();

using $fetchMock = Hammock\mock_object_method($dog, 'fetch', $args ==> {
	// Each intercepted argument must be type-asserted.
	$arg = strval($args[0]);

	if ($arg === 'ball') {
		return 'frisbee';
	}
	
	return $arg;
});

$dog->fetch('ball') === 'frisbee'; // true
$dog->fetch('bone') === 'bone'; // true

// The arguments for each intercepted call can be retrieved later.
$fetchMock->getArgsForCall(0) === vec['ball']; // true
$fetchMock->getArgsForCall(1) === vec['bone']; // true

// The number of calls from the time the function was mocked.
$fetchMock->getNumCalls() === 2; // true
```

Spy on an object's method without altering its behavior:

```hack
$dog = new Dog();

using $fetchSpy = Hammock\spy_object_method($dog, 'fetch');

$dog->fetch('ball') === 'ball'; // true

$fetchSpy()->getNumCalls() === 1; // true
```

The full API documentation can be found in [MAIN_API.md](https://github.com/quizlet/hammock/blob/master/MAIN_API.md).

# Contributing

If you ever wanted to contribute to an open-source project, now is the chance! Please read [CONTRIBUTING.md](https://github.com/quizlet/hammock/blob/master/CONTRIBUTING.md) to understand the process for submitting a pull request.

# Acknowledgements

Thanks to the following people who have contributed to Hammock:
- [Tyron Jung](https://github.com/tyronjung-quizlet)
- [Riya Dashoriya](https://github.com/riyadashoriya-qz)
- [Lexidor](https://github.com/lexidor)
- [Karoun Kasraie](https://github.com/karoun)
- [Andrew Sutherland](https://github.com/asuth)
- [Josh Rai](https://github.com/joshrai)
- [Shaobo Sun](https://github.com/shaobos)
- [Turadg Aleahmad](https://github.com/turadg)
- [Sean Young](https://github.com/syoung-quizlet)

# Contact

Please reach out to tyron.jung@quizlet.com or riya.dashoriya@quizlet.com if you have any questions.
