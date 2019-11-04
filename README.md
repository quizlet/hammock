# Overview

Hammock is a stand-alone mocking library for the Hack language. At its core, it uses `fb_intercept`, which can intercept any global function or method and change its behavior. Hammock aims to provide APIs for mocking public, protected, private, and static methods - as well as global functions - with ease.

The key distinction made by Hammock is between _method containers_ and _functions_. Method containers comprise classes and objects. Functions comprise the methods themselves and global functions. Mocking a method container allows the developer to mock the behavior of any method within it. Hence, the mocking interface for method containers is called a _method mock container_. When a method is mocked through a method mock container, it instantiates a _function mock_, which is what actually mocks the method's behavior. A function mock is the unit of mocking within Hammock, since Hammock ultimately mocks functions.

Hammock also distinguishes between _class-level_ mocks and _object-level_ mocks. A class-level mock affects _all_ instances of a given class. It is useful when trying to override the behavior of a class method regardless of which object calls it. It is also useful when trying to override the behavior of static methods. On the other hand, an object-level mock only affects _one_ instance of a given class. It is useful when trying to override the behavior of just one instance without affecting the behavior of other instances. Note that static methods may _not_ be mocked through object-level mocks.

By virtue of using `fb_intercept`, Hammock is able to override the behavior of functions at runtime. This eliminates the need for dependency injection when mocking objects. That is, rather than creating a mock object that looks and feels the same as a real object, Hammock creates a mock object that _accompanies_ the real object. For the lifespan of the mock object (which ends at the end of the block), the real object's behavior is overridden.

# Examples

If you learn better by reading the API documentation instead of going through examples, you can jump straight to the [API](#api) section.

## Namespace

```hack
use namespace Hammock;
```

## Mocks

Mocks are useful when trying to override a function's behavior with a stub, as well as track the calls into the overridden function.

Class mock:

```hack
// Suppose that `MyClass::returnInput` simply returns the input.
$firstObject = new MyClass();
$firstObject->returnInput(0); // 0

// Create a method mock container for `MyClass`.
using $classMock = Hammock\mock_class(MyClass::class);

// Mock the `returnInput` method to return `1` at all times.
$methodMock = $classMock->mockMethod('returnInput', $args ==> 1);

$firstObject->returnInput(0); // 1
$firstObject->returnInput(2); // 1

// The class-level mock affects all instances of `MyClass`.
$secondObject = new MyClass();

$secondObject->returnInput(0); // 1
$secondObject->returnInput(2); // 1

$methodMock->getNumCalls(); // 4

// It is also possible to mock static methods.
$staticMethodMock = $classMock->mockMethod('staticReturnInput', $args ==> 1);

MyClass::staticReturnInput(0); // 1

$staticMethodMock->getNumCalls(); // 1
```

Object mock:

```hack
// Create instances of an unmocked class.
$firstObject = new MyClass();
$secondObject = new MyClass();

// Create a method mock container for one of the objects.
using $firstObjectMock = Hammock\mock_object($firstObject);

$methodMock = $firstObjectMock->mockMethod('returnInput', $args ==> 1);

// The object-level mock only affects that one object.
$firstObject->returnInput(0); // 1
$secondObject->returnInput(0); // 0

$methodMock->getNumCalls(); // 1
```

Class method mock:

```hack
// Shortcut for mocking a class method.
using $methodMock = Hammock\mock_class_method(MyClass::class, 'returnInput', $args ==> 1);

$firstObject = new MyClass();
$secondObject = new MyClass();

$firstObject->returnInput(0); // 1
$secondObject->returnInput(0); // 1

$methodMock->getNumCalls(); // 2
```

Object method mock:

```hack
// Shortcut for mocking an object method.
using $methodMock = Hammock\mock_object_method($object, 'returnInput', $args ==> 1);

$object->returnInput(0); // 1

$methodMock->getNumCalls(); // 1
```

Global function mock:

```hack
using $functionMock = Hammock\mock_global_function('return_input', $args ==> 1);

return_input(0); // 1

$functionMock->getNumCalls(); // 1
```

## Spies

Spies are useful when trying to track the calls into a function without affecting its behavior.

Class method spy:

```hack
using $classMock = Hammock\mock_class(MyClass::class);

$methodSpy = $classMock->spyMethod('returnInput');

$firstObject = new MyClass();
$secondObject = new MyClass();

$firstObject->returnInput(0); // 0
$secondObject->returnInput(0); // 0

$methodSpy->getNumCalls(); // 2
```

Class method spy (shortcut):

```hack
using $methodSpy = Hammock\spy_class_method(MyClass::class, 'returnInput');

$firstObject = new MyClass();
$secondObject = new MyClass();

$firstObject->returnInput(0); // 0
$secondObject->returnInput(0); // 0

$methodSpy->getNumCalls(); // 2
```

Object method spy:

```hack
using $objectMock = Hammock\mock_object($object);

$methodSpy = $objectMock->spyMethod('returnInput');

$object->returnInput(0); // 0

$methodSpy->getNumCalls(); // 1
```

Object method spy (shortcut):

```hack
using $methodSpy = Hammock\spy_object_method($object, 'returnInput');

$object->returnInput(0); // 0

$methodSpy->getNumCalls(); // 1
```

Global function spy:

```hack
using $functionSpy = Hammock\spy_global_function('return_input');

return_input(0); // 0

$functionSpy->getNumCalls(); // 1
```

## No-ops

No-ops are useful when trying to override a function's behavior with no-op.

Class method noop:

```hack
using $classMock = Hammock\mock_class(MyClass::class);

$methodNoop = $classMock->noopMethod('returnInput');

$firstObject = new MyClass();
$secondObject = new MyClass();

$firstObject->returnInput(0); // null
$secondObject->returnInput(0); // null

$methodNoop->getNumCalls(); // 2
```

Class method noop (shortcut):

```hack
using $methodNoop = Hammock\noop_class_method(MyClass::class, 'returnInput');

$firstObject = new MyClass();
$secondObject = new MyClass();

$firstObject->returnInput(0); // null
$secondObject->returnInput(0); // null

$methodNoop->getNumCalls(); // 2
```

Object method noop:

```hack
using $objectMock = Hammock\mock_object($object);

$methodNoop = $objectMock->noopMethod('returnInput');

$object->returnInput(0); // null

$methodNoop->getNumCalls(); // 1
```

Object method noop (shortcut):

```hack
using $methodNoop = Hammock\noop_object_method($object, 'returnInput');

$object->returnInput(0); // null

$methodNoop->getNumCalls(); // 1
```

Global function noop:

```hack
using $functionNoop = Hammock\noop_global_function('return_input');

return_input(0); // null

$functionNoop->getNumCalls(); // 1
```

# API

The public APIs intended for use are as follows:

- `Hammock\mock_class`
	- Creates a method mock container for a class.
	- `@param classname<T> $className`
	- `@return MethodMockContainer`

- `Hammock\mock_object`
	- Creates a method mock container for an object.
	- `@param T $object`
	- `@return MethodMockContainer`

- `Hammock\mock_class_method`
	- Creates a class-level function mock that overrides the class method behavior with the callback.
	- `@param classname<T> $className`
	- `@param string $methodName`
	- `@param MockCallback $callback`
	- `@return FunctionMock`

- `Hammock\mock_object_method`
	- Creates an object-level function mock that overrides the object method behavior with the callback.
	- `@param T $object`
	- `@param string $methodName`
	- `@param MockCallback $callback`
	- `@return FunctionMock`

- `Hammock\mock_global_function`
	- Creates a function mock that overrides the global function behavior with the callback.
	- `@param string $globalFunctionName`
	- `@param MockCallback $callback`
	- `@return FunctionMock`

- `Hammock\spy_class_method`
	- Creates a class-level function spy that does not change the class method behavior but still tracks all of the calls.
	- `@param classname<T> $className`
	- `@param string $methodName`
	- `@return FunctionMock`

- `Hammock\spy_object_method`
	- Creates an object-level function spy that does not change the object method behavior but still tracks all of the calls.
	- `@param T $object`
	- `@param string $methodName`
	- `@return FunctionMock`

- `Hammock\spy_global_function`
	- Creates a function spy that does not change the global function behavior but still tracks all of the calls.
	- `@param string $globalFunctionName`
	- `@return FunctionMock`

- `Hammock\noop_class_method`
	- Creates a class-level function mock that overrides the class method behavior with no-op.
	- `@param classname<T> $className`
	- `@param string $methodName`
	- `@return FunctionMock`

- `Hammock\noop_object_method`
	- Creates an object-level function mock that overrides the object method behavior with no-op.
	- `@param T $object`
	- `@param string $methodName`
	- `@return FunctionMock`

- `Hammock\noop_global_function`
	- Creates a function mock that overrides the global function behavior with no-op.
	- `@param string $globalFunctionName`
	- `@return FunctionMock`

The `MockCallback` type is simply `(function(vec<mixed>): mixed)`. The only parameter to `MockCallback` is a vector of the intercepted arguments that were passed into the mocked function. Since each intercepted argument loses its original type information, it will have to be properly type-casted to be used within the callback.

Note that all Hammock APIs return either of two mock interfaces: `MethodMockContainer` or `FunctionMock`. That is, they all return a _disposable_ object and therefore require using the `using` statement. This ensures that all mocks are destroyed at the end of the block in which they are created. When a mock is destroyed, the original behavior of the mocked function is restored. The [Advanced](#advanced) section details ways to work around this constraint if it is found to be too limiting.

A `MethodMockContainer` creates and encapsulates a set of function mocks and deactivates them all during disposal. A `FunctionMock` is responsible for mocking exactly one function, and deactivates during disposal. The two mock interfaces expose the following APIs:

- `MethodMockContainer` (implements the `IMethodMockContainer` interface)

	- `mockMethod`
		- Creates a function mock that overrides the method behavior with the callback.
		- `@param string $methodName`
		- `@param MockCallback $callback`
		- `@return IFunctionMock`

	- `spyMethod`
		- Creates a function spy that does not change the method behavior but still tracks all of the calls.
		- `@param string $methodName`
		- `@return IFunctionMock`

	- `noopMethod`
		- Creates a function mock that overrides the method behavior with no-op.
		- `@param string $methodName`
		- `@return IFunctionMock`

	- `getMethodMock`
		- Gets a function mock/spy/noop for a method.
		- `@param string $methodName`
		- `@return IFunctionMock`

- `FunctionMock` (implements the `IFunctionMock` interface)

	- `getNumCalls`
		- Gets the number of calls to the mocked function.
		- `@return int`

	- `getArgsForCall`
		- Gets the vector of arguments from the i-th call to the mocked function.
		- `@param int $i`
		- `@return vec<mixed>`

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
