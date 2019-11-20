# Disposable Mocks

Mocks are useful when trying to override a function's behavior with a stub, as well as track the calls into the overridden function. Disposable ensures that all mocks are destroyed at the end of the block in which they are created. When a mock is destroyed, the original behavior of the mocked function is restored.

## Class mock
API: `Hammock\mock_class`
- Creates a method mock container for a class.
- `@param classname<T> $className`
- `@return MethodMockContainer`

<details><summary>Example</summary>
<p>

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
</details>


## Object mock
API: `Hammock\mock_object`
- Creates a method mock container for an object.
- `@param T $object`
- `@return MethodMockContainer`

<details><summary>Example</summary>
<p>

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
</details>

## Class method mock
API: `Hammock\mock_class_method`
- Creates a class-level function mock that overrides the class method behavior with the callback.
- `@param classname<T> $className`
- `@param string $methodName`
- `@param MockCallback $callback`
- `@return FunctionMock`

<details><summary>Example</summary>
<p>

```hack
// Shortcut for mocking a class method.
using $methodMock = Hammock\mock_class_method(MyClass::class, 'returnInput', $args ==> 1);

$firstObject = new MyClass();
$secondObject = new MyClass();

$firstObject->returnInput(0); // 1
$secondObject->returnInput(0); // 1

$methodMock->getNumCalls(); // 2
```
</details>

## Object method mock
API: `Hammock\mock_object_method`
- Creates an object-level function mock that overrides the object method behavior with the callback.
- `@param T $object`
- `@param string $methodName`
- `@param MockCallback $callback`
- `@return FunctionMock`

<details><summary>Example</summary>
<p>

```hack
// Shortcut for mocking an object method.
using $methodMock = Hammock\mock_object_method($object, 'returnInput', $args ==> 1);

$object->returnInput(0); // 1

$methodMock->getNumCalls(); // 1
```
</details>

## Global function mock
API: `Hammock\mock_global_function`
- Creates a function mock that overrides the global function behavior with the callback.
- `@param string $globalFunctionName`
- `@param MockCallback $callback`
- `@return FunctionMock`

<details><summary>Example</summary>
<p>

```hack
using $functionMock = Hammock\mock_global_function('return_input', $args ==> 1);

return_input(0); // 1

$functionMock->getNumCalls(); // 1
```
</details>