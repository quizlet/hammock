<?hh // strict

namespace Hammock\Persistent;

use Hammock\Persistent\Mocks\{
	ClassMethodMock,
	ClassMock,
	GlobalFunctionMock,
	ObjectMethodMock,
	ObjectMock,
	PersistentFunctionMock,
	PersistentMethodMockContainer,
};

use function Hammock\{get_noop_callback, get_spy_callback};
use type Hammock\MockCallback;

function mock_class<T>(classname<T> $className): PersistentMethodMockContainer {
	$classMock = new ClassMock($className);

	PersistentMockRegistry::register($classMock);

	return $classMock;
}

function mock_object<T>(T $object): PersistentMethodMockContainer {
	$objectMock = new ObjectMock($object);

	PersistentMockRegistry::register($objectMock);

	return $objectMock;
}

function mock_class_method<T>(
	classname<T> $className,
	string $methodName,
	MockCallback $callback,
): PersistentFunctionMock {
	$classMethodMock = new ClassMethodMock($className, $methodName, $callback);

	PersistentMockRegistry::register($classMethodMock);

	return $classMethodMock;
}

function mock_object_method<T>(
	T $object,
	string $methodName,
	MockCallback $callback,
): PersistentFunctionMock {
	$objectMethodMock = new ObjectMethodMock($object, $methodName, $callback);

	PersistentMockRegistry::register($objectMethodMock);

	return $objectMethodMock;
}

function mock_global_function(
	string $globalFunctionName,
	MockCallback $callback,
): PersistentFunctionMock {
	$globalFunctionMock = new GlobalFunctionMock($globalFunctionName, $callback);

	PersistentMockRegistry::register($globalFunctionMock);

	return $globalFunctionMock;
}

function spy_class_method<T>(
	classname<T> $className,
	string $methodName,
): PersistentFunctionMock {
	return mock_class_method($className, $methodName, get_spy_callback());
}

function spy_object_method<T>(
	T $object,
	string $methodName,
): PersistentFunctionMock {
	return mock_object_method($object, $methodName, get_spy_callback());
}

function spy_global_function(
	string $globalFunctionName,
): PersistentFunctionMock {
	return mock_global_function($globalFunctionName, get_spy_callback());
}

function noop_class_method<T>(
	classname<T> $className,
	string $methodName,
): PersistentFunctionMock {
	return mock_class_method($className, $methodName, get_noop_callback());
}

function noop_object_method<T>(
	T $object,
	string $methodName,
): PersistentFunctionMock {
	return mock_object_method($object, $methodName, get_noop_callback());
}

function noop_global_function(
	string $globalFunctionName,
): PersistentFunctionMock {
	return mock_global_function($globalFunctionName, get_noop_callback());
}

function deactivate_all_persistent_mocks(): void {
	PersistentMockRegistry::deactivateAllPersistentMocks();
}
