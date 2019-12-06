<?hh // strict

/**
 * NOTE: This class only exposes static methods and is meant to be used
 * exclusively by classes that implement the `IFunctionMock` interface,
 * as well as the magic `Hammock\this` function which is a public API.
 */

namespace Hammock;

use type Hammock\Exceptions\{HammockException, PassThroughException};
use function Hammock\get_declaring_class_name;
use namespace HH\Lib\{C, Dict, Str, Vec};
use type Hammock\{MockCallback, InterceptedCall};

class MockManager {
	const type InternalMockCallback = (function(mixed, vec<mixed>): mixed);

	// Data about the mock function (callback) and the calls to it.
	const type MockData = shape(
		'callback' => MockCallback,
		'calls' => vec<InterceptedCall>,
	);

	// Maps object hashes to their corresponding mocks.
	const type ObjectMockDataDict = dict<string, self::MockData>;

	protected static dict<string, self::ObjectMockDataDict> $objectMethodMocks =
		dict[];

	protected static dict<string, vec<InterceptedCall>> $classMethodMocks =
		dict[];

	protected static dict<string, vec<InterceptedCall>> $globalFunctionMocks =
		dict[];

	protected static mixed $currentObject = null;

	public static function mockClassMethod<T>(
		classname<T> $className,
		string $methodName,
		MockCallback $callback,
	): void {
		$mockKey =
			self::getFullyQualifiedClassMethodName($className, $methodName);

		if (
			C\contains_key(self::$classMethodMocks, $mockKey) ||
			C\contains_key(self::$objectMethodMocks, $mockKey)
		) {
			throw new HammockException(
				Str\format(
					"The method `%s` has already been mocked.",
					$mockKey,
				),
			);
		}

		self::$classMethodMocks[$mockKey] = vec[];

		self::mock(
			$mockKey,
			(mixed $object, vec<mixed> $args): mixed ==> {
				self::$classMethodMocks[$mockKey][] = shape(
					'args' => $args,
					'object' => $object,
				);

				return $callback($args);
			},
		);
	}

	// NOTE: The `globalFunctionName` parameter has to be fully qualified.
	public static function mockGlobalFunction(
		string $globalFunctionName,
		MockCallback $callback,
	): void {
		if (!\function_exists($globalFunctionName)) {
			throw new HammockException(
				Str\format(
					"The function `%s` does not exist.",
					$globalFunctionName,
				),
			);
		}

		if (C\contains_key(self::$globalFunctionMocks, $globalFunctionName)) {
			throw new HammockException(
				Str\format(
					"The function `%s` has already been mocked.",
					$globalFunctionName,
				),
			);
		}

		self::$globalFunctionMocks[$globalFunctionName] = vec[];

		self::mock(
			$globalFunctionName,
			(mixed $_, vec<mixed> $args) ==> {
				self::$globalFunctionMocks[$globalFunctionName][] = shape(
					'args' => $args,
					'object' => null,
				);

				return $callback($args);
			},
		);
	}

	public static function mockObjectMethod<T>(
		T $object,
		string $methodName,
		MockCallback $callback,
	): void {
		$mockKey =
			self::getFullyQualifiedObjectMethodName($object, $methodName);

		if (C\contains_key(self::$classMethodMocks, $mockKey)) {
			throw new HammockException(
				Str\format(
					"The method `%s` already has a class-level mock.",
					$mockKey,
				),
			);
		}

		if (!C\contains_key(self::$objectMethodMocks, $mockKey)) {
			self::initializeObjectLevelMock($mockKey);
		}

		$objectHash = self::hashObject($object);

		if (C\contains_key(self::$objectMethodMocks[$mockKey], $objectHash)) {
			throw new HammockException(
				Str\format(
					"The method `%s` has already been mocked for this object.",
					$mockKey,
				),
			);
		}

		self::$objectMethodMocks[$mockKey][$objectHash] = shape(
			'callback' => $callback,
			'calls' => vec[],
		);
	}

	public static function getClassMethodCalls<T>(
		classname<T> $className,
		string $methodName,
	): vec<InterceptedCall> {
		$mockKey =
			self::getValidatedClassMethodMockKey($className, $methodName);

		return self::$classMethodMocks[$mockKey];
	}

	public static function getGlobalFunctionCalls(
		string $globalFunctionName,
	): vec<InterceptedCall> {
		if (!C\contains_key(self::$globalFunctionMocks, $globalFunctionName)) {
			throw new HammockException(
				Str\format(
					"The function `%s` has not been mocked.",
					$globalFunctionName,
				),
			);
		}

		return self::$globalFunctionMocks[$globalFunctionName];
	}

	public static function getObjectMethodCalls<T>(
		T $object,
		string $methodName,
	): vec<InterceptedCall> {
		list($mockKey, $objectHash) =
			self::getValidatedObjectMethodMockKeyAndObjectHash(
				$object,
				$methodName,
			);

		return self::$objectMethodMocks[$mockKey][$objectHash]['calls'];
	}

	public static function unmockClassMethod<T>(
		classname<T> $className,
		string $methodName,
	): void {
		$mockKey =
			self::getValidatedClassMethodMockKey($className, $methodName);

		self::unmock($mockKey);

		self::$classMethodMocks = Dict\filter_keys(
			self::$classMethodMocks,
			($key) ==> $key !== $mockKey,
		);
	}

	public static function unmockGlobalFunction(
		string $globalFunctionName,
	): void {
		if (!C\contains_key(self::$globalFunctionMocks, $globalFunctionName)) {
			throw new HammockException(
				Str\format(
					"The function `%s` has not been mocked.",
					$globalFunctionName,
				),
			);
		}

		self::unmock($globalFunctionName);

		self::$globalFunctionMocks = Dict\filter_keys(
			self::$globalFunctionMocks,
			($key) ==> $key !== $globalFunctionName,
		);
	}

	public static function unmockObjectMethod<T>(
		T $object,
		string $methodName,
	): void {
		list($mockKey, $objectHash) =
			self::getValidatedObjectMethodMockKeyAndObjectHash(
				$object,
				$methodName,
			);

		self::$objectMethodMocks[$mockKey] = Dict\filter_keys(
			self::$objectMethodMocks[$mockKey],
			($key) ==> $key !== $objectHash,
		);

		if (C\is_empty(self::$objectMethodMocks[$mockKey])) {
			self::unmock($mockKey);

			self::$objectMethodMocks = Dict\filter_keys(
				self::$objectMethodMocks,
				($key) ==> $key !== $mockKey,
			);
		}
	}

	public static function getNumMockKeys(): int {
		return self::getAllMockKeys() |> C\count($$);
	}

	public static function getCurrentObject(): mixed {
		if (self::$currentObject === null) {
			throw new HammockException(
				"The current object may only be accessed during the execution of an instance method mock callback.",
			);
		}

		return self::$currentObject;
	}

	protected static function getAllMockKeys(): vec<string> {
		return Vec\concat(
			Vec\keys(self::$objectMethodMocks),
			Vec\keys(self::$classMethodMocks),
			Vec\keys(self::$objectMethodMocks),
		);
	}

	protected static function initializeObjectLevelMock(string $mockKey): void {
		self::$objectMethodMocks[$mockKey] = dict[];

		self::mock(
			$mockKey,
			(mixed $object, vec<mixed> $args): mixed ==> {
				if ($object === null) {
					throw new HammockException(
						Str\format(
							"The static method `%s` was mocked through an object-level mock. Static methods may only be mocked by class-level mocks.",
							$mockKey,
						),
					);
				}

				$objectHash = self::hashObject($object);

				if (
					!C\contains_key(
						self::$objectMethodMocks[$mockKey],
						$objectHash,
					)
				) {
					throw new PassThroughException();
				}

				self::$objectMethodMocks[$mockKey][$objectHash]['calls'][] =
					shape(
						'args' => $args,
						'object' => $object,
					);

				return
					self::$objectMethodMocks[$mockKey][$objectHash]['callback']
					|> $$($args);
			},
		);
	}

	protected static function getValidatedClassMethodMockKey<T>(
		classname<T> $className,
		string $methodName,
	): string {
		$mockKey =
			self::getFullyQualifiedClassMethodName($className, $methodName);

		if (!C\contains_key(self::$classMethodMocks, $mockKey)) {
			throw new HammockException(
				Str\format(
					"The method `%s` does not have a class-level mock.",
					$mockKey,
				),
			);
		}

		return $mockKey;
	}

	protected static function getValidatedObjectMethodMockKeyAndObjectHash<T>(
		T $object,
		string $methodName,
	): (string, string) {
		$mockKey =
			self::getFullyQualifiedObjectMethodName($object, $methodName);

		if (!C\contains_key(self::$objectMethodMocks, $mockKey)) {
			throw new HammockException(
				Str\format(
					"The method `%s` does not have an object-level mock.",
					$mockKey,
				),
			);
		}

		$objectHash = self::hashObject($object);

		if (!C\contains_key(self::$objectMethodMocks[$mockKey], $objectHash)) {
			throw new HammockException(
				Str\format(
					"The method `%s` has not been mocked for this object.",
					$mockKey,
				),
			);
		}

		return tuple($mockKey, $objectHash);
	}

	protected static function mock(
		string $mockKey,
		self::InternalMockCallback $callback,
	): void {
		/* HH_FIXME[2049] This function is not in any hhi */
		/* HH_FIXME[4107] This function is not in any hhi */
		\fb_intercept(
			$mockKey,
			function(
				string $_,
				mixed $objectOrString,
				array<mixed> $args,
				self::InternalMockCallback $cb,
				/* HH_IGNORE_ERROR[1002] */
				/* HH_FIXME[2087] Don't use references!*/
				bool &$done,
			): mixed {
				// NOTE: The following 3 ignores should be unnecessary, but the
				// type-checker trips out because of the last `&$done` parameter.
				// Removing the comma after `&$done` fixes the issue, but then
				// `hackfmt` automatically re-adds the comma and breaks it again.

				// TODO: Use `$object is string`.
				/* HH_IGNORE_ERROR[2050] */
				$object = \is_string($objectOrString) ? null : $objectOrString;
				$previousObject = self::$currentObject;

				try {
					self::$currentObject = $object;

					/* HH_IGNORE_ERROR[2050] */
					/* HH_IGNORE_ERROR[4084] */
					return vec($args) |> $cb($object, $$);
				} catch (PassThroughException $e) {
					// Pass through to the original, unmocked behavior.
					$done = false;
				} finally {
					self::$currentObject = $previousObject;
				}
			},
			$callback,
		);
	}

	protected static function unmock(string $mockKey): void {
		/* HH_FIXME[2049] This function is not in any hhi. */
		/* HH_FIXME[4107] This function is not in any hhi. */
		\fb_intercept($mockKey, null);
	}

	protected static function hashObject<T>(T $object): string {
		return \spl_object_hash($object);
	}

	protected static function getFullyQualifiedClassMethodName<T>(
		classname<T> $className,
		string $methodName,
		bool $shouldMatchDeclaringClassName = true,
	): string {
		$declaringClassName = get_declaring_class_name($className, $methodName);

		if (
			$shouldMatchDeclaringClassName && $declaringClassName !== $className
		) {
			throw new HammockException(
				Str\format(
					"The method `%s::%s` is declared in `%s`. Please use `%s::%s` instead.",
					$className,
					$methodName,
					$declaringClassName,
					$declaringClassName,
					$methodName,
				),
			);
		}

		return $declaringClassName.'::'.$methodName;
	}

	protected static function getFullyQualifiedObjectMethodName<T>(
		T $object,
		string $methodName,
	): string {
		// TODO: We have to deal with `null` as a special case because
		// `get_class(null)` returns the class in which it was invoked.
		// We can enforce static constraints by using `T as nonnull`.
		if ($object === null) {
			throw new HammockException(
				Str\format(
					"The method `%s` cannot be resolved for `null`.",
					$methodName,
				),
			);
		}

		$className = \get_class($object);

		if ($className === false) {
			throw new HammockException(
				Str\format(
					"The method `%s` cannot be resolved for a non-object.",
					$methodName,
				),
			);
		}

		// NOTE: When mocking/unmocking object methods, we don't care that
		// the object's class is the declaring class for the input method.
		return self::getFullyQualifiedClassMethodName(
			$className,
			$methodName,
			false,
		);
	}
}
