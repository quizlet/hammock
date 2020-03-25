# Overview

The key distinction made by Hammock is between _method containers_ and _functions_. Method containers comprise classes and objects. Functions comprise methods and global functions. Mocking a method container allows the developer to mock the behavior of any method within it. Hence, the mocking interface for method containers is called a _method mock container_. When a method is mocked through a method mock container, it instantiates a _function mock_, which is what actually mocks the method's behavior. The function mock is the unit of mocking within Hammock, since Hammock ultimately mocks functions.

Hammock also distinguishes between _class-level_ mocks and _object-level_ mocks. A class-level mock affects _all_ instances of a given class. It is useful when trying to override the behavior of a class method regardless of which object calls it. It is also useful when trying to override the behavior of static methods. On the other hand, an object-level mock only affects _one_ instance of a given class. It is useful when trying to override the behavior of just one instance without affecting the behavior of other instances. Note that static methods may _not_ be mocked through object-level mocks.

By virtue of using `fb_intercept`, Hammock is able to override the behavior of functions at runtime. Rather than creating a mock object that looks and feels the same as a real object, Hammock creates a mock object that _accompanies_ the real object. For the lifespan of the mock object (which ends at the end of the block), the real object's behavior is overridden.

To gain more control over when the mocks get cleaned up, refer to [PERSISTENT_API.md](https://github.com/quizlet/hammock/blob/master/PERSISTENT_API.md).

# Top-level API

Hammock provides APIs for mocking, spying, and "no-oping". Mocks, spies, and no-ops all provide the same interface, but each one has a unique purpose:

- Mocks are useful when trying to override a function's behavior with a stub, as well as track the calls into the overridden function.
- Spies are useful when trying to track the calls into a function without affecting its behavior.
- No-ops are useful when trying to override a function's behavior with no-op.

- `Hammock\mock_class`
	- Creates a method mock container for a class.
	- `@param classname<T> $className`
	- `@return MethodMockContainer`
	-	<details><summary style="color: #3ccfcf">Example</summary><p>

		```hack
		$alice = new User('Alice');
		$bob = new User('Bob');

		$alice->getName() === 'Alice'; // true
		$bob->getName() === 'Bob'; // true

		// Create a class-level mock for the `User` class.
		using $userClassMock = Hammock\mock_class(User::class);

		// Mock the `getName` method to return 'Carol' at all times.
		$getNameMock = $userClassMock->mockMethod('getName', $args ==> 'Carol');

		// The class-level mock affects all instances of the mocked class.
		$alice->getName() === 'Carol'; // true
		$bob->getName() === 'Carol'; // true

		$getNameMock->getNumCalls() === 2; // true

		// It is also possible to mock static methods.
		$getByIdMock = $userClassMock->mockMethod('getById', $args ==> $alice);

		User::getById(rand()) === $alice; // true

		$getByIdMock->getNumCalls() === 1; // true
		```

		</p></details>

- `Hammock\mock_object`
	- Creates a method mock container for an object.
	- `@param T $object`
	- `@return MethodMockContainer`
	- <details><summary style="color: #3ccfcf">Example</summary><p>

		```hack
		// Create instances of an unmocked class.
		$alice = new User('Alice');
		$bob = new User('Bob');

		// Create an object-level mock for one of the instances.
		using $aliceMock = Hammock\mock_object($alice);

		$getNameMock = $aliceMock->mockMethod('getName', $args ==> 'Carol');

		// The object-level mock only affects the mocked instance.
		$alice->getName() === 'Carol'; // true
		$bob->getName() === 'Bob'; // true

		$getNameMock->getNumCalls() === 1; // true
		```

		</p></details>

- `Hammock\mock_class_method`
	- Creates a class-level function mock that overrides the class method behavior with the callback.
	- `@param classname<T> $className`
	- `@param string $methodName`
	- `@param MockCallback $callback`
	- `@return FunctionMock`
	- <details><summary style="color: #3ccfcf">Example</summary><p>

		```hack
		$alice = new User('Alice');
		$bob = new User('Bob');

		// Shortcut for mocking a class method.
		using $getNameMock = Hammock\mock_class_method(User::class, 'getName', $args ==> 'Carol');

		$alice->getName() === 'Carol'; // true
		$bob->getName() === 'Carol'; // true

		$getNameMock->getNumCalls() === 2; // true
		```

		</p></details>

- `Hammock\mock_object_method`
	- Creates an object-level function mock that overrides the object method behavior with the callback.
	- `@param T $object`
	- `@param string $methodName`
	- `@param MockCallback $callback`
	- `@return FunctionMock`
	- <details><summary style="color: #3ccfcf">Example</summary><p>

		```hack
		$alice = new User('Alice');
		$bob = new User('Bob');

		// Shortcut for mocking an object method.
		using $getNameMock = Hammock\mock_object_method($alice, 'getName', $args ==> 'Carol');

		$alice->getName() === 'Carol'; // true
		$bob->getName() === 'Bob'; // true

		$getNameMock->getNumCalls() === 1; // true
		```

		</p></details>

- `Hammock\mock_global_function`
	- Creates a function mock that overrides the global function behavior with the callback.
	- `@param string $globalFunctionName`
	- `@param MockCallback $callback`
	- `@return FunctionMock`
	- <details><summary style="color: #3ccfcf">Example</summary><p>

		```hack
		$alice = new User('Alice');

		using $getUserByIdMock = Hammock\mock_global_function('get_user_by_id', $args ==> $alice);

		get_user_by_id(rand()) === $alice; // true

		$getUserByIdMock->getNumCalls() === 1; // true
		```

		</p></details>

- `Hammock\spy_class_method`
	- Creates a class-level function spy that does not change the class method behavior but still tracks all of the calls.
	- `@param classname<T> $className`
	- `@param string $methodName`
	- `@return FunctionMock`
	- <details><summary style="color: #3ccfcf">Example</summary><p>

		```hack
		$alice = new User('Alice');
		$bob = new User('Bob');

		using $getNameSpy = Hammock\spy_class_method(User::class, 'getName');

		$alice->getName() === 'Alice'; // true
		$bob->getName() === 'Bob'; // true

		$getNameSpy->getNumCalls() === 2; // true
		```

		</p></details>

- `Hammock\spy_object_method`
	- Creates an object-level function spy that does not change the object method behavior but still tracks all of the calls.
	- `@param T $object`
	- `@param string $methodName`
	- `@return FunctionMock`
	- <details><summary style="color: #3ccfcf">Example</summary><p>

		```hack
		$alice = new User('Alice');
		$bob = new User('Bob');

		using $getNameSpy = Hammock\spy_object_method($alice, 'getName');

		$alice->getName() === 'Alice'; // true
		$bob->getName() === 'Bob'; // true

		$getNameSpy->getNumCalls() === 1; // true
		```

		</p></details>

- `Hammock\spy_global_function`
	- Creates a function spy that does not change the global function behavior but still tracks all of the calls.
	- `@param string $globalFunctionName`
	- `@return FunctionMock`
	- <details><summary style="color: #3ccfcf">Example</summary><p>

		```hack
		using $getUserByIdSpy = Hammock\spy_global_function('get_user_by_id');

		get_user_by_id(1)->getId() === 1; // true

		$getUserByIdSpy->getNumCalls() === 1; // true
		```

		</p></details>

- `Hammock\noop_class_method`
	- Creates a class-level function mock that overrides the class method behavior with no-op.
	- `@param classname<T> $className`
	- `@param string $methodName`
	- `@return FunctionMock`
	- <details><summary style="color: #3ccfcf">Example</summary><p>

		```hack
		$alice = new User('Alice');
		$bob = new User('Bob');

		using $getNameNoop = Hammock\noop_class_method(User::class, 'getName');

		$alice->getName() === null; // true
		$bob->getName() === null; // true

		$getNameNoop->getNumCalls() === 1; // true
		```

		</p></details>

- `Hammock\noop_object_method`
	- Creates an object-level function mock that overrides the object method behavior with no-op.
	- `@param T $object`
	- `@param string $methodName`
	- `@return FunctionMock`
	- <details><summary style="color: #3ccfcf">Example</summary><p>

		```hack
		$alice = new User('Alice');
		$bob = new User('Bob');

		using $getNameNoop = Hammock\noop_object_method($alice, 'getName');

		$alice->getName() === null; // true
		$bob->getName() === 'Bob'; // true

		$getNameNoop->getNumCalls() === 1; // true
		```

		</p></details>

- `Hammock\noop_global_function`
	- Creates a function mock that overrides the global function behavior with no-op.
	- `@param string $globalFunctionName`
	- `@return FunctionMock`
	- <details><summary style="color: #3ccfcf">Example</summary><p>

		```hack
		using $getUserByIdNoop = Hammock\noop_global_function('get_user_by_id');

		get_user_by_id(1) === null; // true

		$getUserByIdNoop->getNumCalls() === 1; // true
		```

		</p></details>

- `Hammock\this`
	- When called inside a class or object method mock callback, returns the object on which the method is being called. It is useful for mocking fluent methods, since `$this` will point to something other than the object that should be returned. This function throws if called anywhere outside a class or object method mock callback.
	- `@return mixed`
	- <details><summary style="color: #3ccfcf">Example</summary><p>

		```hack
		$alice = new User('Alice');

		using $getNameMock = Hammock\mock_object_method($alice, 'getName', $args ==> {
			Hammock\this() === $alice; // true

			return 'Carol';
		});

		$alice->getName() === 'Carol'; // true
		```

		</p></details>

# Mock interfaces

- `MethodMockContainer` (implements the `IMethodMockContainer` interface)

	- `mockMethod`
		- Creates a function mock that overrides the method behavior with the callback.
		- `@param string $methodName`
		- `@param MockCallback $callback`
		- `@return IFunctionMock`
		- <details><summary style="color: #3ccfcf">Example</summary><p>

			```hack
			$alice = new User('Alice');

			using $aliceMock = Hammock\mock_object($alice);

			$getNameMock = $aliceMock->mockMethod('getName', $args ==> 'Carol');

			$alice->getName() === 'Carol'; // true
			$getNameMock->getNumCalls() === 1; // true
			```

			</p></details>

	- `spyMethod`
		- Creates a function spy that does not change the method behavior but still tracks all of the calls.
		- `@param string $methodName`
		- `@return IFunctionMock`
		- <details><summary style="color: #3ccfcf">Example</summary><p>

			```hack
			$alice = new User('Alice');

			using $aliceMock = Hammock\mock_object($alice);

			$getNameSpy = $aliceMock->spyMethod('getName');

			$alice->getName() === 'Alice'; // true
			$getNameSpy->getNumCalls() === 1; // true
			```

			</p></details>

	- `noopMethod`
		- Creates a function mock that overrides the method behavior with no-op.
		- `@param string $methodName`
		- `@return IFunctionMock`
		- <details><summary style="color: #3ccfcf">Example</summary><p>

			```hack
			$alice = new User('Alice');

			using $aliceMock = Hammock\mock_object($alice);

			$getNameNoop = $aliceMock->noopMethod('getName');

			$alice->getName() === null; // true
			$getNameNoop->getNumCalls() === 1; //true
			```

			</p></details>

	- `getMethodMock`
		- Gets a function mock/spy/noop for a method.
		- `@param string $methodName`
		- `@return IFunctionMock`
		- <details><summary style="color: #3ccfcf">Example</summary><p>

			```hack
			$alice = new User('Alice');

			using $aliceMock = Hammock\mock_object($alice);

			$aliceMock->mockMethod('getName', $args ==> 'Carol');
			$getNameMock = $aliceMock->getMethodMock('getName');

			$alice->getName() === 'Carol'; // true
			$getNameMock->getNumCalls() === 1; // true
			```

			</p></details>

- `FunctionMock` (implements the `IFunctionMock` interface)

	- `getNumCalls`
		- Gets the number of calls to the mocked function.
		- `@return int`
		- <details><summary style="color: #3ccfcf">Example</summary><p>

			```hack
			$alice = new User('Alice');

			using $getNameMock = Hammock\mock_object_method($alice, 'getName', $args ==> 'Carol');

			$alice->getName() === 'Carol'; // true
			$getNameMock->getNumCalls() === 1; // true

			$alice->getName() === 'Carol'; // true
			$getNameMock->getNumCalls() === 2; // true
			```

			</p></details>

	- `getArgsForCall`
		- Gets the vector of arguments from the i-th call to the mocked function.
		- `@param int $i`
		- `@return vec<mixed>`
		- <details><summary style="color: #3ccfcf">Example</summary><p>

			```hack
			using $getByIdSpy = Hammock\spy_class_method(User::class, 'getById');

			$firstUser = User::getById(1);
			$secondUser = User::getById(2);

			$getByIdSpy->getArgsForCall(0) === vec[1]; // true
			$getByIdSpy->getArgsForCall(1) === vec[2]; // true
			```

			</p></details>
