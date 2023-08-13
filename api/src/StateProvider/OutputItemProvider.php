<?php
/**
 * This file is part of the Diagla package.
 *
 * (c) Piotr Opioła <piotr@opiola.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\StateProvider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DTO\OutputInterface;
use App\Exception\InvalidOperationException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class OutputItemProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|null
    {
        if (!$operation instanceof Get) {
            throw new InvalidOperationException([Get::class], get_class($operation));
        }

        $item = $this->itemProvider->provide($operation, $uriVariables, $context);

        if ($operation->getOutput() && is_a($operation->getOutput()['class'], OutputInterface::class, true)) {
            return $operation->getOutput()['class']::createOutput($item);
        }

        return $item;
    }
}
