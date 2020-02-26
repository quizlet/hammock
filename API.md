# API

Mocks are useful when trying to override a function's behavior with a stub, as well as track the calls into the overridden function. Spies are useful when trying to track the calls into a function without affecting its behavior. No-ops are useful when trying to override a function's behavior with no-op. The public APIs intended for use are as follows:

- `Hammock\mock_class`
	- Creates a method mock container for a class.
	- `@param classname<T> $className`
	- `@return MethodMockContainer`
	-	<details><summary>Example</summary><p>

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

		</p></details>

- `Hammock\mock_object`
	- Creates a method mock container for an object.
	- `@param T $object`
	- `@return MethodMockContainer`
	- <details><summary>Example</summary><p>

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

		</p></details>

- `Hammock\mock_class_method`
	- Creates a class-level function mock that overrides the class method behavior with the callback.
	- `@param classname<T> $className`
	- `@param string $methodName`
	- `@param MockCallback $callback`
	- `@return FunctionMock`
	- <details><summary>Example</summary><p>

		```hack
		// Shortcut for mocking a class method.
		using $methodMock = Hammock\mock_class_method(MyClass::class, 'returnInput', $args ==> 1);

		$firstObject = new MyClass();
		$secondObject = new MyClass();

		$firstObject->returnInput(0); // 1
		$secondObject->returnInput(0); // 1

		$methodMock->getNumCalls(); // 2
		```

		</p></details>

- `Hammock\mock_object_method`
	- Creates an object-level function mock that overrides the object method behavior with the callback.
	- `@param T $object`
	- `@param string $methodName`
	- `@param MockCallback $callback`
	- `@return FunctionMock`
	- <details><summary>Example</summary><p>

		```hack
		// Shortcut for mocking an object method.
		using $methodMock = Hammock\mock_object_method($object, 'returnInput', $args ==> 1);

		$object->returnInput(0); // 1

		$methodMock->getNumCalls(); // 1
		```

		</p></details>

- `Hammock\mock_global_function`
	- Creates a function mock that overrides the global function behavior with the callback.
	- `@param string $globalFunctionName`
	- `@param MockCallback $callback`
	- `@return FunctionMock`
	- <details><summary>Example</summary><p>

		```hack
		using $functionMock = Hammock\mock_global_function('return_input', $args ==> 1);

		return_input(0); // 1

		$functionMock->getNumCalls(); // 1
		```

		</p></details>

- `Hammock\spy_class_method`
	- Creates a class-level function spy that does not change the class method behavior but still tracks all of the calls.
	- `@param classname<T> $className`
	- `@param string $methodName`
	- `@return FunctionMock`
	- <details><summary>Example</summary><p>

		```hack
		using $methodSpy = Hammock\spy_class_method(MyClass::class, 'returnInput');

		$firstObject = new MyClass();
		$secondObject = new MyClass();

		$firstObject->returnInput(0); // 0
		$secondObject->returnInput(0); // 0

		$methodSpy->getNumCalls(); // 2
		```

		</p></details>

- `Hammock\spy_object_method`
	- Creates an object-level function spy that does not change the object method behavior but still tracks all of the calls.
	- `@param T $object`
	- `@param string $methodName`
	- `@return FunctionMock`
	- <details><summary>Example</summary><p>

		```hack
		using $methodSpy = Hammock\spy_object_method($object, 'returnInput');

		$object->returnInput(0); // 0

		$methodSpy->getNumCalls(); // 1
		```

		</p></details>

- `Hammock\spy_global_function`
	- Creates a function spy that does not change the global function behavior but still tracks all of the calls.
	- `@param string $globalFunctionName`
	- `@return FunctionMock`
	- <details><summary>Example</summary><p>

		```hack
		using $functionSpy = Hammock\spy_global_function('return_input');

		return_input(0); // 0

		$functionSpy->getNumCalls(); // 1
		```

		</p></details>

- `Hammock\noop_class_method`
	- Creates a class-level function mock that overrides the class method behavior with no-op.
	- `@param classname<T> $className`
	- `@param string $methodName`
	- `@return FunctionMock`
	- <details><summary>Example</summary><p>

		```hack
		using $methodNoop = Hammock\noop_class_method(MyClass::class, 'returnInput');

		$firstObject = new MyClass();
		$secondObject = new MyClass();

		$firstObject->returnInput(0); // null
		$secondObject->returnInput(0); // null

		$methodNoop->getNumCalls(); // 2
		```

		</p></details>

- `Hammock\noop_object_method`
	- Creates an object-level function mock that overrides the object method behavior with no-op.
	- `@param T $object`
	- `@param string $methodName`
	- `@return FunctionMock`
	- <details><summary>Example</summary><p>

		```hack
		using $methodNoop = Hammock\noop_object_method($object, 'returnInput');

		$object->returnInput(0); // null

		$methodNoop->getNumCalls(); // 1
		```

		</p></details>

- `Hammock\noop_global_function`
	- Creates a function mock that overrides the global function behavior with no-op.
	- `@param string $globalFunctionName`
	- `@return FunctionMock`
	- <details><summary>Example</summary><p>

		```hack
		using $functionNoop = Hammock\noop_global_function('return_input');

		return_input(0); // null

		$functionNoop->getNumCalls(); // 1
		```

		</p></details>

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
		- <details><summary>Example</summary><p>

			```hack
			using $classMock = Hammock\mock_class(MyClass::class);

			$methodSpy = $classMock->spyMethod('returnInput');

			$firstObject = new MyClass();
			$secondObject = new MyClass();

			$firstObject->returnInput(0); // 0
			$secondObject->returnInput(0); // 0

			$methodSpy->getNumCalls(); // 2
			```

			```hack
			using $objectMock = Hammock\mock_object($object);

			$methodSpy = $objectMock->spyMethod('returnInput');

			$object->returnInput(0); // 0

			$methodSpy->getNumCalls(); // 1
			```

			</p></details>

	- `noopMethod`
		- Creates a function mock that overrides the method behavior with no-op.
		- `@param string $methodName`
		- `@return IFunctionMock`
		- <details><summary>Example</summary><p>

			```hack
			using $classMock = Hammock\mock_class(MyClass::class);

			$methodNoop = $classMock->noopMethod('returnInput');

			$firstObject = new MyClass();
			$secondObject = new MyClass();

			$firstObject->returnInput(0); // null
			$secondObject->returnInput(0); // null

			$methodNoop->getNumCalls(); // 2
			```

			```hack
			using $objectMock = Hammock\mock_object($object);

			$methodNoop = $objectMock->noopMethod('returnInput');

			$object->returnInput(0); // null

			$methodNoop->getNumCalls(); // 1
			```

			</p></details>

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