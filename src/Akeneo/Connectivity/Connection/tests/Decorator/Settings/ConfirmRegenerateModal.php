<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Decorator\Settings;

use Pim\Behat\Decorator\ElementDecorator;

class ConfirmRegenerateModal extends ElementDecorator
{
    public function confirm()
    {
        $confirmButton = $this->spin(function () {
            return $this->find('css', '[data-testid="confirm-regenerate"]');
        }, 'Can not find confirm regeneration button in modal.');
        $confirmButton->click();
    }
}
