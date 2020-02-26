# Overview

Hammock is a stand-alone mocking library for the Hack language. At its core, it uses `fb_intercept`, which can intercept any global function or method and change its behavior. Hammock aims to provide APIs for mocking public, protected, private, and static methods - as well as global functions - with ease.

The key distinction made by Hammock is between _method containers_ and _functions_. Method containers comprise classes and objects. Functions comprise the methods themselves and global functions. Mocking a method container allows the developer to mock the behavior of any method within it. Hence, the mocking interface for method containers is called a _method mock container_. When a method is mocked through a method mock container, it instantiates a _function mock_, which is what actually mocks the method's behavior. A function mock is the unit of mocking within Hammock, since Hammock ultimately mocks functions.

Hammock also distinguishes between _class-level_ mocks and _object-level_ mocks. A class-level mock affects _all_ instances of a given class. It is useful when trying to override the behavior of a class method regardless of which object calls it. It is also useful when trying to override the behavior of static methods. On the other hand, an object-level mock only affects _one_ instance of a given class. It is useful when trying to override the behavior of just one instance without affecting the behavior of other instances. Note that static methods may _not_ be mocked through object-level mocks.

By virtue of using `fb_intercept`, Hammock is able to override the behavior of functions at runtime. This eliminates the need for dependency injection when mocking objects. That is, rather than creating a mock object that looks and feels the same as a real object, Hammock creates a mock object that _accompanies_ the real object. For the lifespan of the mock object (which ends at the end of the block), the real object's behavior is overridden.

# Installation

To install Hammock, run the command below

```
composer require --dev quizlet/hammock
```

# Advanced

## Accessing a Method Mock Through its Container

In order to access a method mock through its container, use `getMethodMock` on the method mock container:

```hack
using $classMock = Hammock\mock_class(MyClass::class);

$classMock->mockMethod('returnInput', $args ==> 1);

// Call `MyClass::returnInput` n times.

$methodMock = $classMock->getMethodMock('returnInput');

$methodMock->getNumCalls(); // n
```

## Passing Through to the Original Behavior

In order to pass through to the original function behavior within the mock callback, throw the `PassThroughException`:

```hack
using $functionMock = Hammock\mock_global_function('return_zero', $args ==> {
	if (boolval($args[0])) {
		throw new Hammock\Exceptions\PassThroughException();
	}

	return 1;
});

return_zero(false); // 1.
return_zero(true); // 0.
```

## Creating Mocks Through a Helper

In order to create a mock inside a helper function and return it, use the `<<__ReturnDisposable>>` attribute:

```hack
<<__ReturnDisposable>>
function helper(): Hammock\Disposables\FunctionMock {
	return Hammock\mock_global_function('return_zero', $args ==> 1);
}
```

# Warning

Direct usage of internal classes (especially the non-disposable ones) is strongly discouraged.

# Contributing
If you ever wanted to contribute to open source, now is the chance!

Please read [CONTRIBUTING.md](https://github.com/quizlet/hammock/blob/master/CONTRIBUTING.md) for details and the process for submitting pull requests.

# Versioning
We use GitHub for versioning. For the versions available, see the [tags on this repository](https://github.com/quizlet/hammock/tags).

# Contributors
Thanks to the following people who have contributed to this project:
- [Tyron Jung](https://github.com/tyronjung-quizlet)
- [Riya Dashoriya](https://github.com/riyadashoriya-qz)
- [Karoun Kasraie](https://github.com/karoun)
- [Andrew Sutherland](https://github.com/asuth)
- [Josh Rai](https://github.com/joshrai)
- [Shaobo Sun](https://github.com/shaobos)

# Acknowledgements
- [Turadg Aleahmad](https://github.com/turadg)
- [Sean Young](https://github.com/syoung-quizlet)

# Contact
Please reach out to tyron.jung@quizlet.com or riya.dashoriya@quizlet.com if you have any questions.

# License
[MIT](https://github.com/quizlet/hammock/blob/master/LICENSE)
