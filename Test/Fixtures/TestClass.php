<?hh // strict

namespace Hammock\Fixtures;

class TestClass {
	use TestTrait;

	public function returnInput<T>(T $input): T {
		return $input;
	}

	public function returnThis(): this {
		return $this;
	}

	public function returnCallbackResult<T>((function(): T) $callback): T {
		return $callback();
	}

	// NOTE: The following methods have a hierarchy to test
	// the level at which the interception is taking place.

	public static function staticReturnInput<T>(T $input): T {
		return (new self())->publicReturnInput($input);
	}

	public function publicReturnInput<T>(T $input): T {
		return $this->protectedReturnInput($input);
	}

	protected function protectedReturnInput<T>(T $input): T {
		return $this->privateReturnInput($input);
	}

	private function privateReturnInput<T>(T $input): T {
		return $input;
	}

	// NOTE: The following methods demonstrate how an overridden
	// method has a different fully qualified name from their
	// parent method, and therefore they are mocked separately.

	public function overriddenReturnInput<T>(T $input): T {
		return $input;
	}

	public static function overriddenStaticReturnInput<T>(T $input): T {
		return $input;
	}
}
