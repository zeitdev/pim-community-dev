<?php
declare(strict_types=1);


namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Context\Page\Connections;


use Context\Page\Base\Base;

class RegenerateSecret extends Base
{
    /** @var string */
    protected $path = '#/connections/{code}/regenerate-secret';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Confirm modal' => [
                    'css'        => '[data-testid="Confirm regeneration-modal"]',
                    'decorators' => ['Akeneo\Connectivity\Connection\Tests\EndToEnd\Decorator\Settings\ConfirmRegenerateModal']
                ],
            ]
        );
    }
}
