<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Context\Page\Connections;

use Context\Page\Base\Form;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    /** @var string */
    protected $path = '#/connections/{code}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Credentials form' => [
                    'css'        => '[data-testid="credentials-form"]',
                    'decorators' => ['Akeneo\Connectivity\Connection\Tests\EndToEnd\Decorator\Settings\EditCredentials']
                ],
                'Regenerate secret modal' => [
                    'css'        => '[data-testid="Confirm regeneration-modal"]',
                    'decorators' => ['Akeneo\Connectivity\Connection\Tests\EndToEnd\Decorator\Settings\ConfirmRegenerateModal']
                ],
            ]
        );
    }
}
