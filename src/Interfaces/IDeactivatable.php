<?hh // strict

namespace Hammock\Interfaces;

interface IDeactivatable {
	public function deactivate(): void;
	public function isDeactivated(): bool;
}
