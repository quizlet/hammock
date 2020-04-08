<?hh // strict

namespace Hammock\Fixtures;

use type Hammock\Interfaces\IDeactivatable;

class MockDeactivatable implements IDeactivatable {
	private bool $isDeactivated = false;
	public function deactivate(): void {
		$this->isDeactivated = true;
	}
	public function isDeactivated(): bool {
		return $this->isDeactivated;
	}
}
