# Overview

Hammock is a stand-alone mocking library for the Hack language. At its core, it uses `fb_intercept`, which can intercept any global function or method and change its behavior. Hammock aims to provide APIs for mocking public, protected, private, and static methods - as well as global functions - with ease. The mock classes implement the [`IDisposable`](https://docs.hhvm.com/hack/reference/interface/IDisposable/) interface, so that [`using`](https://docs.hhvm.com/hack/statements/using) statements can automatically restore the original behavior of mocked functions at the end of the block scope.

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

Use the block scope:

```hack
$dog = new Dog();

using (Hammock\mock_object_method($dog, 'fetch', $args ==> 'frisbee')) {
	$dog->fetch('ball') === 'frisbee'; // true
} // Original behavior is restored at the end of the `using` block.

$dog->fetch('ball') === 'ball'; // true
```

Get the number of calls to the mock function:

```hack
$dog = new Dog();

using $fetchMock = Hammock\mock_object_method($dog, 'fetch', $args ==> 'frisbee');

$dog->fetch('ball');

$fetchMock->getNumCalls() === 1; // true
```

Spy on an object's method without altering the behavior:

```hack
$dog = new Dog();

using $fetchSpy = Hammock\spy_object_method($dog, 'fetch');

$dog->fetch('ball') === 'ball'; // true

$fetchSpy()->getNumCalls() === 1; // true
```

The full API documentation can be found in [API.md](https://github.com/quizlet/hammock/blob/master/API.md).

# Contributing

If you ever wanted to contribute to open-source, now is the chance! Please read [CONTRIBUTING.md](https://github.com/quizlet/hammock/blob/master/CONTRIBUTING.md) to understand the process for submitting pull requests.

# Acknowledgements

Thanks to the following people who have contributed to Hammock:
- [Tyron Jung](https://github.com/tyronjung-quizlet)
- [Riya Dashoriya](https://github.com/riyadashoriya-qz)
- [Karoun Kasraie](https://github.com/karoun)
- [Andrew Sutherland](https://github.com/asuth)
- [Josh Rai](https://github.com/joshrai)
- [Shaobo Sun](https://github.com/shaobos)
- [Turadg Aleahmad](https://github.com/turadg)
- [Sean Young](https://github.com/syoung-quizlet)

# Contact

Please reach out to tyron.jung@quizlet.com or riya.dashoriya@quizlet.com if you have any questions.
