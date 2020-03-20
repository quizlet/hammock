<?hh // strict

namespace Hammock;

use type Facebook\HackTest\HackTest;
use type Hammock\Exceptions\HammockException;
use type Hammock\Fixtures\{ChildClass, TestClass, MockDeactivatable, MockPersistentMockRegistry};
use type Hammock\Persistent\Mocks\{PersistentFunctionMock};
use function Facebook\FBExpect\expect;
use function Hammock\Fixtures\return_input;
use namespace Hammock\Persistent;
use namespace HH\Lib\{C, Str};

class PersistentTest extends HackTest {
	<<__Override>>
	public async function beforeEachTestAsync(): Awaitable<void> {
		Persistent\deactivate_all_persistent_mocks();
		expect(MockManager::getNumMockKeys())->toBeSame(0);
	}

	public function testClassMethodMock(): void {
		$methodMock = $this->mockClassMethod(TestClass::class, 'returnInput');

		$firstObject = new TestClass();
		$secondObject = new TestClass();

		expect($firstObject->returnInput(0))->toBeSame(1);
		expect($secondObject->returnInput(2))->toBeSame(1);
		expect($methodMock->getNumCalls())->toBeSame(2);
		expect($methodMock->getArgsForCall(0))->toBeSame(vec[0]);
		expect($methodMock->getArgsForCall(1))->toBeSame(vec[2]);

		expect(() ==> {
			$this->mockClassMethod(TestClass::class, 'returnInput');
		})->toThrow(
			HammockException::class,
			"The method `Hammock\Fixtures\TestClass::returnInput` has already been mocked.",
		);
	}

	public function testObjectMethodMock(): void {
		$firstObject = new TestClass();
		$secondObject = new TestClass();

		$methodMock = $this->mockObjectMethod($secondObject, 'returnInput');

		expect($firstObject->returnInput(2))->toBeSame(2);
		expect($secondObject->returnInput(2))->toBeSame(4);
		expect($methodMock->getNumCalls())->toBeSame(1);
		expect($methodMock->getArgsForCall(0))->toBeSame(vec[2]);

		expect(() ==> {
			$this->mockObjectMethod($secondObject, 'returnInput');
		})->toThrow(
			HammockException::class,
			"The method `Hammock\Fixtures\TestClass::returnInput` has already been mocked for this object.",
		);
	}

	public function testGlobalFunctionMock(): void {
		expect(return_input(0))->toBeSame(0);

		$functionMock = $this->mockGlobalFunction();

		expect(return_input(0))->toBeSame(1);
		expect($functionMock->getNumCalls())->toBeSame(1);
		expect($functionMock->getArgsForCall(0))->toBeSame(vec[0]);
		expect(() ==> {
			$this->mockGlobalFunction();
		})->toThrow(
			HammockException::class,
			"The function `Hammock\Fixtures\\return_input` has already been mocked.",
		);
	}

	public function testClassMethodSpy(): void {
		$methodSpy = $this->spyClassMethod();

		$firstObject = new TestClass();
		$secondObject = new TestClass();

		expect($firstObject->returnInput(0))->toBeSame(0);
		expect($secondObject->returnInput(1))->toBeSame(1);
		expect($methodSpy->getNumCalls())->toBeSame(2);
		expect($methodSpy->getArgsForCall(0))->toBeSame(vec[0]);
		expect($methodSpy->getArgsForCall(1))->toBeSame(vec[1]);

		expect(() ==> {
			$this->spyClassMethod();
		})->toThrow(
			HammockException::class,
			"The method `Hammock\Fixtures\TestClass::returnInput` has already been mocked.",
		);
	}

	public function testObjectMethodSpy(): void {
		$firstObject = new TestClass();
		$secondObject = new TestClass();

		$methodSpy = $this->spyObjectMethod($secondObject);
		$n = 5;

		for ($i = 0; $i < $n; $i += 1) {
			expect($firstObject->returnInput($i))->toBeSame($i);
			expect($secondObject->returnInput($i))->toBeSame($i);
		}

		expect($methodSpy->getNumCalls())->toBeSame($n);

		for ($i = 0; $i < $n; $i += 1) {
			expect($methodSpy->getArgsForCall($i))->toBeSame(vec[$i]);
		}

		expect(() ==> {
			$this->spyObjectMethod($secondObject);
		})->toThrow(
			HammockException::class,
			"The method `Hammock\Fixtures\TestClass::returnInput` has already been mocked for this object.",
		);
	}

	public function testGlobalFunctionSpy(): void {
		$functionSpy = $this->mockGlobalSpyFunction();

		$n = 5;

		for ($i = 0; $i < $n; $i += 1) {
			expect(return_input($i))->toBeSame($i);
		}

		expect($functionSpy->getNumCalls())->toBeSame($n);

		for ($i = 0; $i < $n; $i += 1) {
			expect($functionSpy->getArgsForCall($i))->toBeSame(vec[$i]);
		}

		expect(() ==> {
			$this->mockGlobalSpyFunction();
		})->toThrow(
			HammockException::class,
			"The function `Hammock\Fixtures\\return_input` has already been mocked.",
		);
	}

	public function testNoopClassMethod(): void {
		$methodNoop = $this->noopClassMethod();

		expect(TestClass::staticReturnInput(0))->toBeNull();
		expect($methodNoop->getNumCalls())->toBeSame(1);

		expect(() ==> {
			$this->noopClassMethod();
		})->toThrow(
			HammockException::class,
			"The method `Hammock\Fixtures\TestClass::staticReturnInput` has already been mocked.",
		);
	}

	public function testNoopObjectMethod(): void {
		$object = new TestClass();
		$methodNoop = $this->noopObjectMethod($object);

		expect($object->returnInput(0))->toBeNull();
		expect($methodNoop->getNumCalls())->toBeSame(1);

		expect(() ==> {
			$this->noopObjectMethod($object);
		})->toThrow(
			HammockException::class,
			"The method `Hammock\Fixtures\TestClass::returnInput` has already been mocked for this object.",
		);
	}

	public function testNoopGlobalFunction(): void {
		$functionNoop = $this->noopGlobalFunction();

		expect(return_input(0))->toBeNull();
		expect($functionNoop->getNumCalls())->toBeSame(1);

		expect(() ==> {
			$this->noopGlobalFunction();
		})->toThrow(
			HammockException::class,
			"The function `Hammock\Fixtures\\return_input` has already been mocked.",
		);
	}

	public function testMockNullObject(): void {
		expect(() ==> {
			$this->mockObjectMethod(null, 'returnInput');
		})->toThrow(
			HammockException::class,
			"The method `returnInput` cannot be resolved for `null`.",
		);
	}

	public function testMockNonexistentMethod(): void {
		expect(() ==> {
			$this->mockClassMethod(TestClass::class, 'nonexistentMethod');
		})->toThrow(
			HammockException::class,
			"The method `Hammock\Fixtures\TestClass::nonexistentMethod` does not exist.",
		);
	}

	public function testMockNonexistentGlobalFunction(): void {
		expect(() ==> {
			Persistent\mock_global_function(
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
			$this->mockObjectMethod($object, 'staticReturnInput');
			TestClass::staticReturnInput(0);
		})->toThrow(
			HammockException::class,
			"The static method `Hammock\Fixtures\TestClass::staticReturnInput` was mocked through an object-level mock. Static methods may only be mocked by class-level mocks.",
		);
	}

	public function testMockPrimitive(): void {
		expect(() ==> {
			$this->mockObjectMethod(0, 'returnInput');
		})->toThrow(
			HammockException::class,
			"The method `returnInput` cannot be resolved for a non-object.",
		);
	}

	public function testGetArgsForCallOutOfIndex(): void {
		expect(() ==> {
			$methodMock =
				$this->mockClassMethod(TestClass::class, 'staticReturnInput');

			TestClass::staticReturnInput(0);
			TestClass::staticReturnInput(0);
			TestClass::staticReturnInput(0);

			$methodMock->getArgsForCall(3);
		})->toThrow(
			HammockException::class,
			"Cannot access index 3 of calls (total number of calls: 3).",
		);
	}

	public function testGetNumCallsAfterDeactivation(): void {
		expect(() ==> {
			$methodMock =
				$this->mockClassMethod(TestClass::class, 'staticReturnInput');
			$methodMock->deactivate();
			$methodMock->getNumCalls();
		})->toThrow(
			HammockException::class,
			"This function mock has been deactivated. Further interaction with this function mock is prohibited.",
		);
	}

	public function testGetArgsForCallAfterDeactivation(): void {
		expect(() ==> {
			$methodMock =
				$this->mockClassMethod(TestClass::class, 'staticReturnInput');
			$methodMock->deactivate();
			$methodMock->getArgsForCall(0);
		})->toThrow(
			HammockException::class,
			"This function mock has been deactivated. Further interaction with this function mock is prohibited.",
		);
	}

	public function testDeactivatingClassMethodMock(): void {
		$methodMock = $this->mockClassMethod(TestClass::class, 'staticReturnInput');

		expect(TestClass::staticReturnInput(0))->toBeSame(1);

		$methodMock->deactivate();

		expect(TestClass::staticReturnInput(0))->toBeSame(0);
	}

	public function testDeactivatingObjectMethodMock(): void {
		$object = new TestClass();

		$methodMock = $this->mockObjectMethod($object, 'returnInput');

		expect($object->returnInput(2))->toBeSame(4);

		$methodMock->deactivate();

		expect($object->returnInput(0))->toBeSame(0);
	}

	public function testDeactivatingGlobalFunctionMock(): void {
		$functionMock = $this->mockGlobalFunction();

		expect(return_input(0))->toBeSame(1);

		$functionMock->deactivate();

		expect(return_input(0))->toBeSame(0);
	}

	public function testDeactivatingMultipleTimes(): void {
		$methodMock = $this->mockClassMethod(TestClass::class, 'staticReturnInput');

		expect(TestClass::staticReturnInput(0))->toBeSame(1);

		$methodMock->deactivate();
		$methodMock->deactivate();
		$methodMock->deactivate();

		expect(TestClass::staticReturnInput(0))->toBeSame(0);
	}

	public function testMockMethodDefinedInParentClass(): void {
		expect(() ==> {
			$this->mockClassMethod(ChildClass::class, 'returnInput');
		})->toThrow(
			HammockException::class,
			"Hammock\Fixtures\ChildClass::returnInput` is declared in `Hammock\Fixtures\TestClass`. Please use `Hammock\Fixtures\TestClass::returnInput` instead.",
		);
	}

	public function testMockRegistryPrune(): void {
		$activatedMock = new MockDeactivatable();
		MockPersistentMockRegistry::register($activatedMock);

		for ($i = 0; $i < MockPersistentMockRegistry::REGISTRY_SOFT_LIMIT - 1; $i++) {
			$deactivatedMock = new MockDeactivatable();
			$deactivatedMock->deactivate();
			MockPersistentMockRegistry::register($deactivatedMock);
		}

		$registry = MockPersistentMockRegistry::getRegistry();
		expect(C\count($registry))->toBeSame(1);
		expect($registry[0]->isDeactivated())->toBeSame(false);
	}

	// Subroutines.

	private function mockClassMethod<T>(
		classname<T> $className,
		string $methodName,
	): PersistentFunctionMock {
		return Persistent\mock_class_method($className, $methodName, $args ==> 1);
	}

	private function mockObjectMethod<T>(
		T $object,
		string $methodName,
	): PersistentFunctionMock {
		return Persistent\mock_object_method(
			$object,
			$methodName,
			$args ==> \intval($args[0]) ** 2,
		);
	}

	private function mockGlobalFunction(): PersistentFunctionMock {
		return Persistent\mock_global_function(
			'Hammock\Fixtures\return_input',
			$args ==> 1,
		);
	}

	private function spyClassMethod(): PersistentFunctionMock {
		return Persistent\spy_class_method(TestClass::class, 'returnInput');
	}

	private function spyObjectMethod(TestClass $object): PersistentFunctionMock {
		return Persistent\spy_object_method($object, 'returnInput');
	}

	private function mockGlobalSpyFunction(): PersistentFunctionMock {
		return Persistent\spy_global_function('Hammock\Fixtures\return_input');
	}

	private function noopClassMethod(): PersistentFunctionMock {
		return Persistent\noop_class_method(TestClass::class, 'staticReturnInput');
	}

	private function noopObjectMethod(TestClass $object): PersistentFunctionMock {
		return Persistent\noop_object_method($object, 'returnInput');
	}

	private function noopGlobalFunction(): PersistentFunctionMock {
		return Persistent\noop_global_function('Hammock\Fixtures\return_input');
	}
}
