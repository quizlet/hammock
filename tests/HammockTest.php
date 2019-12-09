<?hh // strict

namespace Hammock;

use type Facebook\HackTest\HackTest;
use type Hammock\Exceptions\HammockException;
use type Hammock\Fixtures\{AnotherClass, ChildClass, TestClass};
use function Facebook\FBExpect\expect;
use function Hammock\Fixtures\{return_input, return_inputs};

class HammockTest extends HackTest {
	public function testClassMock(): void {
		$firstObject = new TestClass();

		expect($firstObject->returnInput(0))->toBeSame(0);

		using ($classMock = mock_class(TestClass::class)) {
			$methodMock = $classMock->mockMethod('returnInput', $args ==> 1);

			$secondObject = new TestClass();

			expect($firstObject->returnInput(0))->toBeSame(1);
			expect($secondObject->returnInput(2))->toBeSame(1);
			expect($methodMock->getNumCalls())->toBeSame(2);
			expect($methodMock->getArgsForCall(0))->toBeSame(vec[0]);
			expect($methodMock->getArgsForCall(1))->toBeSame(vec[2]);
		}

		expect($firstObject->returnInput(0))->toBeSame(0);
	}

	public function testObjectMock(): void {
		$firstObject = new TestClass();
		$secondObject = new TestClass();
		$thirdObject = new TestClass();

		using (
			$secondObjectMock = mock_object($secondObject),

			$thirdObjectMock = mock_object($thirdObject)
		) {
			$secondMethodMock = $secondObjectMock->mockMethod(
				'returnInput',
				$args ==> \intval($args[0]) ** 2,
			);

			$thirdMethodMock = $thirdObjectMock->mockMethod(
				'returnInput',
				$args ==> \intval($args[0]) ** 3,
			);

			expect($firstObject->returnInput(2))->toBeSame(2);
			expect($secondObject->returnInput(2))->toBeSame(4);
			expect($thirdObject->returnInput(2))->toBeSame(8);
			expect($secondMethodMock->getNumCalls())->toBeSame(1);
			expect($secondMethodMock->getArgsForCall(0))->toBeSame(vec[2]);
			expect($thirdMethodMock->getNumCalls())->toBeSame(1);
			expect($thirdMethodMock->getArgsForCall(0))->toBeSame(vec[2]);
		}

		expect($firstObject->returnInput(2))->toBeSame(2);
		expect($secondObject->returnInput(2))->toBeSame(2);
		expect($thirdObject->returnInput(2))->toBeSame(2);
	}

	public function testClassMethodMock(): void {
		using $methodMock =
			mock_class_method(TestClass::class, 'returnInput', $args ==> 1);

		$firstObject = new TestClass();
		$secondObject = new TestClass();

		expect($firstObject->returnInput(0))->toBeSame(1);
		expect($secondObject->returnInput(2))->toBeSame(1);
		expect($methodMock->getNumCalls())->toBeSame(2);
		expect($methodMock->getArgsForCall(0))->toBeSame(vec[0]);
		expect($methodMock->getArgsForCall(1))->toBeSame(vec[2]);
	}

	public function testObjectMethodMock(): void {
		$firstObject = new TestClass();
		$secondObject = new TestClass();

		using $methodMock = mock_object_method(
			$secondObject,
			'returnInput',
			$args ==> \intval($args[0]) ** 2,
		);

		expect($firstObject->returnInput(2))->toBeSame(2);
		expect($secondObject->returnInput(2))->toBeSame(4);
		expect($methodMock->getNumCalls())->toBeSame(1);
		expect($methodMock->getArgsForCall(0))->toBeSame(vec[2]);
	}

	public function testGlobalFunctionMock(): void {
		expect(return_input(0))->toBeSame(0);

		using (
			$functionMock =
				mock_global_function('Hammock\Fixtures\return_input', $args ==> 1)
		) {
			expect(return_input(0))->toBeSame(1);
			expect($functionMock->getNumCalls())->toBeSame(1);
			expect($functionMock->getArgsForCall(0))->toBeSame(vec[0]);
		}

		expect(return_input(0))->toBeSame(0);
	}

	public function testClassMethodSpy(): void {
		using ($classMock = mock_class(TestClass::class)) {
			$methodSpy = $classMock->spyMethod('returnInput');

			$firstObject = new TestClass();
			$secondObject = new TestClass();

			expect($firstObject->returnInput(0))->toBeSame(0);
			expect($secondObject->returnInput(1))->toBeSame(1);
			expect($methodSpy->getNumCalls())->toBeSame(2);
			expect($methodSpy->getArgsForCall(0))->toBeSame(vec[0]);
			expect($methodSpy->getArgsForCall(1))->toBeSame(vec[1]);
		}

		// Shortcut.
		using $methodSpy = spy_class_method(TestClass::class, 'returnInput');

		$firstObject = new TestClass();
		$secondObject = new TestClass();

		expect($firstObject->returnInput(0))->toBeSame(0);
		expect($secondObject->returnInput(1))->toBeSame(1);
		expect($methodSpy->getNumCalls())->toBeSame(2);
		expect($methodSpy->getArgsForCall(0))->toBeSame(vec[0]);
		expect($methodSpy->getArgsForCall(1))->toBeSame(vec[1]);
	}

	public function testObjectMethodSpy(): void {
		$firstObject = new TestClass();
		$secondObject = new TestClass();

		using ($firstObjectMock = mock_object($firstObject)) {
			$methodSpy = $firstObjectMock->spyMethod('returnInput');

			$n = 5;

			for ($i = 0; $i < $n; $i += 1) {
				expect($firstObject->returnInput($i))->toBeSame($i);
				expect($secondObject->returnInput($i))->toBeSame($i);
			}

			expect($methodSpy->getNumCalls())->toBeSame($n);

			for ($i = 0; $i < $n; $i += 1) {
				expect($methodSpy->getArgsForCall($i))->toBeSame(vec[$i]);
			}
		}

		// Shortcut.
		using $methodSpy = spy_object_method($secondObject, 'returnInput');

		$n = 5;

		for ($i = 0; $i < $n; $i += 1) {
			expect($firstObject->returnInput($i))->toBeSame($i);
			expect($secondObject->returnInput($i))->toBeSame($i);
		}

		expect($methodSpy->getNumCalls())->toBeSame($n);

		for ($i = 0; $i < $n; $i += 1) {
			expect($methodSpy->getArgsForCall($i))->toBeSame(vec[$i]);
		}
	}

	public function testGlobalFunctionSpy(): void {
		using $functionSpy = spy_global_function('Hammock\Fixtures\return_input');

		$n = 5;

		for ($i = 0; $i < $n; $i += 1) {
			expect(return_input($i))->toBeSame($i);
		}

		expect($functionSpy->getNumCalls())->toBeSame($n);

		for ($i = 0; $i < $n; $i += 1) {
			expect($functionSpy->getArgsForCall($i))->toBeSame(vec[$i]);
		}
	}

	// NOTE: The call hierarchy goes like this:
	// 		`staticReturnInput`
	// 			calls `publicReturnInput`
	// 				calls `protectedReturnInput`
	//					calls `privateReturnInput`

	public function testMockProtectedMethod(): void {
		$object = new TestClass();

		using $protectedMethodMock =
			mock_object_method($object, 'protectedReturnInput', $args ==> 1);

		using $privateMethodSpy = spy_object_method($object, 'privateReturnInput');

		expect($object->publicReturnInput(0))->toBeSame(1);
		expect($protectedMethodMock->getNumCalls())->toBeSame(1);
		expect($privateMethodSpy->getNumCalls())->toBeSame(0);
	}

	public function testMockPrivateMethod(): void {
		$object = new TestClass();

		using $privateMethodMock =
			mock_object_method($object, 'privateReturnInput', $args ==> 1);

		expect($object->publicReturnInput(0))->toBeSame(1);
		expect($privateMethodMock->getNumCalls())->toBeSame(1);
	}

	public function testMockStaticMethod(): void {
		using $staticMethodMock =
			mock_class_method(TestClass::class, 'staticReturnInput', $args ==> 1);

		using $publicMethodSpy =
			spy_class_method(TestClass::class, 'publicReturnInput');

		expect(TestClass::staticReturnInput(0))->toBeSame(1);
		expect($staticMethodMock->getNumCalls())->toBeSame(1);
		expect($publicMethodSpy->getNumCalls())->toBeSame(0);
	}

	// NOTE: `ChildClass` extends `TestClass`.

	public function testMockParentClassMethod(): void {
		$object = new TestClass();
		$childObject = new ChildClass();

		using mock_class_method(TestClass::class, 'returnInput', $args ==> 1);

		expect($object->returnInput(0))->toBeSame(1);
		expect($childObject->returnInput(0))->toBeSame(1);

		using mock_class_method(
			TestClass::class,
			'overriddenReturnInput',
			$args ==> 1,
		);

		expect($object->overriddenReturnInput(0))->toBeSame(1);
		expect($childObject->overriddenReturnInput(0))->toBeSame(0);
	}

	public function testMockParentClassStaticMethod(): void {
		using mock_class_method(TestClass::class, 'staticReturnInput', $args ==> 1);

		expect(TestClass::staticReturnInput(0))->toBeSame(1);
		expect(ChildClass::staticReturnInput(0))->toBeSame(1);

		using mock_class_method(
			TestClass::class,
			'overriddenStaticReturnInput',
			$args ==> 1,
		);

		expect(TestClass::overriddenStaticReturnInput(0))->toBeSame(1);
		expect(ChildClass::overriddenStaticReturnInput(0))->toBeSame(0);

		using mock_class_method(
			ChildClass::class,
			'overriddenStaticReturnInput',
			$args ==> 2,
		);

		expect(TestClass::overriddenStaticReturnInput(0))->toBeSame(1);
		expect(ChildClass::overriddenStaticReturnInput(0))->toBeSame(2);
	}

	public function testMockParentObjectMethod(): void {
		$childObject = new ChildClass();

		using mock_object_method($childObject, 'returnInput', $args ==> 1);

		expect($childObject->returnInput(0))->toBeSame(1);

		// NOTE: Mocking an object method that is declared in the parent
		// class will automatically mock the parent class method.
		expect(() ==> {
			using mock_class_method(TestClass::class, 'returnInput', $args ==> 1);
		})->toThrow(
			HammockException::class,
			"The method `Hammock\Fixtures\TestClass::returnInput` has already been mocked.",
		);
	}

	// NOTE: `TestClass` uses `TestTrait` and `TestTrait` implements `traitStaticReturnInput`.

	public function testMockTraitMethod(): void {
		expect(TestClass::traitStaticReturnInput(0))->toBeSame(0);

		using mock_class_method(
			TestClass::class,
			'traitStaticReturnInput',
			$args ==> 1,
		);

		expect(TestClass::traitStaticReturnInput(0))->toBeSame(1);
	}

	public function testMockMultipleClasses(): void {
		expect(TestClass::staticReturnInput(0))->toBeSame(0);
		expect(AnotherClass::anotherStaticReturnInput(0))->toBeSame(0);

		using mock_class_method(TestClass::class, 'staticReturnInput', $args ==> 1);

		using mock_class_method(
			AnotherClass::class,
			'anotherStaticReturnInput',
			$args ==> 2,
		);

		expect(TestClass::staticReturnInput(0))->toBeSame(1);
		expect(AnotherClass::anotherStaticReturnInput(0))->toBeSame(2);
	}

	public function testMockObjectsOfDifferentType(): void {
		$object = new TestClass();
		$anotherObject = new AnotherClass();

		expect($object->returnInput(0))->toBeSame(0);
		expect($anotherObject->anotherReturnInput(0))->toBeSame(0);

		using mock_object_method($object, 'returnInput', $args ==> 1);

		using mock_object_method($anotherObject, 'anotherReturnInput', $args ==> 2);

		expect($object->returnInput(0))->toBeSame(1);
		expect($anotherObject->anotherReturnInput(0))->toBeSame(2);
	}

	public function testMockFunctionWithMultipleArgs(): void {
		expect(return_inputs(1, 2, 3))->toBeSame(tuple(1, 2, 3));

		using $functionMock = mock_global_function(
			'Hammock\Fixtures\return_inputs',
			($args) ==> tuple(
				\intval($args[0]) ** 1,
				\intval($args[1]) ** 2,
				\intval($args[2]) ** 3,
			),
		);

		expect(return_inputs(1, 2, 3))->toBeSame(tuple(1, 4, 27));
		expect($functionMock->getArgsForCall(0))->toBeSame(vec[1, 2, 3]);
	}

	public function testNoopClassMethod(): void {
		using ($classMock = mock_class(TestClass::class)) {
			$methodNoop = $classMock->noopMethod('staticReturnInput');

			expect(TestClass::staticReturnInput(0))->toBeNull();
			expect($methodNoop->getNumCalls())->toBeSame(1);
		}

		expect(TestClass::staticReturnInput(0))->toBeSame(0);

		// Shortcut.
		using $methodNoop =
			noop_class_method(TestClass::class, 'staticReturnInput');

		expect(TestClass::staticReturnInput(0))->toBeNull();
		expect($methodNoop->getNumCalls())->toBeSame(1);
	}

	public function testNoopObjectMethod(): void {
		$object = new TestClass();

		using ($objectMock = mock_object($object)) {
			$methodNoop = $objectMock->noopMethod('returnInput');

			expect($object->returnInput(0))->toBeNull();
			expect($methodNoop->getNumCalls())->toBeSame(1);
		}

		// Shortcut.
		using $methodNoop = noop_object_method($object, 'returnInput');

		expect($object->returnInput(0))->toBeNull();
		expect($methodNoop->getNumCalls())->toBeSame(1);
	}

	public function testNoopGlobalFunction(): void {
		using $functionNoop = noop_global_function('Hammock\Fixtures\return_input');

		expect(return_input(0))->toBeNull();
		expect($functionNoop->getNumCalls())->toBeSame(1);
	}

	// The following tests are for invalid use cases.

	public function testMockAlreadyMockedMethod(): void {
		expect(() ==> {
			using $classMock = mock_class(TestClass::class);

			$classMock->mockMethod('staticReturnInput', $args ==> 1);
			$classMock->mockMethod('staticReturnInput', $args ==> 1);
		})->toThrow(
			HammockException::class,
			"The method `Hammock\Fixtures\TestClass::staticReturnInput` has already been mocked.",
		);
	}

	public function testSpyAlreadyMockedMethod(): void {
		expect(() ==> {
			$object = new TestClass();

			using $objectMock = mock_object($object);

			$objectMock->spyMethod('returnInput');
			$objectMock->spyMethod('returnInput');
		})->toThrow(
			HammockException::class,
			"The method `Hammock\Fixtures\TestClass::returnInput` has already been mocked for this object.",
		);
	}

	public function testNoopAlreadyMockedMethod(): void {
		expect(() ==> {
			$object = new TestClass();

			using $classMock = mock_class(TestClass::class);
			using $objectMock = mock_object($object);

			$classMock->noopMethod('returnInput');
			$objectMock->noopMethod('returnInput');
		})->toThrow(
			HammockException::class,
			"The method `Hammock\Fixtures\TestClass::returnInput` already has a class-level mock.",
		);
	}

	public function testMockAlreadyMockedGlobalFunction(): void {
		expect(() ==> {
			using mock_global_function('Hammock\Fixtures\return_input', $args ==> 1);

			using mock_global_function('Hammock\Fixtures\return_input', $args ==> 1);
		})->toThrow(
			HammockException::class,
			"The function `Hammock\Fixtures\\return_input` has already been mocked.",
		);
	}

	public function testSpyAlreadyMockedGlobalFunction(): void {
		expect(() ==> {
			using spy_global_function('Hammock\Fixtures\return_input');
			using spy_global_function('Hammock\Fixtures\return_input');
		})->toThrow(
			HammockException::class,
			"The function `Hammock\Fixtures\\return_input` has already been mocked.",
		);
	}

	public function testNoopAlreadyMockedGlobalFunction(): void {
		expect(() ==> {
			using noop_global_function('Hammock\Fixtures\return_input');
			using noop_global_function('Hammock\Fixtures\return_input');
		})->toThrow(
			HammockException::class,
			"The function `Hammock\Fixtures\\return_input` has already been mocked.",
		);
	}

	public function testMockNonexistentMethod(): void {
		expect(() ==> {
			using mock_class_method(
				TestClass::class,
				'nonexistentMethod',
				$args ==> 1,
			);
		})->toThrow(
			HammockException::class,
			"The method `Hammock\Fixtures\TestClass::nonexistentMethod` does not exist.",
		);
	}

	public function testMockNonexistentGlobalFunction(): void {
		expect(() ==> {
			using mock_global_function(
				'Hammock\Fixtures\nonexistent_global_function',
				$args ==> 1,
			);
		})->toThrow(
			HammockException::class,
			"The function `Hammock\Fixtures\\nonexistent_global_function` does not exist.",
		);
	}

	public function testMockStaticMethodThroughObject(): void {
		expect(() ==> {
			$object = new TestClass();

			using mock_object_method($object, 'staticReturnInput', $args ==> 1);

			TestClass::staticReturnInput(0);
		})->toThrow(
			HammockException::class,
			"The static method `Hammock\Fixtures\TestClass::staticReturnInput` was mocked through an object-level mock. Static methods may only be mocked by class-level mocks.",
		);
	}

	public function testMockPrimitive(): void {
		expect(() ==> {
			using mock_object_method(0, 'returnInput', $args ==> 1);
		})->toThrow(
			HammockException::class,
			"The method `returnInput` cannot be resolved for a non-object.",
		);
	}

	public function testGetUnmockedMethod(): void {
		expect(() ==> {
			using $classMock = mock_class(TestClass::class);

			$classMock->getMethodMock('staticReturnInput');
		})->toThrow(
			HammockException::class,
			"There is no mock for the method `staticReturnInput`.",
		);
	}

	public function testGetArgsForCallOutOfIndex(): void {
		expect(() ==> {
			using $methodMock =
				mock_class_method(TestClass::class, 'staticReturnInput', $args ==> 1);

			TestClass::staticReturnInput(0);
			TestClass::staticReturnInput(0);
			TestClass::staticReturnInput(0);

			$methodMock->getArgsForCall(3);
		})->toThrow(
			HammockException::class,
			"Cannot access index 3 of calls (total number of calls: 3).",
		);
	}

	public function testMockMethodDefinedInParentClass(): void {
		expect(() ==> {
			using mock_class_method(ChildClass::class, 'returnInput', $args ==> 1);
		})->toThrow(
			HammockException::class,
			"Hammock\Fixtures\ChildClass::returnInput` is declared in `Hammock\Fixtures\TestClass`. Please use `Hammock\Fixtures\TestClass::returnInput` instead.",
		);
	}

	public function testThisWithClassMethodMock(): void {
		$object = new TestClass();

		using mock_class_method(
			TestClass::class,
			'returnInput',
			$args ==> {
				expect(this())->toBeSame($object);

				return 1;
			},
		);

		$exceptionMessage =
			"The current object may only be accessed during the execution of an instance method mock callback.";

		expect(() ==> this())->toThrow(HammockException::class, $exceptionMessage);

		expect($object->returnInput(0))->toBeSame(1);

		expect(() ==> this())->toThrow(HammockException::class, $exceptionMessage);
	}

	public function testThisWithObjectMethodMock(): void {
		$firstObject = new TestClass();
		$secondObject = new TestClass();

		using mock_object_method(
			$firstObject,
			'returnInput',
			$args ==> {
				expect(this())->toBeSame($firstObject);

				// Test nested object method mocks.
				expect($secondObject->returnInput(0))->toBeSame(1);

				expect(this())->toBeSame($firstObject);

				return 1;
			},
		);

		using mock_object_method(
			$secondObject,
			'returnInput',
			$args ==> {
				expect(this())->toBeSame($secondObject);

				return 1;
			},
		);

		expect(() ==> this())->toThrow(HammockException::class);
		expect($firstObject->returnInput(0))->toBeSame(1);
		expect(() ==> this())->toThrow(HammockException::class);
		expect($secondObject->returnInput(0))->toBeSame(1);
		expect(() ==> this())->toThrow(HammockException::class);
	}

	public function testThisWithFluentMethodMock(): void {
		$object = new TestClass();

		using $methodMock = mock_object_method(
			$object,
			'returnThis',
			$args ==> {
				return this();
			},
		);

		using mock_object_method(
			$object,
			'returnInput',
			$args ==> {
				expect(this())->toBeSame($object);

				return 1;
			},
		);

		expect(() ==> this())->toThrow(HammockException::class);
		expect($object->returnThis()->returnInput(0))->toBeSame(1);
		expect(() ==> this())->toThrow(HammockException::class);
		expect($methodMock->getNumCalls())->toBeSame(1);
	}

	public function testThisWithGlobalFunctionMock(): void {
		using mock_global_function(
			'Hammock\Fixtures\return_input',
			$args ==> {
				expect(() ==> this())->toThrow(
					HammockException::class,
					"The current object may only be accessed during the execution of an instance method mock callback.",
				);

				return 1;
			},
		);

		expect(return_input(0))->toBeSame(1);
	}

	public function testThisWithStaticMethodMock(): void {
		using mock_class_method(
			TestClass::class,
			'staticReturnInput',
			$args ==> {
				expect(() ==> this())->toThrow(
					HammockException::class,
					"The current object may only be accessed during the execution of an instance method mock callback.",
				);

				return 1;
			},
		);

		expect(TestClass::staticReturnInput(0))->toBeSame(1);
	}

	public function testThisWithIoCBetweenTwoObjectMethodMocks(): void {
		$firstObject = new TestClass();
		$secondObject = new TestClass();
		$thirdObject = new TestClass();

		using mock_object_method(
			$firstObject,
			'returnInput',
			$args ==> {
				expect(this())->toBeSame($firstObject);

				$that = this();

				expect(
					$secondObject->returnCallbackResult(() ==> {
						// NOTE: This is where the behaviors of
						// `Hammock\this()` and `$this` differ.
						expect(this())->toBeSame($secondObject);
						expect($that)->toBeSame($firstObject);

						return 1;
					}),
				)->toBeSame(2);

				expect(this())->toBeSame($firstObject);

				expect(
					$thirdObject->returnCallbackResult(() ==> {
						expect(this())->toBeSame($firstObject);

						return 1;
					}),
				)->toBeSame(1);

				expect(this())->toBeSame($firstObject);

				return 1;
			},
		);

		using mock_object_method(
			$secondObject,
			'returnCallbackResult',
			$args ==> {
				expect(this())->toBeSame($secondObject);

				/* HH_IGNORE_ERROR[4009] */
				return $args[0]() * 2;
			},
		);

		expect($firstObject->returnInput(0))->toBeSame(1);
	}

	public function testMockNullObject(): void {
		expect(() ==> {
			using $objectMock = mock_object(null);

			$objectMock->mockMethod('returnInput', $args ==> 1);
		})->toThrow(
			HammockException::class,
			"The method `returnInput` cannot be resolved for `null`.",
		);

		expect(() ==> {
			using mock_object_method(null, 'returnInput', $args ==> 1);
		})->toThrow(
			HammockException::class,
			"The method `returnInput` cannot be resolved for `null`.",
		);
	}

	<<__Override>>
	public async function afterEachTestAsync(): Awaitable<void> {
		expect(() ==> this())->toThrow(
			HammockException::class,
			"The current object may only be accessed during the execution of an instance method mock callback.",
		);

		expect(MockManager::getNumMockKeys())->toBeSame(0);
	}
}
