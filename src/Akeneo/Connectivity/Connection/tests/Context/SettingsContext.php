<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Context;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SettingsContext extends PimContext
{
    use SpinCapableTrait;

    /** @var CreateConnectionHandler */
    private $createConnection;

    /** @var ConnectionWithCredentials */
    private $lastConnectionCredentials;

    public function __construct(string $mainContextClass, CreateConnectionHandler $createConnection)
    {
        parent::__construct($mainContextClass);
        $this->createConnection = $createConnection;
    }

    /**
     * @param string $flowType
     * @param string $label
     *
     * @Given the :flowType Connection :label has been created
     */
    public function theConnectionHasBeenCreated(string $flowType, string $label): void
    {
        $map = [
            'data source' => 'data_source',
            'source' => 'data_source',
            'data destination' => 'data_destination',
            'destination' => 'data_destination',
            'data other' => 'other',
            'other' => 'other',
        ];
        if (!isset($map[$flowType])) {
            throw new \UnexpectedValueException('The flow type you want to choose does not exist.');
        }

        $code = str_replace(' ', '_', $label);
        $command = new CreateConnectionCommand($code, $label, $map[$flowType]);
        $this->lastConnectionCredentials = $this->createConnection->handle($command);
    }

    /**
     * @param string $connection
     *
     * @When I regenerate the secret of the ":connection" connection
     */
    public function regenerateSecretOfAConnection(string $connection)
    {
        $navigationContext = $this->getNavigationContext();
        $code = str_replace(' ', '_', $connection);
        $navigationContext->iAmOnThePage('Connections edit', ['code' => $code]);
        $this->getMainContext()->getSubcontext('webUser')->scrollContainerTo();

        $this->getCurrentPage()->getElement('Credentials form')->regenerateSecret();

//        $navigationContext->iShouldBeRedirectedOnThePage('Connection regenerateSecret', ['code' => $code]);
//        $navigationContext->iAmOnThePage('Connection regenerateSecret', ['code' => $code]);
//        $this->getCurrentPage()->getElement('Confirm modal')->confirm();
    }

    /**
     * @Then the secret should have changed
     */
    public function theSecretShouldHaveChanged()
    {
        $this
            ->getNavigationContext()
            ->iAmOnThePage('Connections edit', ['code' => $this->lastConnectionCredentials->code()]);
        $currentSecret = $this->getCurrentPage()->getElement('Credentials form')->getSecret();
        Assert::assertNotSame($this->lastConnectionCredentials->secret(), $currentSecret);
    }

    /**
     * @param TableNode $creationData
     *
     * @When I create a connection with the following information:
     */
    public function createConnection(TableNode $creationData): void
    {
        $this->getNavigationContext()->iAmOnThePage('Connections create');
        $creationForm = $this->getCurrentPage()->getElement('Creation form');

        $data = $creationData->getColumnsHash()[0];
        $creationForm->setFlowType($data['flow type']);
        $creationForm->setLabel($data['label']);

        $creationForm->save();
    }

    /**
     * @param string $connection
     * @param string $listType
     *
     * @throws \Context\Spin\TimeoutException
     * @throws \UnexpectedValueException
     *
     * @Then I should see the ":connection" connection in the ":listType" list
     */
    public function iShouldSeeTheConnectionInTheList(string $connection, string $listType)
    {
        $this->getNavigationContext()->iAmOnThePage('Connections index');
        $listType = strtolower($listType);
        $map = [
            'data source' => 'Data source connections list',
            'data destination' => 'Data destination connections list',
            'data other' => 'Other connections list',
            'other' => 'Other connections list',
        ];
        if (!isset($map[$listType])) {
            throw new \UnexpectedValueException('The flow type you want to access to does not exist.');
        }

        $element = $map[$listType];
        $list = $this->spin(function () use ($element) {
            return $this->getCurrentPage()->getElement($element);
        }, sprintf('Can not find list for "%s"', $listType));

        $this->spin(function () use ($list, $connection) {
            return $list->find('css', sprintf('[title="%s"]', $connection));
        }, sprintf('Can not find connection "%s" in list "%s"', $connection, $listType));
    }
}
