/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace ArangoDb;

use ArangoDb\Type\Transaction as TransactionType;
use ArangoDb\Type\Transactional;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
class TransactionalClient
{
    /**
     * @var ClientInterface
     */
    private client;
    /**
     * Types
     *
     * @var Transactional[]
     */
    private types;
    public function __construct(<ClientInterface> client)
    {
        let this->client = client;
    }

    public function send(array params = [], bool waitForSync = false) -> <ResponseInterface>
    {
        let actions = "";
        let collectionsWrite = [[]];
        let collectionsRead = [[]];
        let returnIds = [];
        for key, type in this->types {
            let collectionsWrite[] = type->collectionsWrite();
            let collectionsWrite[] = type->collectionsRead();
            // TODO multiple rIds
            let actions .= str_replace("var rId", "var rId" . key, type->toJs());
            let returnIds[] = "rId" . key;
        }
        let collectionsWrite = array_merge(...collectionsWrite);
        let collectionsRead = array_merge(...collectionsRead);
        let response = this->client->sendRequest(TransactionType::with(sprintf("function () {var db = require('@arangodb').db;%s return {%s}}", actions, implode(',', returnIds)), array_unique(collectionsWrite), params, array_unique(collectionsRead), waitForSync)->toRequest());
        let this->types = [];
        return response;
    }

    public function add(<Transactional> type) -> void
    {
        let this->types[] = type;
    }

    public function addList(<Transactional> ...types) -> void
    {
        let this->types = array_merge(this->types, types);
    }

}