# Overview

The persistent API is very similar to the main API, but the returned interfaces are not disposable. This means that the `using` keyword is omitted, and the resulting mocks are not automatically cleaned up at the end of the block scope. The persistent API is especially useful for repeatedly creating the same mocks during test setup. Because these mocks will not be cleaned up at the end of the setup, the mocked behavior may be shared across many different tests. However, this means that the persistent mocks have to be cleaned up manually, preferably during teardown. Otherwise, there could be a memory leak as the mock will persist until the end of the request / CLI invocation. This could also lead to some very confusing errors.

```hack
class UserTest extends HackTest {
	<<__Override>>
	public async function beforeEachTestAsync(): Awaitable<void> {
		// Force all DB queries to skip the cache.
		Hammock\Persistent\mock_class_method(Cache::class, 'get', $args ==> null);
	}

	// Tests with DB queries that skip the cache...

	<<__Override>>
	public async function afterEachTestAsync(): Awaitable<void> {
		Hammock\Persistent\deactivate_all_persistent_mocks();
	}
}
```

# Top-level API

For most of the persistent API, examples are unnecessary as the disposable equivalents can be found in [MAIN_API.md](https://github.com/quizlet/hammock/blob/master/MAIN_API.md). When using the persistent API, simply omit the `using` keyword.

- `Hammock\Persistent\mock_class`
	- Creates a persistent method mock container for a class.
	- `@param classname<T> $className`
	- `@return PersistentMethodMockContainer`

- `Hammock\Persistent\mock_object`
	- Creates a persistent method mock container for an object.
	- `@param T $object`
	- `@return PersistentMethodMockContainer`

- `Hammock\Persistent\mock_class_method`
	- Creates a persistent class-level function mock that overrides the class method behavior with the callback.
	- `@param classname<T> $className`
	- `@param string $methodName`
	- `@param MockCallback $callback`
	- `@return PersistentFunctionMock`

- `Hammock\Persistent\mock_object_method`
	- Creates an persistent object-level function mock that overrides the object method behavior with the callback.
	- `@param T $object`
	- `@param string $methodName`
	- `@param MockCallback $callback`
	- `@return PersistentFunctionMock`

- `Hammock\Persistent\mock_global_function`
	- Creates a persistent function mock that overrides the global function behavior with the callback.
	- `@param string $globalFunctionName`
	- `@param MockCallback $callback`
	- `@return PersistentFunctionMock`

- `Hammock\Persistent\spy_class_method`
	- Creates a persistent class-level function spy that does not change the class method behavior but still tracks all of the calls.
	- `@param classname<T> $className`
	- `@param string $methodName`
	- `@return PersistentFunctionMock`

- `Hammock\Persistent\spy_object_method`
	- Creates an persistent object-level function spy that does not change the object method behavior but still tracks all of the calls.
	- `@param T $object`
	- `@param string $methodName`
	- `@return PersistentFunctionMock`

- `Hammock\Persistent\spy_global_function`
	- Creates a persistent function spy that does not change the global function behavior but still tracks all of the calls.
	- `@param string $globalFunctionName`
	- `@return PersistentFunctionMock`

- `Hammock\Persistent\noop_class_method`
	- Creates a persistent class-level function mock that overrides the class method behavior with no-op.
	- `@param classname<T> $className`
	- `@param string $methodName`
	- `@return PersistentFunctionMock`

- `Hammock\Persistent\noop_object_method`
	- Creates an persistent object-level function mock that overrides the object method behavior with no-op.
	- `@param T $object`
	- `@param string $methodName`
	- `@return PersistentFunctionMock`

- `Hammock\Persistent\noop_global_function`
	- Creates a persistent function mock that overrides the global function behavior with no-op.
	- `@param string $globalFunctionName`
	- `@return PersistentFunctionMock`

- `Hammock\Persistent\deactivate_all_persistent_mocks`
	- Deactivates all persistent mocks that were created. It generally makes sense to call this at the beginning of setup or during teardown.
	- `@return void`
	-	<details><summary>Example</summary><p>

		```hack
		$alice = new User('Alice');

		$alice->getName() === 'Alice'; // true

		Hammock\Persistent\mock_object_method($alice, 'getName', $args ==> 'Carol');

		$alice->getName() === 'Carol'; // true

		Hammock\Persistent\deactivate_all_persistent_mocks();

		$alice->getName() === 'Alice'; // true
		```

		</p></details>

# Mock interfaces

- `PersistentMethodMockContainer` (implements the `IMethodMockContainer` and `IDeactivatable` interfaces)

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
	
	- `deactivate`
		- Deactivates the persistent method mock container, putting it up for garbage collection and restoring the original behavior.
		- `@return void`
		- <details><summary>Example</summary><p>

			```hack
			$alice = new User('Alice');

			$aliceMock = Hammock\Persistent\mock_object($alice);

			$aliceMock->mockMethod('getName', $args ==> 'Carol');

			$alice->getName() === 'Carol'; // true

			$aliceMock->deactivate();

			$alice->getName() === 'Alice'; // true
			```

			</p></details>

	- `isDeactivated`
		- Returns a boolean of whether the persistent method mock container has been deactivated.
		- `@return bool`
		- <details><summary>Example</summary><p>

			```hack
			$alice = newUser('Alice');

			$aliceMock = Hammock\Persistent\mock_object($alice);

			$aliceMock->isDeactivated(); // false

			$aliceMock->deactivate();

			$aliceMock->isDeactivated(); // true
			```

			</p></details>

- `PersistentFunctionMock` (implements the `IFunctionMock` and `IDeactivatable` interfaces)

	- `getNumCalls`
		- Gets the number of calls to the mocked function.
		- `@return int`

	- `getArgsForCall`
		- Gets the vector of arguments from the i-th call to the mocked function.
		- `@param int $i`
		- `@return vec<mixed>`
	
	- `deactivate`
		- Deactivates the persistent function mock, putting it up for garbage collection and restoring the original behavior.
		- `@return void`
		- <details><summary>Example</summary><p>

			```hack
			$alice = new User('Alice');

			$getNameMock = Hammock\Persistent\mock_object_method($alice, 'getName', $args ==> 'Carol');
			
			$alice->getName() === 'Carol'; // true
			$getNameMock->getNumCalls() === 1; // true

			$getNameMock->deactivate();

			$alice->getName() === 'Alice'; // true
			```

			</p></details>

	- `isDeactivated`
		- Returns a boolean of whether the persistent function mock has been deactivated.
		- `@return bool`
		- <details><summary>Example</summary><p>

			```hack
			$alice = new User('Alice');

			$getNameMock = Hammock\Persistent\mock_object_method($alice, 'getName', $args ==> 'Carol');

			$getNameMock->isDeactivated(); // false

			$getNameMock->deactivate();

			$getNameMock->isDeactivated(); // true
			```

			</p></details>
