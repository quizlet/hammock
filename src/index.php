<?hh // strict

namespace Hammock;

use type Hammock\Persistent\Mocks\{
	ClassMethodMock,
	ClassMock,
	GlobalFunctionMock,
	ObjectMethodMock,
	ObjectMock,
};

use type Hammock\Mocks\{FunctionMock, MethodMockContainer};
use function Hammock\{get_noop_callback, get_spy_callback};
use type Hammock\MockCallback;

<<__ReturnDisposable>>
function mock_class<T>(classname<T> $className): MethodMockContainer {
	$classMock = new ClassMock($className);

	return new MethodMockContainer($classMock);
}

<<__ReturnDisposable>>
function mock_object<T>(T $object): MethodMockContainer {
	$objectMock = new ObjectMock($object);

	return new MethodMockContainer($objectMock);
}

<<__ReturnDisposable>>
function mock_class_method<T>(
	classname<T> $className,
	string $methodName,
	MockCallback $callback,
): FunctionMock {
	$classMethodMock = new ClassMethodMock($className, $methodName, $callback);

	return new FunctionMock($classMethodMock);
}

<<__ReturnDisposable>>
function mock_object_method<T>(
	T $object,
	string $methodName,
	MockCallback $callback,
): FunctionMock {
	$objectMethodMock = new ObjectMethodMock($object, $methodName, $callback);

	return new FunctionMock($objectMethodMock);
}

<<__ReturnDisposable>>
function mock_global_function(
	string $globalFunctionName,
	MockCallback $callback,
): FunctionMock {
	$globalFunctionMock = new GlobalFunctionMock($globalFunctionName, $callback);

	return new FunctionMock($globalFunctionMock);
}

<<__ReturnDisposable>>
function spy_class_method<T>(
	classname<T> $className,
	string $methodName,
): FunctionMock {
	return mock_class_method($className, $methodName, get_spy_callback());
}

<<__ReturnDisposable>>
function spy_object_method<T>(T $object, string $methodName): FunctionMock {
	return mock_object_method($object, $methodName, get_spy_callback());
}

<<__ReturnDisposable>>
function spy_global_function(string $globalFunctionName): FunctionMock {
	return mock_global_function($globalFunctionName, get_spy_callback());
}

<<__ReturnDisposable>>
function noop_class_method<T>(
	classname<T> $className,
	string $methodName,
): FunctionMock {
	return mock_class_method($className, $methodName, get_noop_callback());
}

<<__ReturnDisposable>>
function noop_object_method<T>(T $object, string $methodName): FunctionMock {
	return mock_object_method($object, $methodName, get_noop_callback());
}

<<__ReturnDisposable>>
function noop_global_function(string $globalFunctionName): FunctionMock {
	return mock_global_function($globalFunctionName, get_noop_callback());
}

function this(): mixed {
	return MockManager::getCurrentObject();
}
