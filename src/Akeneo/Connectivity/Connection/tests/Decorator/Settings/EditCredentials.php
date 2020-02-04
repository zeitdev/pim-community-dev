<?php
declare(strict_types=1);


namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Decorator\Settings;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCredentials extends ElementDecorator
{
    use SpinCapableTrait;

    public function regenerateSecret(): void
    {
        $regenerateButton = $this->spin(function () {
            return $this->find('css', '[data-testid="regenerate-secret"]');
        }, 'Can not find regenerate secret button in edit connection form.');
        var_dump($regenerateButton->getAttribute('title'));

        $regenerateButton->click();

        //$confirmButton = $this->spin(function () {
        //    return $this->find('css', '[data-testid="confirm-regenerate"]');
        //}, 'Can not find confirm regeneration button in modal.');
        sleep(3);
        //$confirmButton->click();
    }

    public function getSecret(): string
    {
        return $this->spin(function () {
            return $this->find('css', '[data-testid="Secret-value"]')->getSecret();
        }, 'Can not find regenerate secret button in edit connection form.');
    }
}
