# Disposable Mocks

Mocks are useful when trying to override a function's behavior with a stub, as well as track the calls into the overridden function. Disposable ensures that all mocks are destroyed at the end of the block in which they are created. When a mock is destroyed, the original behavior of the mocked function is restored.

## Class mock
Creates a method mock container for a class.

API: `Hammock\mock_class`
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
Creates a method mock container for an object.

API: `Hammock\mock_object`
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
Creates a class-level function mock that overrides the class method behavior with the callback.

API: `Hammock\mock_class_method`
- `@param classname<T> $className`
- `@param string $methodName`
- `@param MockCallback $callback`
- `@return FunctionMock`

<details><summary>Example</summary>
<p>

```hack
using $methodMock = Hammock\mock_class_method(MyClass::class, 'returnInput', $args ==> 1);

$firstObject = new MyClass();
$secondObject = new MyClass();

$firstObject->returnInput(0); // 1
$secondObject->returnInput(0); // 1

$methodMock->getNumCalls(); // 2
```
</details>

## Object method mock
Creates an object-level function mock that overrides the object method behavior with the callback.

API: `Hammock\mock_object_method`
- `@param T $object`
- `@param string $methodName`
- `@param MockCallback $callback`
- `@return FunctionMock`

<details><summary>Example</summary>
<p>

```hack
using $methodMock = Hammock\mock_object_method($object, 'returnInput', $args ==> 1);

$object->returnInput(0); // 1

$methodMock->getNumCalls(); // 1
```
</details>

## Global function mock
Creates a function mock that overrides the global function behavior with the callback.

API: `Hammock\mock_global_function`
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

# Spies

Spies are useful when trying to track the calls into a function without affecting its behavior.

## Class method spy
Creates a class-level function spy that does not change the class method behavior but still tracks all of the calls.

API: `Hammock\spy_class_method`
- `@param classname<T> $className`
- `@param string $methodName`
- `@return FunctionMock`

<details><summary>Example</summary>
<p>

```hack
using $methodSpy = Hammock\spy_class_method(MyClass::class, 'returnInput');

$firstObject = new MyClass();
$secondObject = new MyClass();

$firstObject->returnInput(0); // 0
$secondObject->returnInput(0); // 0

$methodSpy->getNumCalls(); // 2
```
</details>

## Object method spy
Creates an object-level function spy that does not change the object method behavior but still tracks all of the calls.

API: `Hammock\spy_object_method`
- `@param T $object`
- `@param string $methodName`
- `@return FunctionMock`

<details><summary>Example</summary>
<p>

```hack
using $methodSpy = Hammock\spy_object_method($object, 'returnInput');

$object->returnInput(0); // 0

$methodSpy->getNumCalls(); // 1
```

</details>

## Global function spy
Creates a function spy that does not change the global function behavior but still tracks all of the calls.

API: `Hammock\spy_global_function`
- `@param string $globalFunctionName`
- `@return FunctionMock`

<details><summary>Example</summary>
<p>

```hack
using $methodSpy = Hammock\spy_object_method($object, 'returnInput');

$object->returnInput(0); // 0

$methodSpy->getNumCalls(); // 1
```
</details>

# No-ops

No-ops are useful when trying to override a function's behavior with no-op.

## Class method noop
Creates a class-level function mock that overrides the class method behavior with no-op.

API: `Hammock\noop_class_method`
- `@param classname<T> $className`
- `@param string $methodName`
- `@return FunctionMock`

<details><summary>Example</summary>
<p>

```hack
using $methodNoop = Hammock\noop_class_method(MyClass::class, 'returnInput');

$firstObject = new MyClass();
$secondObject = new MyClass();

$firstObject->returnInput(0); // null
$secondObject->returnInput(0); // null

$methodNoop->getNumCalls(); // 2
```

</details>

## Object method noop
Creates an object-level function mock that overrides the object method behavior with no-op.

API: `Hammock\noop_object_method`
- `@param T $object`
- `@param string $methodName`
- `@return FunctionMock`

<details><summary>Example</summary>
<p>

```hack
using $methodNoop = Hammock\noop_object_method($object, 'returnInput');

$object->returnInput(0); // null

$methodNoop->getNumCalls(); // 1
```

</details>


## Global function noop
Creates a function mock that overrides the global function behavior with no-op.

API: `Hammock\noop_global_function`
- `@param string $globalFunctionName`
- `@return FunctionMock`

<details><summary>Example</summary>
<p>

```hack
using $functionNoop = Hammock\noop_global_function('return_input');

return_input(0); // null

$functionNoop->getNumCalls(); // 1
```

</details>